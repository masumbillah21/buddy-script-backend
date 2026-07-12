<?php

namespace App\Repositories\Contracts;

use App\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;

interface CommentRepositoryInterface
{
    public function getCommentsForPost(int $postId, int $perPage = 20): LengthAwarePaginator;
    public function create(array $data): Comment;
    public function findById(int $id): ?Comment;
    public function delete(Comment $comment): bool;
}
