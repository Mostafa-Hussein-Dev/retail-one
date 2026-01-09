<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'total_debt',
        'credit_limit',
        'is_active',
    ];

    protected $casts = [
        'total_debt' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'display_name',
        'debt_status',
        'available_credit',
        'debt_status_text',
        'debt_status_color',
        'can_purchase_on_debt',
    ];

    /**
     * Get sales for this customer
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get debt transactions for this customer
     */
    public function debtTransactions(): HasMany
    {
        return $this->hasMany(CustomerDebtTransaction::class);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->phone ? " ({$this->phone})" : '');
    }

    public function getDebtStatusAttribute(): string
    {
        if ($this->total_debt == 0) return 'no_debt';
        if ($this->total_debt > 0) return 'has_debt';
        return 'credit_balance'; // Negative debt means customer has credit
    }

    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->total_debt);
    }

    public function getDebtStatusTextAttribute(): string
    {
        return match($this->debt_status) {
            'no_debt' => 'لا توجد مديونية',
            'has_debt' => 'يوجد مديونية',
            'credit_balance' => 'رصيد دائن',
            default => 'غير معروف',
        };
    }

    public function getDebtStatusColorAttribute(): string
    {
        return match($this->debt_status) {
            'no_debt' => '#27ae60',
            'has_debt' => '#e74c3c',
            'credit_balance' => '#3498db',
            default => '#95a5a6',
        };
    }

    public function getCanPurchaseOnDebtAttribute(): bool
    {
        return $this->is_active && $this->total_debt < $this->credit_limit;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithDebt($query)
    {
        return $query->where('total_debt', '>', 0);
    }

    public function scopeOverCreditLimit($query)
    {
        return $query->whereRaw('total_debt > credit_limit');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Business Logic Methods
    public function addDebt(float $amount, string $description = null, int $saleId = null): void
    {
        $this->increment('total_debt', $amount);

        $this->debtTransactions()->create([
            'sale_id' => $saleId,
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

    public function canPurchaseAmount(float $amount): bool
    {
        if (!$this->is_active) return false;

        $newDebtTotal = $this->total_debt + $amount;
        return $newDebtTotal <= $this->credit_limit;
    }

    public function getTotalSales(): float
    {
        return $this->sales()->sum('total_amount');
    }

    public function getTotalSalesCount(): int
    {
        return $this->sales()->count();
    }

    public function getLastSaleDate(): ?\Carbon\Carbon
    {
        $lastSale = $this->sales()->latest('sale_date')->first();
        return $lastSale ? $lastSale->sale_date : null;
    }

    public function getDebtTransactionHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->debtTransactions()->latest('transaction_date')->get();
    }

    public function hasDebt(): bool
    {
        return $this->total_debt > 0;
    }

    public function isOverCreditLimit(): bool
    {
        return $this->total_debt > $this->credit_limit;
    }

    public function getCreditUtilizationPercentage(): float
    {
        if ($this->credit_limit == 0) return 0;
        return ($this->total_debt / $this->credit_limit) * 100;
    }

    public function getDebtAging(): array
    {
        $aging = [
            'current' => 0,      // 0-30 days
            'overdue_30' => 0,   // 31-60 days
            'overdue_60' => 0,   // 61-90 days
            'overdue_90' => 0,   // 90+ days
        ];

        $debtSales = $this->sales()->where('debt_amount', '>', 0)->get();

        foreach ($debtSales as $sale) {
            $daysDiff = now()->diffInDays($sale->sale_date);

            if ($daysDiff <= 30) {
                $aging['current'] += $sale->debt_amount;
            } elseif ($daysDiff <= 60) {
                $aging['overdue_30'] += $sale->debt_amount;
            } elseif ($daysDiff <= 90) {
                $aging['overdue_60'] += $sale->debt_amount;
            } else {
                $aging['overdue_90'] += $sale->debt_amount;
            }
        }

        return $aging;
    }

    public function getPaymentHistory(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->debtTransactions()
            ->where('transaction_type', 'payment')
            ->latest('transaction_date')
            ->limit($limit)
            ->get();
    }

    public function getAverageOrderValue(): float
    {
        $totalSales = $this->getTotalSales();
        $salesCount = $this->getTotalSalesCount();

        return $salesCount > 0 ? $totalSales / $salesCount : 0;
    }

    // Static Methods
    public static function getActiveCustomers()
    {
        return static::active()->get();
    }

    public static function getCustomersWithDebt()
    {
        return static::withDebt()->active()->get();
    }

    public static function getOverCreditLimitCustomers()
    {
        return static::overCreditLimit()->active()->get();
    }

    public static function getTotalDebtAmount(): float
    {
        return static::sum('total_debt');
    }

    public static function searchCustomers(string $search)
    {
        return static::active()->search($search)->limit(10)->get();
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (is_null($customer->is_active)) {
                $customer->is_active = true;
            }
            if (is_null($customer->total_debt)) {
                $customer->total_debt = 0.00;
            }
            if (is_null($customer->credit_limit)) {
                $customer->credit_limit = 500.00; // Default credit limit
            }
        });

        static::updating(function ($customer) {
            // Ensure debt doesn't go below 0
            if ($customer->total_debt < 0) {
                $customer->total_debt = 0.00;
            }
        });
    }

    // Helper Methods for POS
    public function getFormattedDebt(): string
    {
        return '$' . number_format($this->total_debt, 2);
    }

    public function getFormattedCreditLimit(): string
    {
        return '$' . number_format($this->credit_limit, 2);
    }

    public function getFormattedAvailableCredit(): string
    {
        return '$' . number_format($this->available_credit, 2);
    }

    public function canAfford(float $amount): bool
    {
        return $this->canPurchaseAmount($amount);
    }

    public function getRiskLevel(): string
    {
        if (!$this->hasDebt()) return 'low';

        $utilization = $this->getCreditUtilizationPercentage();

        if ($utilization >= 90) return 'high';
        if ($utilization >= 70) return 'medium';
        return 'low';
    }

    public function getRiskLevelColor(): string
    {
        return match($this->getRiskLevel()) {
            'low' => '#27ae60',
            'medium' => '#f39c12',
            'high' => '#e74c3c',
            default => '#95a5a6',
        };
    }

    public function getRiskLevelText(): string
    {
        return match($this->getRiskLevel()) {
            'low' => 'مخاطر منخفضة',
            'medium' => 'مخاطر متوسطة',
            'high' => 'مخاطر عالية',
            default => 'غير محدد',
        };
    }
}
