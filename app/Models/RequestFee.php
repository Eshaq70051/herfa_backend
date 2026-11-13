<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل رسمًا أو تكلفة إضافية مسجلة على طلب خدمة.
 */
class RequestFee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'fee_amount' => 'decimal:2',
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }
}
