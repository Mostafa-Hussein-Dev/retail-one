<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'name',
        'name_ar',
        'category_id',
        'supplier_id',
        'cost_price',
        'selling_price',
        'quantity',
        'minimum_quantity',
        'unit',
        'description',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'minimum_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'profit_margin',
        'is_low_stock',
        'is_out_of_stock',
        'stock_status',
        'selling_price_lbp',
        'cost_price_lbp',
        'display_name',
        'unit_display',
    ];

    // Unit types
    const UNITS = [
        'piece' => 'قطعة',
        'kg' => 'كيلو',
        'gram' => 'غرام',
        'liter' => 'لتر',
        'meter' => 'متر',
    ];

    /**
     * Get category relationship
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get supplier relationship
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Accessors
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price == 0) return 0;
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->minimum_quantity && $this->quantity > 0;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->quantity <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->is_out_of_stock) return 'out_of_stock';
        if ($this->is_low_stock) return 'low_stock';
        return 'in_stock';
    }

    public function getSellingPriceLbpAttribute(): float
    {
        $exchangeRate = 89500; // Default exchange rate
        return $this->selling_price * $exchangeRate;
    }

    public function getCostPriceLbpAttribute(): float
    {
        $exchangeRate = 89500; // Default exchange rate
        return $this->cost_price * $exchangeRate;
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'ar' && !empty($this->name_ar)) {
            return $this->name_ar;
        }
        return $this->name;
    }

    public function getUnitDisplayAttribute(): string
    {
        return self::UNITS[$this->unit] ?? $this->unit;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= minimum_quantity AND quantity > 0');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeByBarcode($query, string $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('name_ar', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhereHas('category', function ($cat) use ($search) {
                    $cat->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%");
                });
        });
    }

    // Business Logic Methods
    public function canSell(float $requestedQuantity): bool
    {
        return $this->is_active && $this->quantity >= $requestedQuantity;
    }

    public function reduceStock(float $quantity): bool
    {
        if (!$this->canSell($quantity)) {
            return false;
        }

        $this->quantity -= $quantity;
        return $this->save();
    }

    public function increaseStock(float $quantity): bool
    {
        $this->quantity += $quantity;
        return $this->save();
    }

    public function calculateProfit(float $quantity): float
    {
        return ($this->selling_price - $this->cost_price) * $quantity;
    }

    // Static Methods
    public static function findByBarcode(string $barcode): ?self
    {
        return static::where('barcode', $barcode)->active()->first();
    }

    public static function getLowStockProducts()
    {
        return static::lowStock()->active()->with('category')->get();
    }

    public static function getOutOfStockProducts()
    {
        return static::outOfStock()->active()->with('category')->get();
    }

    public static function getUnits(): array
    {
        return self::UNITS;
    }

    // Mutators
    public function setBarcodeAttribute($value)
    {
        $this->attributes['barcode'] = $value ?: $this->generateBarcode();
    }

    // Helper Methods
    private function generateBarcode(): string
    {
        do {
            $barcode = str_pad(rand(1, 9999999999999), 13, '0', STR_PAD_LEFT);
        } while (static::where('barcode', $barcode)->exists());

        return $barcode;
    }

    public function getStockStatusColor(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => '#e74c3c',
            'low_stock' => '#f39c12',
            'in_stock' => '#27ae60',
            default => '#95a5a6',
        };
    }

    public function getStockStatusText(): string
    {
        return match($this->stock_status) {
            'out_of_stock' => 'نفد من المخزون',
            'low_stock' => 'مخزون منخفض',
            'in_stock' => 'متوفر',
            default => 'غير معروف',
        };
    }
}
