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
        Schema::create('admin_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->unique()->constrained();
            $table->enum('admin_specialty', ['post_manager', 'report_manager', 'group_manager', 'user_manager', 'support_manager', 'general_admin'])->default('general_admin');
            $table->string('office_image')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_profiles');
    }
};
