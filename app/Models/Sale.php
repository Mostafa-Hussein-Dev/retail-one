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
        'paid_amount',
        'debt_amount',
        'payment_method',
        'notes',
        'sale_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'debt_amount' => 'decimal:2',
        'sale_date' => 'datetime',
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
        $this->subtotal = $this->items->sum('total_price');
        $this->total_amount = $this->subtotal - $this->discount_amount;

        // Update debt amount for debt sales
        if ($this->payment_method === 'debt') {
            $this->debt_amount = $this->total_amount;
            $this->paid_amount = 0.00;
        } else {
            $this->debt_amount = max(0, $this->total_amount - $this->paid_amount);
        }

        $this->save();
    }

    public function completeSale(): bool
    {
        try {
            DB::transaction(function () {
                // Reduce stock for all items
                foreach ($this->items as $item) {
                    $product = $item->product;
                    if (!$product->reduceStock($item->quantity)) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }
                }

                // Create debt transaction if needed
                if ($this->debt_amount > 0 && $this->customer_id) {
                    CustomerDebtTransaction::create([
                        'customer_id' => $this->customer_id,
                        'sale_id' => $this->id,
                        'transaction_type' => 'debt',
                        'amount' => $this->debt_amount,
                        'description' => "Sale debt - Receipt #{$this->receipt_number}",
                    ]);

                    // Update customer total debt
                    $this->customer->increment('total_debt', $this->debt_amount);
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

        foreach ($this->returns as $return) {
            foreach ($return->items as $returnItem) {
                $saleItemId = $returnItem->sale_item_id;
                $returnedQuantities[$saleItemId] = ($returnedQuantities[$saleItemId] ?? 0) + $returnItem->quantity;
            }
        }

        return $this->items->map(function ($item) use ($returnedQuantities) {
            $returnedQty = $returnedQuantities[$item->id] ?? 0;
            $item->returnable_quantity = $item->quantity - $returnedQty;
            return $item;
        })->where('returnable_quantity', '>', 0);
    }

    public function processPayment(float $amount): bool
    {
        if ($amount <= 0 || $amount > $this->debt_amount) {
            return false;
        }

        $this->paid_amount += $amount;
        $this->debt_amount -= $amount;
        $this->save();

        // Create payment transaction
        if ($this->customer_id) {
            CustomerDebtTransaction::create([
                'customer_id' => $this->customer_id,
                'sale_id' => $this->id,
                'transaction_type' => 'payment',
                'amount' => $amount,
                'description' => "Payment for Receipt #{$this->receipt_number}",
            ]);

            // Update customer total debt
            $this->customer->decrement('total_debt', $amount);
        }

        return true;
    }

    // Static Methods
    public static function todaysSales(): \Illuminate\Database\Eloquent\Collection
    {
        return static::today()->with(['items.product', 'customer', 'user'])->get();
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

        return $query->sum('total_amount') ?? 0;
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

        return $query->with('items')->get()->sum('total_profit') ?? 0;
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
