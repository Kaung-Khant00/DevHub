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
        Schema::create('developer_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('developer_id')->constrained('users');
            $table->foreignId('rated_by')->constrained('users');
            $table->enum('rating',[1,2,3,4,5]);
            $table->text('review')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developer_ratings');
    }
};
