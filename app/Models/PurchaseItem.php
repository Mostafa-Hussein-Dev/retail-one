<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // ===== RELATIONSHIPS =====

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ===== HELPER METHODS =====

    /**
     * Get formatted unit cost
     */
    public function getFormattedUnitCost(): string
    {
        return '$' . number_format($this->unit_cost, 2);
    }

    /**
     * Get formatted total cost
     */
    public function getFormattedTotalCost(): string
    {
        return '$' . number_format($this->total_cost, 2);
    }
}
