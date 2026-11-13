<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يحفظ بندًا أو معلومة تفصيلية تابعة لطلب خدمة.
 */
class RequestDetail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }
}
