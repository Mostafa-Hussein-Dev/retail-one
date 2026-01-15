<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description', 'ip_address', 'user_agent'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $action, ?string $description = null, ?int $userId = null): void
    {
        static::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function getRecent(int $limit = 10)
    {
        return static::with('user')->latest()->limit($limit)->get();
    }

    public static function cleanup(int $days = 30): void
    {
        static::where('created_at', '<', now()->subDays($days))->delete();
    }
}
