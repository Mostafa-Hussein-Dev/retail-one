<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'user_id',
        'total_amount',
        'paid_amount',
        'debt_amount',
        'purchase_date',
        'notes',
        'is_voided',
        'void_reason',
        'voided_by',
        'voided_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'debt_amount' => 'decimal:2',
        'purchase_date' => 'datetime',
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];

    protected $appends = [
        'status',
    ];

    // ===== RELATIONSHIPS =====

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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
        return $this->hasMany(PurchaseItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function debtTransactions(): HasMany
    {
        return $this->hasMany(SupplierDebtTransaction::class);
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

    public function scopeWithDebt($query)
    {
        return $query->where('debt_amount', '>', 0);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('purchase_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year);
    }

    // ===== BUSINESS LOGIC METHODS =====

    /**
     * Generate unique purchase number (format: PO-YYYYMMDD-0001)
     */
    public function generatePurchaseNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', today())->count() + 1;
        return 'PO-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
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

    /**
     * Process payment to supplier
     *
     * @param float $amount Payment amount
     * @return bool Success status
     */
    public function processPayment(float $amount): bool
    {
        if ($amount <= 0 || $amount > $this->debt_amount) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Reduce debt on purchase
            $this->debt_amount -= $amount;
            $this->paid_amount += $amount;
            $this->save();

            // Create payment transaction (negative amount)
            if ($this->supplier_id) {
                SupplierDebtTransaction::create([
                    'supplier_id' => $this->supplier_id,
                    'purchase_id' => $this->id,
                    'transaction_type' => 'payment',
                    'amount' => -$amount, // Negative reduces debt
                    'description' => "Payment for Purchase #{$this->purchase_number}",
                ]);

                // Reduce supplier total debt
                $this->supplier->decrement('total_debt', $amount);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase payment failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Void this purchase
     * - Mark purchase as voided
     * - Mark all debt transactions as voided
     * - Reduce stock for all items (reverse the increase)
     * - Supplier debt auto-recalculates (excluding voided transactions)
     *
     * @param string $reason Void reason
     * @param int $voidedBy User ID who voided
     * @return bool Success status
     */
    public function voidPurchase(string $reason, int $voidedBy): bool
    {
        if ($this->is_voided) {
            return false; // Already voided
        }

        DB::beginTransaction();
        try {
            // Reduce stock (reverse the increase from purchase)
            foreach ($this->purchaseItems as $item) {
                $product = $item->product;

                // Check if we have enough stock to reduce
                if ($product->quantity < $item->quantity) {
                    throw new \Exception("Cannot void: Product {$product->name} has been sold. Current stock: {$product->quantity}, need: {$item->quantity}");
                }

                $product->decrement('quantity', $item->quantity);
            }

            // Mark all debt transactions as voided
            $this->debtTransactions()->update([
                'voided_at' => now(),
                'void_reason' => 'Voided Purchase',
            ]);

            // If this was a debt purchase, recalculate supplier total_debt
            // The supplier's total_debt should be SUM of non-voided transactions
            if ($this->supplier_id && $this->debt_amount > 0) {
                $this->supplier->decrement('total_debt', $this->debt_amount);
            }

            // Mark purchase as voided
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
            Log::error('Purchase void failed: ' . $e->getMessage());
            return false;
        }
    }

    // ===== ACCESSORS =====

    /**
     * Get purchase status text
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_voided) return 'voided';
        if ($this->debt_amount > 0) return 'partially_paid';
        return 'paid';
    }

    /**
     * Get purchase status color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'voided' => '#95a5a6',
            'partially_paid' => '#f39c12',
            'paid' => '#27ae60',
            default => '#7f8c8d',
        };
    }

    /**
     * Get purchase status text in Arabic
     */
    public function getStatusText(): string
    {
        return match($this->status) {
            'voided' => 'ملغي',
            'partially_paid' => 'مدفوع جزئياً',
            'paid' => 'مدفوع',
            default => 'غير معروف',
        };
    }

    // ===== BOOT METHOD =====

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (empty($purchase->purchase_number)) {
                $purchase->purchase_number = $purchase->generatePurchaseNumber();
            }
            if (empty($purchase->purchase_date)) {
                $purchase->purchase_date = now();
            }
            if (empty($purchase->user_id)) {
                $purchase->user_id = auth()->id();
            }
        });
    }
}
