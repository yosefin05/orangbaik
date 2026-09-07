<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_unanswered_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chatbot_conversations')->cascadeOnDelete();
            $table->foreignId('message_id')->constrained('chatbot_messages')->cascadeOnDelete();
            $table->text('question');
            $table->text('normalized_question');
            $table->string('status', 20)->default('pending');
            $table->foreignId('resolved_questionnaire_id')->nullable()->constrained('chatbot_questionnaires')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_unanswered_questions');
    }
};
