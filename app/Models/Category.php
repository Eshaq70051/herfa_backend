<?php

// لإدارة التصنيفات1

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // بيانات التجريبيه
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل تصنيفًا للخدمات، ويجهز بياناته وعلاقته بالخدمات التابعة له.
 */
class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // يستقبل رابطًا جاهزًا من Laravel.
    protected $appends = ['image_url']; // اسم افتراضي

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getNameArAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return url($this->image);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
