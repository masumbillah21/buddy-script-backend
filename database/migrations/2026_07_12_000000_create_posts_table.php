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
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->string('image_path')->nullable();
            $table->string('video_path')->nullable();
            $table->string('title')->nullable();
            $table->string('type', 20)->default('text');
            $table->dateTime('event_date')->nullable();
            $table->string('visibility', 20)->default('public'); // public, private
            $table->integer('reactions_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->timestamps();

            // Indexes for scalability
            $table->index(['visibility', 'created_at']);
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
