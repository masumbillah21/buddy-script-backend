<?php

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository implements PostRepositoryInterface
{
    public function getFeed(int $perPage = 20): LengthAwarePaginator
    {
        return Post::with('user')
            ->where('visibility', 'public')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getUserFeed(int $userId, ?int $currentUserId, int $perPage = 20): LengthAwarePaginator
    {
        $query = Post::with('user')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($currentUserId !== $userId) {
            $query->where('visibility', 'public');
        }

        return $query->paginate($perPage);
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
