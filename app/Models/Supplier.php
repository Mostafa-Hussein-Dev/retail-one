<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'address',
        'total_debt',
        'is_active',
    ];

    protected $casts = [
        'total_debt' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'display_name',
        'debt_status',
    ];

    /**
     * Get products from this supplier
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get active products from this supplier
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    /**
     * Get purchases from this supplier
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get debt transactions for this supplier
     */
    public function debtTransactions(): HasMany
    {
        return $this->hasMany(SupplierDebtTransaction::class);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->contact_person ? " ({$this->contact_person})" : '');
    }

    public function getDebtStatusAttribute(): string
    {
        if ($this->total_debt == 0) return 'no_debt';
        if ($this->total_debt > 0) return 'we_owe'; // We owe the supplier
        return 'supplier_owes'; // Supplier owes us (negative debt)
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithDebt($query)
    {
        return $query->where('total_debt', '!=', 0);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Business Logic Methods
    public function addDebt(float $amount, string $description = null, int $purchaseId = null): void
    {
        $this->increment('total_debt', $amount);

        $this->debtTransactions()->create([
            'purchase_id' => $purchaseId,
            'transaction_type' => 'debt',
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    public function payDebt(float $amount, string $description = null): bool
    {
        if ($amount > $this->total_debt) {
            return false; // Cannot pay more than owed
        }

        $this->decrement('total_debt', $amount);

        $this->debtTransactions()->create([
            'transaction_type' => 'payment',
            'amount' => $amount,
            'description' => $description,
        ]);

        return true;
    }

    public function getProductsCount(): int
    {
        return $this->products()->count();
    }

    public function getActiveProductsCount(): int
    {
        return $this->activeProducts()->count();
    }

    public function getTotalPurchases(): float
    {
        return $this->purchases()->sum('total_amount');
    }

    public function hasDebt(): bool
    {
        return $this->total_debt != 0;
    }

    public function getDebtStatusText(): string
    {
        return match ($this->debt_status) {
            'no_debt' => 'لا توجد مديونية',
            'we_owe' => 'ندين للمورد',
            'supplier_owes' => 'المورد مدين لنا',
            default => 'غير معروف',
        };
    }

    public function getDebtStatusColor(): string
    {
        return match ($this->debt_status) {
            'no_debt' => '#27ae60',
            'we_owe' => '#e74c3c',
            'supplier_owes' => '#3498db',
            default => '#95a5a6',
        };
    }

    // Static Methods
    public static function getActiveSuppliers()
    {
        return static::active()->get();
    }

    public static function getSuppliersWithDebt()
    {
        return static::withDebt()->active()->get();
    }
}
