<?php

namespace App\Repositories\Eloquent;

use App\Models\PostReaction;
use App\Models\CommentReaction;
use App\Repositories\Contracts\ReactionRepositoryInterface;
use Illuminate\Pagination\CursorPaginator;

class ReactionRepository implements ReactionRepositoryInterface
{
    // Post reactions
    public function findPostReaction(string $userId, string $postId): ?PostReaction
    {
        return PostReaction::where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();
    }

    public function createPostReaction(array $data): PostReaction
    {
        return PostReaction::create($data);
    }

    public function deletePostReaction(PostReaction $reaction): bool
    {
        return $reaction->delete();
    }

    public function updatePostReaction(PostReaction $reaction, string $type): bool
    {
        // Since we don't have an update timestamp or standard primary key update tracking in some setups,
        // we can simply modify the reaction type directly.
        $reaction->reaction_type = $type;
        return $reaction->save();
    }

    public function getPostReactions(string $postId, ?string $type = null, int $perPage = 20): CursorPaginator
    {
        $query = PostReaction::with('user')
            ->where('post_id', $postId)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('reaction_type', $type);
        }

        return $query->cursorPaginate($perPage);
    }

    public function getPostReactionTypesForUser(string $userId, array $postIds): array
    {
        return PostReaction::where('user_id', $userId)
            ->whereIn('post_id', $postIds)
            ->pluck('reaction_type', 'post_id')
            ->toArray();
    }

    // Comment reactions
    public function findCommentReaction(string $userId, string $commentId): ?CommentReaction
    {
        return CommentReaction::where('user_id', $userId)
            ->where('comment_id', $commentId)
            ->first();
    }

    public function createCommentReaction(array $data): CommentReaction
    {
        return CommentReaction::create($data);
    }

    public function deleteCommentReaction(CommentReaction $reaction): bool
    {
        return $reaction->delete();
    }

    public function updateCommentReaction(CommentReaction $reaction, string $type): bool
    {
        $reaction->reaction_type = $type;
        return $reaction->save();
    }

    public function getCommentReactions(string $commentId, int $perPage = 20): CursorPaginator
    {
        return CommentReaction::with('user')
            ->where('comment_id', $commentId)
            ->orderBy('created_at', 'desc')
            ->cursorPaginate($perPage);
    }

    public function getCommentReactionTypesForUser(string $userId, array $commentIds): array
    {
        return CommentReaction::where('user_id', $userId)
            ->whereIn('comment_id', $commentIds)
            ->pluck('reaction_type', 'comment_id')
            ->toArray();
    }
}
