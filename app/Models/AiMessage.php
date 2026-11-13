<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يحفظ رسالة واحدة ضمن محادثة مع المساعد الذكي.
 */
class AiMessage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
