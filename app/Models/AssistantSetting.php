<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AssistantSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Get a setting by key, with default fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $value = Cache::remember("assistant_setting_{$key}", 3600, function () use ($key) {
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : null;
            });

            if ($value === null) {
                return $default;
            }

            // Auto-decode JSON if applicable
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): static
    {
        $serialized = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;

        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $serialized]
        );

        Cache::forget("assistant_setting_{$key}");

        return $setting;
    }
}
