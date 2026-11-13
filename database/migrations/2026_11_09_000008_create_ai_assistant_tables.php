<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 30. جدول ai_conversations — محادثات المساعد الذكي
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->enum('conversation_status', ['active', 'archived'])->default('active');
            $table->timestamps();
        });

        // 31. جدول ai_messages — رسائل المساعد الذكي
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->enum('sender_type', ['user', 'assistant']);
            $table->text('message_content');
            $table->timestamp('created_at')->useCurrent();
        });

        // 32. جدول ai_usage_logs — سجل استخدام المساعد الذكي
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->foreignId('message_id')->nullable()->constrained('ai_messages')->nullOnDelete();
            $table->string('operation_type', 100);
            $table->integer('tokens_used')->default(0);
            $table->enum('response_status', ['success', 'failed'])->default('success');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
