<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'unit_cost',
        'discount_percentage',
        'discount_amount',
        'total_price',
        'profit',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    protected $appends = [
        'original_price',
        'total_original_price',
        'unit_price_lbp',
        'total_price_lbp',
        'profit_margin',
        'has_discount',
    ];

    /**
     * Get sale relationship
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get product relationship
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getOriginalPriceAttribute(): float
    {
        return $this->unit_price + ($this->discount_amount / $this->quantity);
    }

    public function getTotalOriginalPriceAttribute(): float
    {
        return $this->original_price * $this->quantity;
    }

    public function getUnitPriceLbpAttribute(): float
    {
        $exchangeRate = 89500; // Default exchange rate
        return $this->unit_price * $exchangeRate;
    }

    public function getTotalPriceLbpAttribute(): float
    {
        $exchangeRate = 89500; // Default exchange rate
        return $this->total_price * $exchangeRate;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->unit_cost == 0) return 0;
        return (($this->unit_price - $this->unit_cost) / $this->unit_cost) * 100;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_percentage > 0 || $this->discount_amount > 0;
    }

    // Business Logic Methods
    public function calculateTotals(): void
    {
        // Calculate discount amount if percentage is used
        if ($this->discount_percentage > 0) {
            $this->discount_amount = ($this->unit_price * $this->quantity) * ($this->discount_percentage / 100);
        }

        // Calculate total price
        $this->total_price = ($this->unit_price * $this->quantity) - $this->discount_amount;

        // Calculate profit
        $this->profit = (($this->unit_price - $this->unit_cost) * $this->quantity) - $this->discount_amount;
    }

    public function applyPercentageDiscount(float $percentage): void
    {
        $this->discount_percentage = $percentage;
        $this->discount_amount = ($this->unit_price * $this->quantity) * ($percentage / 100);
        $this->calculateTotals();
        $this->save();
    }

    public function applyFixedDiscount(float $amount): void
    {
        $this->discount_amount = $amount;
        $this->discount_percentage = ($amount / ($this->unit_price * $this->quantity)) * 100;
        $this->calculateTotals();
        $this->save();
    }

    public function updateQuantity(float $newQuantity): void
    {
        $this->quantity = $newQuantity;
        $this->calculateTotals();
        $this->save();
    }

    public function updateUnitPrice(float $newPrice): void
    {
        $this->unit_price = $newPrice;
        $this->calculateTotals();
        $this->save();
    }

    public function removeDiscount(): void
    {
        $this->discount_percentage = 0.00;
        $this->discount_amount = 0.00;
        $this->calculateTotals();
        $this->save();
    }

    public function getDiscountText(): string
    {
        if ($this->discount_percentage > 0) {
            return number_format($this->discount_percentage, 1) . '% خصم';
        } elseif ($this->discount_amount > 0) {
            return '$' . number_format($this->discount_amount, 2) . ' خصم';
        }
        return '';
    }

    public function canReturn(float $quantity = null): bool
    {
        // Check if the requested quantity can be returned
        $requestedQty = $quantity ?? $this->quantity;

        // Get already returned quantity for this sale item
        $returnedQty = $this->getReturnedQuantity();

        return ($returnedQty + $requestedQty) <= $this->quantity;
    }

    public function getReturnedQuantity(): float
    {
        return $this->sale->returns()
            ->join('return_items', 'returns.id', '=', 'return_items.return_id')
            ->where('return_items.sale_item_id', $this->id)
            ->sum('return_items.quantity');
    }

    public function getAvailableQuantityForReturn(): float
    {
        return $this->quantity - $this->getReturnedQuantity();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($saleItem) {
            $saleItem->calculateTotals();
        });

        static::updating(function ($saleItem) {
            if ($saleItem->isDirty(['quantity', 'unit_price', 'discount_percentage', 'discount_amount'])) {
                $saleItem->calculateTotals();
            }
        });

        static::saved(function ($saleItem) {
            // Recalculate sale totals when sale item changes
            if ($saleItem->sale) {
                $saleItem->sale->recalculateTotal();
            }
        });
    }
}
