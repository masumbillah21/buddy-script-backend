<?php

namespace App\Repositories\Contracts;

use App\Models\PostReaction;
use App\Models\CommentReaction;
use Illuminate\Pagination\CursorPaginator;

interface ReactionRepositoryInterface
{
    // Post reactions
    public function findPostReaction(string $userId, string $postId): ?PostReaction;
    public function createPostReaction(array $data): PostReaction;
    public function deletePostReaction(PostReaction $reaction): bool;
    public function updatePostReaction(PostReaction $reaction, string $type): bool;
    public function getPostReactions(string $postId, ?string $type = null, int $perPage = 20): CursorPaginator;
    public function getPostReactionTypesForUser(string $userId, array $postIds): array;

    // Comment reactions
    public function findCommentReaction(string $userId, string $commentId): ?CommentReaction;
    public function createCommentReaction(array $data): CommentReaction;
    public function deleteCommentReaction(CommentReaction $reaction): bool;
    public function updateCommentReaction(CommentReaction $reaction, string $type): bool;
    public function getCommentReactions(string $commentId, int $perPage = 20): CursorPaginator;
    public function getCommentReactionTypesForUser(string $userId, array $commentIds): array;
}
