<?php

namespace App\Repositories\Contracts;

use App\Models\PostReaction;
use App\Models\CommentReaction;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReactionRepositoryInterface
{
    // Post reactions
    public function findPostReaction(int $userId, int $postId): ?PostReaction;
    public function createPostReaction(array $data): PostReaction;
    public function deletePostReaction(PostReaction $reaction): bool;
    public function updatePostReaction(PostReaction $reaction, string $type): bool;
    public function getPostReactions(int $postId, ?string $type = null, int $perPage = 20): LengthAwarePaginator;
    public function getPostReactionTypesForUser(int $userId, array $postIds): array;

    // Comment reactions
    public function findCommentReaction(int $userId, int $commentId): ?CommentReaction;
    public function createCommentReaction(array $data): CommentReaction;
    public function deleteCommentReaction(CommentReaction $reaction): bool;
    public function updateCommentReaction(CommentReaction $reaction, string $type): bool;
    public function getCommentReactions(int $commentId, int $perPage = 20): LengthAwarePaginator;
    public function getCommentReactionTypesForUser(int $userId, array $commentIds): array;
}
