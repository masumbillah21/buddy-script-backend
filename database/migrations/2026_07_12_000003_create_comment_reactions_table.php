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
        Schema::create('comment_reactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->string('reaction_type', 20); // like, love, haha, wow, sad, angry
            $table->timestamp('created_at')->useCurrent();

            // Composite unique index to enforce single reaction per user per comment
            $table->unique(['user_id', 'comment_id']);

            // Index for querying reaction list
            $table->index(['comment_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};
