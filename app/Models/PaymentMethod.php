<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يعرّف وسائل الدفع المتاحة لإتمام العمليات المالية.
 */
class PaymentMethod extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return str_starts_with($this->image, 'http') ? $this->image : asset($this->image);
        }

        return null;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
