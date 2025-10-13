<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_message_likes', function (Blueprint $table) {
            $table->foreignId('question_message_id')->constrained('question_messages');
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('feedback');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_message_likes');
    }
};
