<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل خدمة في كتالوج التطبيق، بما يشمل بياناتها وأسعارها وصورها.
 */
class Service extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['image_url', 'base_price', 'warranty_days'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getNameArAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function getIconAttribute(): ?string
    {
        if (! empty($this->attributes['icon'])) {
            return $this->attributes['icon'];
        }
        if ($this->relationLoaded('category') && $this->category && ! empty($this->category->icon)) {
            return $this->category->icon;
        }

        return 'fa-wrench';
    }

    public function getBasePriceAttribute(): float
    {
        if (isset($this->attributes['base_price']) && (float) $this->attributes['base_price'] > 0) {
            return (float) $this->attributes['base_price'];
        }

        $price = $this->prices()->where('is_active', true)->first();
        if ($price) {
            return (float) ($price->fixed_price ?? $price->first_hour_price ?? 0);
        }

        return 50.00;
    }

    public function getWarrantyDaysAttribute(): int
    {
        return isset($this->attributes['warranty_days']) ? (int) $this->attributes['warranty_days'] : 30;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! empty($this->image)) {
            return str_starts_with($this->image, 'http') ? $this->image : url($this->image);
        }
        $firstImage = $this->images()->first();
        if ($firstImage && $firstImage->image_path) {
            return str_starts_with($firstImage->image_path, 'http') ? $firstImage->image_path : url($firstImage->image_path);
        }
        if ($this->relationLoaded('category') && $this->category) {
            return $this->category->image_url;
        }

        return null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function prices()
    {
        return $this->hasMany(ServicePrice::class);
    }

    public function images()
    {
        return $this->hasMany(ServiceImage::class);
    }

    public function craftsmen()
    {
        return $this->belongsToMany(User::class, 'craftsman_services', 'service_id', 'craftsman_id');
    }

    public function requests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }
}
