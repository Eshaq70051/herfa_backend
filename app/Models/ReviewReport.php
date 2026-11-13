<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يسجل بلاغًا أو اعتراضًا على تقييم.
 */
class ReviewReport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
