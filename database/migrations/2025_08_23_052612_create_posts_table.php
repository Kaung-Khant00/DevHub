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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('image')->nullable();
            $table->foreignId('file_id')->nullable()->constrained('files');
            $table->mediumText('code')->nullable();
            $table->string('code_lang')->nullable()->default('not specified');
            $table->text('tags')->nullable();
            $table->enum('privacy', ['public', 'private','followers_only'])->default('public');
            $table->boolean('visibility')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
