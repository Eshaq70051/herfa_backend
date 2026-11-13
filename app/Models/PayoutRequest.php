<?php

// سحب الاموال من الحرفي

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل طلب سحب رصيد يقدمه المستخدم من محفظته.
 */
class PayoutRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function craftsman()
    {
        return $this->belongsTo(User::class, 'craftsman_id');
    }
}
