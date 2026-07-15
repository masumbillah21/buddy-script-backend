<?php

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Pagination\CursorPaginator;

class CommentRepository implements CommentRepositoryInterface
{
    public function getCommentsForPost(string $postId, int $perPage = 20): CursorPaginator
    {
        return Comment::with(['user', 'replies.user'])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->cursorPaginate($perPage);
    }

    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    public function findById(string $id): ?Comment
    {
        return Comment::find($id);
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }
}
