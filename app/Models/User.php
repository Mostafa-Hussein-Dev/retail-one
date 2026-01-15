<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Check if user is a manager
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is a cashier
     */
    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * Get role display name in Arabic
     */
    public function getRoleDisplayAttribute(): string
    {
        return $this->role === 'manager' ? 'مدير' : 'أمين صندوق';
    }

    /**
     * Get user status display
     */
    public function getStatusDisplayAttribute(): string
    {
        if ($this->trashed()) {
            return 'محذوف';
        }
        return $this->is_active ? 'نشط' : 'غير نشط';
    }

    /**
     * Get user status color class
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->trashed()) {
            return '#e74c3c'; // red for deleted
        }
        return $this->is_active ? '#27ae60' : '#95a5a6'; // green for active, gray for inactive
    }

    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get managers only
     */
    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    /**
     * Scope to get cashiers only
     */
    public function scopeCashiers($query)
    {
        return $query->where('role', 'cashier');
    }
}
