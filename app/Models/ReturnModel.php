<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnModel extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_number',
        'sale_id',
        'user_id',
        'total_return_amount',
        'payment_method',
        'cash_refund_amount',
        'debt_reduction_amount',
        'reason',
        'return_date',
        'is_voided',
        'void_reason',
        'voided_by',
        'voided_at',
    ];

    protected $casts = [
        'total_return_amount' => 'decimal:2',
        'cash_refund_amount' => 'decimal:2',
        'debt_reduction_amount' => 'decimal:2',
        'return_date' => 'datetime',
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
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
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    // ===== SCOPES =====

    public function scopeNotVoided($query)
    {
        return $query->where('is_voided', false);
    }

    public function scopeVoided($query)
    {
        return $query->where('is_voided', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('return_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('return_date', now()->month)
            ->whereYear('return_date', now()->year);
    }

    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    // ===== BUSINESS LOGIC METHODS =====

    /**
     * Generate unique return number (format: RET-YYYYMMDD-0001)
     */
    public function generateReturnNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        return 'RET-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate refund distribution between cash and debt
     *
     * Logic:
     * - Cash sale: All refund as cash
     * - Debt sale (unpaid): All refund reduces debt
     * - Debt sale (partially paid): Reduce debt first, then cash for excess
     *
     * @param Sale $sale
     * @param float $totalRefund
     * @return array ['payment_method', 'cash_refund', 'debt_reduction']
     */
    public static function calculateRefundDistribution(Sale $sale, float $totalRefund): array
    {
        if ($sale->payment_method === 'cash') {
            // Cash sale: full refund in cash
            return [
                'payment_method' => 'cash_refund',
                'cash_refund' => $totalRefund,
                'debt_reduction' => 0,
            ];
        }

        // Debt sale: calculate split
        $remainingDebt = $sale->debt_amount;

        if ($totalRefund <= $remainingDebt) {
            // Full refund goes to debt reduction
            return [
                'payment_method' => 'debt_reduction',
                'cash_refund' => 0,
                'debt_reduction' => $totalRefund,
            ];
        } else {
            // Mixed: reduce debt fully, refund excess in cash
            return [
                'payment_method' => 'mixed',
                'cash_refund' => $totalRefund - $remainingDebt,
                'debt_reduction' => $remainingDebt,
            ];
        }
    }

    /**
     * Process a return
     *
     * @param Sale $sale
     * @param array $items [['sale_item_id' => 1, 'quantity' => 5], ...]
     * @param string $reason
     * @return ReturnModel|false
     */
    public static function processReturn(Sale $sale, array $items, string $reason)
    {
        DB::beginTransaction();
        try {
            Log::info('Starting return processing', ['sale_id' => $sale->id, 'payment_method' => $sale->payment_method]);

            // 1. Validate sale
            if ($sale->is_voided) {
                throw new \Exception("Cannot return items from a voided sale");
            }

            // 2. Validate and calculate total refund
            $totalRefund = 0;
            $validatedItems = [];

            foreach ($items as $item) {
                $saleItem = $sale->saleItems()->findOrFail($item['sale_item_id']);
                Log::info('Processing return item', ['sale_item_id' => $item['sale_item_id'], 'quantity' => $item['quantity']]);

                // Check available quantity
                $availableQty = $saleItem->getAvailableQuantityForReturn();
                Log::info('Available quantity', ['sale_item_id' => $item['sale_item_id'], 'available' => $availableQty]);

                // Validate: quantity must be positive
                if ($item['quantity'] <= 0) {
                    throw new \Exception("Return quantity must be greater than 0 for {$saleItem->product->name}.");
                }

                // Validate: cannot return more than available
                if ($item['quantity'] > $availableQty) {
                    $alreadyReturned = $saleItem->quantity - $availableQty;
                    throw new \Exception("Cannot return {$item['quantity']} of {$saleItem->product->name}. Only {$availableQty} available (Sold: {$saleItem->quantity}, Already Returned: {$alreadyReturned}).");
                }

                // Calculate refund for this item using the actual price paid (unit price after discount)
                $actualPricePerUnit = $saleItem->total_price / $saleItem->quantity;
                $itemRefund = $actualPricePerUnit * $item['quantity'];
                $totalRefund += $itemRefund;

                $validatedItems[] = [
                    'sale_item' => $saleItem,
                    'quantity' => $item['quantity'],
                    'refund' => $itemRefund,
                ];
            }

            Log::info('Total refund calculated', ['total_refund' => $totalRefund]);

            // 3. Calculate refund distribution
            $distribution = self::calculateRefundDistribution($sale, $totalRefund);
            Log::info('Refund distribution calculated', $distribution);

            // 4. Create return record
            $return = self::create([
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
                'total_return_amount' => $totalRefund,
                'payment_method' => $distribution['payment_method'],
                'cash_refund_amount' => $distribution['cash_refund'],
                'debt_reduction_amount' => $distribution['debt_reduction'],
                'reason' => $reason,
                'return_date' => now(),
            ]);

            Log::info('Return record created', ['return_id' => $return->id, 'return_number' => $return->return_number]);

            // 5. Create return items and restore stock
            foreach ($validatedItems as $item) {
                // Calculate the actual price per unit paid (after discount)
                $actualPricePerUnit = $item['sale_item']->total_price / $item['sale_item']->quantity;

                ReturnItem::create([
                    'return_id' => $return->id,
                    'sale_item_id' => $item['sale_item']->id,
                    'product_id' => $item['sale_item']->product_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $actualPricePerUnit, // Store the actual price paid per unit
                    'total_price' => $item['refund'],
                ]);

                // Restore stock
                $item['sale_item']->product->increment('quantity', $item['quantity']);
                Log::info('Stock restored', ['product_id' => $item['sale_item']->product_id, 'quantity' => $item['quantity']]);
            }

            // 6. Adjust customer debt if applicable
            if ($distribution['debt_reduction'] > 0 && $sale->customer_id) {
                Log::info('Adjusting customer debt', [
                    'customer_id' => $sale->customer_id,
                    'debt_reduction' => $distribution['debt_reduction']
                ]);

                CustomerDebtTransaction::create([
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id,
                    'transaction_type' => 'refund',
                    'amount' => -$distribution['debt_reduction'], // Negative reduces debt
                    'description' => "Return #{$return->return_number}",
                ]);

                // Reduce sale debt amount
                $sale->decrement('debt_amount', $distribution['debt_reduction']);

                Log::info('Customer debt adjusted successfully');
            }

            DB::commit();
            Log::info('Return transaction committed successfully');

            return $return;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Return processing failed: ' . $e->getMessage());
            Log::error('Exception trace', ['trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Void this return
     * - Mark return as voided
     * - Reduce stock (reverse the increase)
     * - Reverse debt/refund adjustments
     *
     * @param string $reason
     * @param int $voidedBy
     * @return bool
     */
    public function voidReturn(string $reason, int $voidedBy): bool
    {
        if ($this->is_voided) {
            return false; // Already voided
        }

        DB::beginTransaction();
        try {
            // 1. Reduce stock (reverse the increase from return)
            foreach ($this->returnItems as $item) {
                $product = $item->product;

                // Check if we have enough stock to reduce
                if ($product->quantity < $item->quantity) {
                    throw new \Exception("Cannot void: Product {$product->name} stock is too low. Current: {$product->quantity}, need: {$item->quantity}");
                }

                $product->decrement('quantity', $item->quantity);
            }

            // 2. Reverse debt reduction if applicable
            if ($this->debt_reduction_amount > 0 && $this->sale->customer_id) {
                // Increase sale debt amount back
                $this->sale->increment('debt_amount', $this->debt_reduction_amount);

                // Mark the refund transaction as voided
                CustomerDebtTransaction::where('sale_id', $this->sale_id)
                    ->where('transaction_type', 'refund')
                    ->where('description', 'like', "%{$this->return_number}%")
                    ->update([
                        'voided_at' => now(),
                        'void_reason' => 'Voided Return',
                    ]);
            }

            // 3. Mark return as voided
            $this->update([
                'is_voided' => true,
                'void_reason' => $reason,
                'voided_by' => $voidedBy,
                'voided_at' => now(),
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Return void failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get return status
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_voided) return 'voided';
        return 'active';
    }

    /**
     * Get status color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'voided' => '#95a5a6',
            'active' => '#f39c12',
            default => '#7f8c8d',
        };
    }

    /**
     * Get status text in Arabic
     */
    public function getStatusText(): string
    {
        return match($this->status) {
            'voided' => 'ملغي',
            'active' => 'نشط',
            default => 'غير معروف',
        };
    }

    /**
     * Get payment method text in Arabic
     */
    public function getPaymentMethodText(): string
    {
        return match($this->payment_method) {
            'cash_refund' => 'استرداد نقدي',
            'debt_reduction' => 'تخفيض دين',
            'mixed' => 'مختلط',
            default => 'غير معروف',
        };
    }

    // ===== BOOT METHOD =====

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($return) {
            if (empty($return->return_number)) {
                $return->return_number = $return->generateReturnNumber();
            }
            if (empty($return->return_date)) {
                $return->return_date = now();
            }
            if (empty($return->user_id)) {
                $return->user_id = auth()->id();
            }
        });
    }
}
