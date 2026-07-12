<?php

namespace App\Services;

use App\DTOs\Post\CreatePostDTO;
use App\DTOs\Post\UpdatePostDTO;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService
{
    public function __construct(
        protected PostRepositoryInterface $postRepository
    ) {}

    public function getFeed(int $perPage = 20): LengthAwarePaginator
    {
        return $this->postRepository->getFeed($perPage);
    }

    public function getUserFeed(int $userId, ?int $currentUserId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->postRepository->getUserFeed($userId, $currentUserId, $perPage);
    }

    public function createPost(CreatePostDTO $dto): Post
    {
        return $this->postRepository->create($dto->toArray());
    }

    public function getPost(int $id, ?int $currentUserId): Post
    {
        $post = $this->postRepository->findById($id);

        if (!$post) {
            abort(404, 'Post not found');
        }

        if ($post->visibility === 'private' && (!$currentUserId || $post->user_id !== $currentUserId)) {
            throw new AuthorizationException('This post is private.');
        }

        return $post;
    }

    public function updatePost(Post $post, UpdatePostDTO $dto, int $userId): Post
    {
        if ($post->user_id !== $userId) {
            throw new AuthorizationException('You are not authorized to update this post.');
        }

        $this->postRepository->update($post, $dto->data);

        return $post->fresh(['user']);
    }

    public function deletePost(Post $post, int $userId): bool
    {
        if ($post->user_id !== $userId) {
            throw new AuthorizationException('You are not authorized to delete this post.');
        }

        return $this->postRepository->delete($post);
    }
}
