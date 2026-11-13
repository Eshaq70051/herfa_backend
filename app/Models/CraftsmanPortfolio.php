<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يحفظ عنصرًا من معرض أعمال الصانع.
 */
class CraftsmanPortfolio extends Model
{
    use HasFactory;

    protected $table = 'craftsman_portfolio';

    protected $guarded = ['id'];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function craftsman()
    {
        return $this->belongsTo(User::class, 'craftsman_id');
    }
}
