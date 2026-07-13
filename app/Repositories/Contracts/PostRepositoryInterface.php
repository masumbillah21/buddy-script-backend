<?php

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Contracts\Pagination\CursorPaginator;

interface PostRepositoryInterface
{
    public function getFeed(int $perPage = 20): CursorPaginator;
    public function getUserFeed(int $userId, ?int $currentUserId, int $perPage = 20): CursorPaginator;
    public function create(array $data): Post;
    public function findById(int $id): ?Post;
    public function update(Post $post, array $data): bool;
    public function delete(Post $post): bool;
}
