<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    public static function get(string $key, $default = null)
    {
        return Cache::remember('setting_' . $key, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) return $default;

            return match($setting->type) {
                'boolean' => (bool) $setting->value,
                'integer' => (int) $setting->value,
                'decimal' => (float) $setting->value,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, $value): bool
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => static::detectType($value)]
        );

        Cache::forget('setting_' . $key);
        return true;
    }

    public static function clearCache(): void
    {
        Cache::forget('all_settings');
        foreach (static::pluck('key') as $key) {
            Cache::forget('setting_' . $key);
        }
    }

    protected static function detectType($value): string
    {
        if (is_bool($value)) return 'boolean';
        if (is_int($value)) return 'integer';
        if (is_float($value)) return 'decimal';
        if (is_array($value)) return 'json';
        return 'string';
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
    }
}
