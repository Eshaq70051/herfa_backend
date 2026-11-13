<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل عرض سعر مرتبطًا بطلب وصانع، ويحوّل السعر إلى قيمة عشرية.
 */
class OrderOffer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'proposed_price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function craftsman()
    {
        return $this->belongsTo(User::class, 'craftsman_id');
    }
}
