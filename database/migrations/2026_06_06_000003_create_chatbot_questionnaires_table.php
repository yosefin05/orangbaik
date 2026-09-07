<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_questionnaires', function (Blueprint $table): void {
            $table->id();
            $table->text('question');
            $table->longText('answer');
            $table->text('keywords')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('chatbot_categories')->nullOnDelete();
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_questionnaires');
    }
};
