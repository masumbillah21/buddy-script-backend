<?php

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;

class PostRepository implements PostRepositoryInterface
{
    public function getFeed(int $perPage = 20): CursorPaginator
    {
        return Post::with('user')
            ->where('visibility', 'public')
            ->orderBy('id', 'desc')
            ->cursorPaginate($perPage);
    }

    public function getUserFeed(int $userId, ?int $currentUserId, int $perPage = 20): CursorPaginator
    {
        $query = Post::with('user')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc');

        if ($currentUserId !== $userId) {
            $query->where('visibility', 'public');
        }

        return $query->cursorPaginate($perPage);
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function findById(int $id): ?Post
    {
        return Post::with('user')->find($id);
    }

    public function update(Post $post, array $data): bool
    {
        return $post->update($data);
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }
}
