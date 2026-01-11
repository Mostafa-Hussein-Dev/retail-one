<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'credit_limit',
        'is_active',
    ];

    protected $casts = [
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

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function debtTransactions(): HasMany
    {
        return $this->hasMany(CustomerDebtTransaction::class);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->phone ? " ({$this->phone})" : '');
    }

    /**
     * Calculate total debt from transactions (not stored)
     * Debt = sum of all non-voided transaction amounts
     */
    public function getTotalDebtAttribute(): float
    {
        // If the model was just loaded from DB and we want to avoid N+1 queries,
        // we could cache this, but for now calculate fresh
        return $this->debtTransactions()
            ->whereNull('voided_at')
            ->sum('amount') ?? 0;
    }

    public function getDebtStatusAttribute(): string
    {
        if ($this->total_debt == 0) return 'no_debt';
        if ($this->total_debt > 0) return 'has_debt';
        return 'credit_balance';
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
        return $query->whereExists(function ($subquery) {
            $subquery->select(DB::raw(1))
                ->from('customer_debt_transactions')
                ->whereColumn('customer_debt_transactions.customer_id', 'customers.id')
                ->whereNull('voided_at')
                ->havingRaw('SUM(amount) > 0');
        });
    }

    public function scopeOverCreditLimit($query)
    {
        return $query->whereExists(function ($subquery) {
            $subquery->select(DB::raw(1))
                ->from('customer_debt_transactions')
                ->whereColumn('customer_debt_transactions.customer_id', 'customers.id')
                ->whereNull('voided_at')
                ->havingRaw('SUM(amount) > customers.credit_limit');
        });
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
            return false;
        }

        $this->debtTransactions()->create([
            'transaction_type' => 'payment',
            'amount' => -$amount, // Negative amount reduces debt
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
        return $lastSale ?$lastSale->sale_date : null;
    }

    public function getDebtTransactionHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->debtTransactions()->latest('created_at')->get();
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
            'current' => 0,
            'overdue_30' => 0,
            'overdue_60' => 0,
            'overdue_90' => 0,
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
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function getAverageOrderValue(): float
    {
        $totalSales = $this->getTotalSales();
        $salesCount = $this->getTotalSalesCount();

        return $salesCount > 0 ? $totalSales / $salesCount : 0;
    }

    // ===== PHASE 4 NEW METHODS =====

    /**
     * Get sales with outstanding debt (non-voided, debt_amount > 0)
     */
    public function getSalesWithDebt()
    {
        return $this->sales()
            ->where('is_voided', false)
            ->where('debt_amount', '>', 0)
            ->orderBy('sale_date', 'desc')
            ->get();
    }

    /**
     * Get fully paid debt sales (non-voided, was debt sale, now debt_amount = 0)
     */
    public function getFullyPaidDebtSales()
    {
        return $this->sales()
            ->where('is_voided', false)
            ->where('payment_method', 'debt')
            ->where('debt_amount', 0)
            ->orderBy('sale_date', 'desc')
            ->get();
    }

    /**
     * Get transaction history ordered by date
     */
    public function getTransactionHistory()
    {
        return $this->debtTransactions()
            ->with('sale')
            ->orderBy('created_at', 'desc')
            ->get();
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
        // Calculate from transactions instead of summing stored values
        return CustomerDebtTransaction::whereNull('voided_at')
            ->sum('amount') ?? 0;
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
            if (is_null($customer->credit_limit)) {
                $customer->credit_limit = 500.00;
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
