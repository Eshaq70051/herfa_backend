<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يسجل استهلاك خدمات الذكاء الاصطناعي المرتبط بالمستخدم أو الطلب.
 */
class AiUsageLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    public function message()
    {
        return $this->belongsTo(AiMessage::class, 'message_id');
    }
}
