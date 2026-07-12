<?php

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentRepository implements CommentRepositoryInterface
{
    public function getCommentsForPost(int $postId, int $perPage = 20): LengthAwarePaginator
    {
        return Comment::with(['user', 'replies.user'])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    public function findById(int $id): ?Comment
    {
        return Comment::find($id);
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }
}
