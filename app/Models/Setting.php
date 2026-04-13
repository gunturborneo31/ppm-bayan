<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'label', 'description'];
    protected $casts = [
        'value' => 'json',
    ];

    public static function tableExists(): bool
    {
        try {
            return Schema::hasTable((new static())->getTable());
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        if (!self::tableExists()) {
            return $default;
        }

        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key
     */
    public static function setValue($key, $value, $label = null, $description = null)
    {
        if (!self::tableExists()) {
            return null;
        }

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'label' => $label ?? $key,
                'description' => $description,
            ]
        );
    }

    /**
     * Check if perencanaan/planning is locked
     */
    public static function isPlanningLocked()
    {
        return self::getValue('planning_locked', false);
    }

    /**
     * Lock perencanaan/planning
     */
    public static function lockPlanning($locked = true)
    {
        return self::setValue('planning_locked', $locked, 'Kunci Perencanaan', 'Mengunci input perencanaan untuk semua user non-superadmin');
    }
}
