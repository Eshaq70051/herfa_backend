<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يعرّف نوع الإشعار وبياناته الوصفية.
 */
class NotificationType extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
