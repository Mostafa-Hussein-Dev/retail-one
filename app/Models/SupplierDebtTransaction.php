<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierDebtTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'purchase_id',
        'transaction_type',
        'amount',
        'description',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'voided_at' => 'datetime',
        'transaction_date' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    // ===== SCOPES =====

    public function scopeNotVoided($query)
    {
        return $query->whereNull('voided_at');
    }

    public function scopeVoided($query)
    {
        return $query->whereNotNull('voided_at');
    }

    public function scopeDebt($query)
    {
        return $query->where('transaction_type', 'debt');
    }

    public function scopePayment($query)
    {
        return $query->where('transaction_type', 'payment');
    }

    // ===== HELPER METHODS =====

    public function isVoided(): bool
    {
        return !is_null($this->voided_at);
    }

    public function getTypeText(): string
    {
        return match($this->transaction_type) {
            'debt' => 'دين',
            'payment' => 'دفعة',
            default => 'غير معروف',
        };
    }

    public function getTypeColor(): string
    {
        return match($this->transaction_type) {
            'debt' => '#e74c3c',     // Red (we owe)
            'payment' => '#27ae60',  // Green (we paid)
            default => '#95a5a6',
        };
    }

    public function getFormattedAmount(): string
    {
        $prefix = $this->amount > 0 ? '+' : '';
        return $prefix . '$' . number_format($this->amount, 2);
    }
}
