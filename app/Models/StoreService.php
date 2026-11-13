<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل خدمة معروضة ضمن المتجر أو مقدمة من متجر.
 */
class StoreService extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
        'rating' => 'decimal:1',
        'reviews_count' => 'integer',
        'display_order' => 'integer',
    ];

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute()
    {
        if (empty($this->logo)) {
            return null;
        }

        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }

        return asset($this->logo);
    }
}
