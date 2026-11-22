<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يحفظ بيانات جهاز المستخدم، مثل رمز الإشعارات المرتبط به.
 */
class UserDevice extends Model
{
    use HasFactory;

    protected $table = 'devices_user';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
