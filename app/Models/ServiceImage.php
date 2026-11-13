<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يحفظ صورة تابعة لخدمة في كتالوج التطبيق.
 */
class ServiceImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
