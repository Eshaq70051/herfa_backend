<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل عرض سعر يقدمه صانع لتنفيذ طلب خدمة.
 */
class Offer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['image_url'];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! empty($this->image)) {
            return str_starts_with($this->image, 'http') ? $this->image : url($this->image);
        }
        $firstImage = $this->images()->first();
        if ($firstImage && $firstImage->image_path) {
            return str_starts_with($firstImage->image_path, 'http') ? $firstImage->image_path : url($firstImage->image_path);
        }

        return null;
    }

    public function craftsman()
    {
        return $this->belongsTo(User::class, 'craftsman_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function images()
    {
        return $this->hasMany(OfferImage::class);
    }
}
