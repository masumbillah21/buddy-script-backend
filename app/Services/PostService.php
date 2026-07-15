<?php

namespace App\Services;

use App\DTOs\Post\CreatePostDTO;
use App\DTOs\Post\UpdatePostDTO;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Cache;

class PostService
{
    /** Cache TTL in seconds (10 minutes) */
    private const CACHE_TTL = 600;

    public function __construct(
        protected PostRepositoryInterface $postRepository
    ) {}

    public function getFeed(int $perPage = 20): CursorPaginator
    {
        return $this->postRepository->getFeed($perPage);
    }

    public function getUserFeed(string $userId, ?string $currentUserId, int $perPage = 20): CursorPaginator
    {
        return $this->postRepository->getUserFeed($userId, $currentUserId, $perPage);
    }

    public function createPost(CreatePostDTO $dto): Post
    {
        $post = $this->postRepository->create($dto->toArray());
        Cache::put("post:{$post->id}", $post, self::CACHE_TTL);
        return $post;
    }

    public function getPost(string $id, ?string $currentUserId): Post
    {
        $post = Cache::remember("post:{$id}", self::CACHE_TTL, function () use ($id) {
            return $this->postRepository->findById($id);
        });

        if (!$post) {
            abort(404, 'Post not found');
        }

        if ($post->visibility === 'private' && (!$currentUserId || $post->user_id !== $currentUserId)) {
            throw new AuthorizationException('This post is private.');
        }

        return $post;
    }

    public function updatePost(Post $post, UpdatePostDTO $dto, string $userId): Post
    {
        if ($post->user_id !== $userId) {
            throw new AuthorizationException('You are not authorized to update this post.');
        }

        $this->postRepository->update($post, $dto->data);

        Cache::forget("post:{$post->id}");

        return $post->fresh(['user']);
    }

    public function deletePost(Post $post, string $userId): bool
    {
        if ($post->user_id !== $userId) {
            throw new AuthorizationException('You are not authorized to delete this post.');
        }

        $result = $this->postRepository->delete($post);

        Cache::forget("post:{$post->id}");

        return $result;
    }
}
