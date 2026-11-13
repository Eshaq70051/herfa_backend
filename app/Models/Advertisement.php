<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل إعلانًا في التطبيق، ويجهز رابطه وصوره وتواريخ ظهوره.
 */
class Advertisement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['image_url'];

    protected $casts = [
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images()
    {
        return $this->hasMany(AdvertisementImage::class);
    }
}
