<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يحفظ صورة مرتبطة بإعلان واحد.
 */
class AdvertisementImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }
}
