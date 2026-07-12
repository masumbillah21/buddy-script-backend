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
        Schema::create('post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('reaction_type', 20); // like, love, haha, wow, sad, angry
            $table->timestamp('created_at')->useCurrent();

            // Composite unique index to enforce single reaction per user per post
            $table->unique(['user_id', 'post_id']);

            // Indexes for querying lists and aggregations
            $table->index(['post_id', 'created_at']);
            $table->index(['post_id', 'reaction_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_reactions');
    }
};
