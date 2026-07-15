<?php

namespace App\Services;

use App\DTOs\Reaction\ReactToPostDTO;
use App\DTOs\Reaction\ReactToCommentDTO;
use App\Models\PostReaction;
use App\Models\CommentReaction;
use App\Repositories\Contracts\ReactionRepositoryInterface;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReactionService
{
    public function __construct(
        protected ReactionRepositoryInterface $reactionRepository
    ) {}

    public function reactToPost(ReactToPostDTO $dto): ?PostReaction
    {
        return DB::transaction(function () use ($dto) {
            // Verify post exists
            $postExists = DB::table('posts')->where('id', $dto->post_id)->exists();
            if (!$postExists) {
                abort(404, 'Post not found');
            }

            // Find existing reaction
            $reaction = $this->reactionRepository->findPostReaction($dto->user_id, $dto->post_id);

            if ($reaction) {
                if ($reaction->reaction_type === $dto->reaction_type) {
                    // Toggle off: remove reaction, decrement counter
                    $this->reactionRepository->deletePostReaction($reaction);
                    DB::table('posts')->where('id', $dto->post_id)->decrement('reactions_count');
                    Cache::forget("post:{$dto->post_id}");
                    return null;
                } else {
                    // Update reaction type: counter remains unchanged
                    $this->reactionRepository->updatePostReaction($reaction, $dto->reaction_type);
                    Cache::forget("post:{$dto->post_id}");
                    return $reaction->fresh();
                }
            } else {
                // New reaction: create reaction, increment counter
                $newReaction = $this->reactionRepository->createPostReaction($dto->toArray());
                DB::table('posts')->where('id', $dto->post_id)->increment('reactions_count');
                Cache::forget("post:{$dto->post_id}");
                return $newReaction;
            }
        });
    }

    public function reactToComment(ReactToCommentDTO $dto): ?CommentReaction
    {
        return DB::transaction(function () use ($dto) {
            // Verify comment exists
            $comment = DB::table('comments')->where('id', $dto->comment_id)->first();
            if (!$comment) {
                abort(404, 'Comment not found');
            }

            // Find existing reaction
            $reaction = $this->reactionRepository->findCommentReaction($dto->user_id, $dto->comment_id);

            if ($reaction) {
                if ($reaction->reaction_type === $dto->reaction_type) {
                    // Toggle off
                    $this->reactionRepository->deleteCommentReaction($reaction);
                    DB::table('comments')->where('id', $dto->comment_id)->decrement('reactions_count');
                    Cache::forget("post:{$comment->post_id}");
                    return null;
                } else {
                    // Update type
                    $this->reactionRepository->updateCommentReaction($reaction, $dto->reaction_type);
                    Cache::forget("post:{$comment->post_id}");
                    return $reaction->fresh();
                }
            } else {
                // New reaction
                $newReaction = $this->reactionRepository->createCommentReaction($dto->toArray());
                DB::table('comments')->where('id', $dto->comment_id)->increment('reactions_count');
                Cache::forget("post:{$comment->post_id}");
                return $newReaction;
            }
        });
    }

    public function getPostReactions(string $postId, ?string $type = null, int $perPage = 20): CursorPaginator
    {
        return $this->reactionRepository->getPostReactions($postId, $type, $perPage);
    }

    public function getCommentReactions(string $commentId, int $perPage = 20): CursorPaginator
    {
        return $this->reactionRepository->getCommentReactions($commentId, $perPage);
    }

    public function getPostReactionTypesForUser(string $userId, array $postIds): array
    {
        return $this->reactionRepository->getPostReactionTypesForUser($userId, $postIds);
    }

    public function getCommentReactionTypesForUser(string $userId, array $commentIds): array
    {
        return $this->reactionRepository->getCommentReactionTypesForUser($userId, $commentIds);
    }
}
