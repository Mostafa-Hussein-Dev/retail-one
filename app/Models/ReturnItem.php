<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'sale_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // ===== RELATIONSHIPS =====

    public function return(): BelongsTo
    {
        return $this->belongsTo(ReturnModel::class, 'return_id');
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ===== HELPER METHODS =====

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPrice(): string
    {
        return number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPrice(): string
    {
        return number_format($this->total_price, 2);
    }
}
