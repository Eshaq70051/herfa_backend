<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل تقييم العميل للخدمة أو للصانع بعد تنفيذ الطلب.
 */
class Review extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function craftsman()
    {
        return $this->belongsTo(User::class, 'craftsman_id');
    }

    public function replies()
    {
        return $this->hasMany(ReviewReply::class);
    }

    public function reports()
    {
        return $this->hasMany(ReviewReport::class);
    }
}
