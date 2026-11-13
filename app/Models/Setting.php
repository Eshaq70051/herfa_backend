<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يخزن إعدادًا من إعدادات النظام القابلة للضبط ويرتبط بجدول app_settings.
 */
class Setting extends Model
{
    use HasFactory;

    protected $table = 'app_settings';

    protected $guarded = ['id'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('setting_key', $key)->first();

        return $setting ? $setting->setting_value : $default;
    }

    public static function set(string $key, $value)
    {
        return static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}
