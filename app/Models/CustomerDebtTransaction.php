<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDebtTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'sale_id',
        'transaction_type',
        'amount',
        'payment_method',
        'description',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // Scopes
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

    // Helper Methods
    public function isVoided(): bool
    {
        return !is_null($this->voided_at);
    }

    public function getTypeText(): string
    {
        return match ($this->transaction_type) {
            'debt' => 'دين',
            'payment' => 'دفعة',
            default => 'غير معروف',
        };
    }

    public function getTypeColor(): string
    {
        return match ($this->transaction_type) {
            'debt' => '#e74c3c',
            'payment' => '#27ae60',
            default => '#95a5a6',
        };
    }
}
