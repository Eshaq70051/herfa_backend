<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يحفظ صورة مرفقة بعرض سعر.
 */
class OfferImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
