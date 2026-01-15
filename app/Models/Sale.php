<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'customer_id',
        'user_id',
        'subtotal',
        'discount_amount',
        'total_amount',
        'debt_amount',
        'payment_method',
        'notes',
        'sale_date',
        'is_voided',
        'void_reason',
        'voided_by',
        'voided_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'debt_amount' => 'decimal:2',
        'sale_date' => 'datetime',
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];

    protected $appends = [
        'total_profit',
        'items_count',
        'status',
        'total_amount_lbp',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnModel::class);
    }

    public function debtTransaction(): HasOne
    {
        return $this->hasOne(CustomerDebtTransaction::class);
    }

    // ===== PHASE 4 UPDATE =====
    public function debtTransactions(): HasMany
    {
        return $this->hasMany(CustomerDebtTransaction::class);
    }

    // Accessors
    public function getTotalProfitAttribute(): float
    {
        return $this->items->sum('profit');
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getStatusAttribute(): string
    {
        if ($this->debt_amount > 0) return 'partially_paid';
        if ($this->hasReturns()) return 'returned';
        return 'completed';
    }

    public function getTotalAmountLbpAttribute(): float
    {
        return $this->total_amount * config('pos.exchange_rate', 89500);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }

    public function scopeNotVoided($query)
    {
        return $query->where('is_voided', false);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('sale_date', now()->year);
    }

    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeWithDebt($query)
    {
        return $query->where('debt_amount', '>', 0);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }

    // Business Logic Methods
    public function generateReceiptNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        return $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function addItem(Product $product, float $quantity, float $discount = 0): SaleItem
    {
        $unitPrice = $product->selling_price;
        $unitCost = $product->cost_price;
        $discountAmount = ($unitPrice * $quantity) * ($discount / 100);
        $totalPrice = ($unitPrice * $quantity) - $discountAmount;
        $profit = (($unitPrice - $unitCost) * $quantity) - $discountAmount;

        return $this->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'unit_cost' => $unitCost,
            'discount_percentage' => $discount,
            'discount_amount' => $discountAmount,
            'total_price' => $totalPrice,
            'profit' => $profit,
        ]);
    }

    public function removeItem(int $itemId): bool
    {
        $item = $this->items()->find($itemId);
        if ($item) {
            $item->delete();
            $this->recalculateTotal();
            return true;
        }
        return false;
    }

    public function updateItemQuantity(int $itemId, float $newQuantity): bool
    {
        $item = $this->items()->find($itemId);
        if ($item && $newQuantity > 0) {
            $item->quantity = $newQuantity;
            $item->total_price = $item->unit_price * $newQuantity - $item->discount_amount;
            $item->profit = ($item->unit_price - $item->unit_cost) * $newQuantity - $item->discount_amount;
            $item->save();

            $this->recalculateTotal();
            return true;
        }
        return false;
    }

    public function applyDiscount(float $discountAmount): void
    {
        $this->discount_amount = $discountAmount;
        $this->recalculateTotal();
    }

    public function recalculateTotal(): void
    {
        $this->subtotal = $this->items->sum(function($item) {
            return $item->unit_price * $item->quantity;
        });

        $this->discount_amount = $this->items->sum('discount_amount');
        $this->total_amount = $this->subtotal - $this->discount_amount;

        if ($this->payment_method === 'debt') {
            $this->debt_amount = $this->total_amount;
        } else {
            $this->debt_amount = 0;
        }

        $this->save();
    }

    public function completeSale(): bool
    {
        try {
            DB::transaction(function () {
                foreach ($this->items as $item) {
                    $product = $item->product;
                    if (!$product->reduceStock($item->quantity)) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                }

                if ($this->debt_amount > 0 && $this->customer_id) {
                    CustomerDebtTransaction::create([
                        'customer_id' => $this->customer_id,
                        'sale_id' => $this->id,
                        'transaction_type' => 'debt',
                        'amount' => $this->debt_amount,
                        'description' => "Sale debt - Receipt #{$this->receipt_number}",
                    ]);
                }

                $this->sale_date = now();
                $this->save();
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Sale completion failed: ' . $e->getMessage());
            return false;
        }
    }

    public function hasReturns(): bool
    {
        return $this->returns()->exists();
    }

    public function canReturn(): bool
    {
        return !$this->hasReturns() || $this->returns()->sum('total_return_amount') < $this->total_amount;
    }

    public function getReturnableItems(): \Illuminate\Database\Eloquent\Collection
    {
        $returnedQuantities = [];

        // Only consider non-voided returns
        $nonVoidedReturns = $this->returns()->where('is_voided', false)->get();

        foreach ($nonVoidedReturns as $return) {
            foreach ($return->items as $returnItem) {
                $saleItemId = $returnItem->sale_item_id;
                $returnedQuantities[$saleItemId] = ($returnedQuantities[$saleItemId] ?? 0) + $returnItem->quantity;
            }
        }

        // Use saleItems to ensure proper loading
        $items = $this->relationLoaded('saleItems') ? $this->saleItems : $this->saleItems()->get();

        return $items->map(function ($item) use ($returnedQuantities) {
            $returnedQty = $returnedQuantities[$item->id] ?? 0;
            $item->returnable_quantity = $item->quantity - $returnedQty;
            return $item;
        })->filter(function ($item) {
            return $item->returnable_quantity > 0;
        })->values();
    }

    public function processPayment(float $amount): bool
    {
        if ($amount <= 0 || $amount > $this->debt_amount) {
            return false;
        }

        $this->debt_amount -= $amount;
        $this->save();

        if ($this->customer_id) {
            CustomerDebtTransaction::create([
                'customer_id' => $this->customer_id,
                'sale_id' => $this->id,
                'transaction_type' => 'payment',
                'amount' => -$amount, // Negative amount reduces debt
                'description' => "Payment for Receipt #{$this->receipt_number}",
            ]);
        }

        return true;
    }

    // ===== PHASE 4 NEW METHODS =====

    /**
     * Void this sale with transaction voiding
     */
    public function voidSale(string $reason, int $voidedBy): bool
    {
        if ($this->is_voided) {
            Log::warning("Attempted to void already voided sale: {$this->id}");
            return false;
        }

        try {
            Log::info("Starting void for sale {$this->id}, receipt: {$this->receipt_number}, payment_method: {$this->payment_method}");
            DB::beginTransaction();

            // Restore stock
            Log::info("Restoring stock for sale {$this->id}");
            foreach ($this->saleItems as $item) {
                Log::info("Restoring product {$item->product_id}: {$item->quantity} units");
                $item->product->increment('quantity', $item->quantity);
            }

            // Mark all debt transactions as voided (they will be excluded from total_debt calculation)
            $voidedCount = $this->debtTransactions()->update([
                'voided_at' => now(),
                'void_reason' => 'Voided Sale',
            ]);
            Log::info("Marked {$voidedCount} transactions as voided");

            // Mark sale as voided
            Log::info("Marking sale {$this->id} as voided");
            $this->update([
                'is_voided' => true,
                'void_reason' => $reason,
                'voided_by' => $voidedBy,
                'voided_at' => now(),
            ]);

            DB::commit();
            Log::info("Successfully voided sale {$this->id}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sale void failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Get total paid from non-voided payments
     */
    public function getTotalPaid(): float
    {
        return abs($this->debtTransactions()
            ->where('transaction_type', 'payment')
            ->whereNull('voided_at')
            ->sum('amount'));
    }

    // Static Methods
    public static function todaysSales(): \Illuminate\Database\Eloquent\Collection
    {
        return static::today()->notVoided()->with(['items.product', 'customer', 'user'])->get();
    }

    public static function getTotalSalesAmount(string $period = 'today'): float
    {
        $query = static::query();

        switch ($period) {
            case 'today':
                $query->today();
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->thisYear();
                break;
        }

        return $query->notVoided()->sum('total_amount') ?? 0;
    }

    public static function getTotalProfit(string $period = 'today'): float
    {
        $query = static::query();

        switch ($period) {
            case 'today':
                $query->today();
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->thisYear();
                break;
        }

        return $query->notVoided()->with('items')->get()->sum('total_profit') ?? 0;
    }

    // ===== PHASE 6 NEW METHODS =====

    /**
     * Get total amount returned from this sale
     */
    public function getTotalReturned(): float
    {
        return $this->returns()
            ->where('is_voided', false)
            ->sum('total_return_amount');
    }

    /**
     * Get payment method text in Arabic
     */
    public function getPaymentMethodText(): string
    {
        return match($this->payment_method) {
            'cash' => 'نقدي',
            'debt' => 'دين',
            default => 'غير معروف',
        };
    }

    /**
     * Check if sale is fully returned (all items returned)
     */
    public function isFullyReturned(): bool
    {
        $totalReturned = $this->getTotalReturned();
        return $totalReturned >= $this->total_amount;
    }

    /**
     * Check if a specific item can be returned with given quantity
     *
     * @param int $saleItemId
     * @param float $quantity
     * @return bool
     */
    public function canReturnItem(int $saleItemId, float $quantity): bool
    {
        $saleItem = $this->saleItems()->find($saleItemId);

        if (!$saleItem) {
            return false;
        }

        $availableQty = $saleItem->getAvailableQuantityForReturn();
        return $quantity <= $availableQty && $quantity > 0;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->receipt_number)) {
                $sale->receipt_number = $sale->generateReceiptNumber();
            }
            if (empty($sale->sale_date)) {
                $sale->sale_date = now();
            }
            if (empty($sale->user_id)) {
                $sale->user_id = auth()->id();
            }
        });
    }
}
