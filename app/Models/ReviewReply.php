<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يحفظ ردًا على تقييم منشور.
 */
class ReviewReply extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function craftsman()
    {
        return $this->belongsTo(User::class, 'craftsman_id');
    }
}
