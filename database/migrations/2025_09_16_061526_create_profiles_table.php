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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('name')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('cover_url')->nullable();
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('posts_count')->default(0);
            $table->unsignedBigInteger('followers_count')->default(0);
            $table->unsignedBigInteger('following_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_online')->default(false);
            $table->string('location')->nullable();
            $table->date('joined_date')->nullable();
            $table->timestamp('last_scraped_at')->nullable();
            $table->timestamps();

            $table->index(['username']);
            $table->index(['likes_count']);
            $table->index(['last_scraped_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
