<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يربط الصانع بالخدمات التي يقدمها.
 */
class CraftsmanService extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function craftsman()
    {
        return $this->belongsTo(User::class, 'craftsman_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
