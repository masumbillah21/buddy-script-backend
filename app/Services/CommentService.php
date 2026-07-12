<?php

namespace App\Services;

use App\DTOs\Comment\CreateCommentDTO;
use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function __construct(
        protected CommentRepositoryInterface $commentRepository
    ) {}

    public function getComments(int $postId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->commentRepository->getCommentsForPost($postId, $perPage);
    }

    public function addComment(CreateCommentDTO $dto): Comment
    {
        return DB::transaction(function () use ($dto) {
            // Verify post exists
            $postExists = DB::table('posts')->where('id', $dto->post_id)->exists();
            if (!$postExists) {
                abort(404, 'Post not found');
            }

            // Verify parent comment exists (if reply)
            if ($dto->parent_id) {
                $parentComment = DB::table('comments')
                    ->where('id', $dto->parent_id)
                    ->where('post_id', $dto->post_id) // must belong to same post
                    ->first();
                if (!$parentComment) {
                    abort(400, 'Parent comment not found or belongs to a different post');
                }
            }

            $comment = $this->commentRepository->create($dto->toArray());

            // Increment post comments_count
            DB::table('posts')->where('id', $dto->post_id)->increment('comments_count');

            // Increment parent replies_count if this is a reply
            if ($dto->parent_id) {
                DB::table('comments')->where('id', $dto->parent_id)->increment('replies_count');
            }

            return $comment->load('user');
        });
    }

    public function deleteComment(Comment $comment, int $userId): bool
    {
        return DB::transaction(function () use ($comment, $userId) {
            if ($comment->user_id !== $userId) {
                throw new AuthorizationException('You are not authorized to delete this comment.');
            }

            // Count the comment itself + any direct replies (replies are cascade deleted)
            $repliesCount = DB::table('comments')->where('parent_id', $comment->id)->count();
            $totalDeleted = 1 + $repliesCount;

            // Decrement post comments_count
            DB::table('posts')->where('id', $comment->post_id)->decrement('comments_count', $totalDeleted);

            // If it's a nested reply, decrement replies_count on parent
            if ($comment->parent_id) {
                DB::table('comments')->where('id', $comment->parent_id)->decrement('replies_count');
            }

            $this->commentRepository->delete($comment);

            return true;
        });
    }
}
