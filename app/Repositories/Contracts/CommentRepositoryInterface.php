<?php

namespace App\Repositories\Contracts;

use App\Models\Comment;
use Illuminate\Pagination\CursorPaginator;

interface CommentRepositoryInterface
{
    public function getCommentsForPost(string $postId, int $perPage = 20): CursorPaginator;
    public function create(array $data): Comment;
    public function findById(string $id): ?Comment;
    public function delete(Comment $comment): bool;
}
