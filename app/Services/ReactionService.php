<?php

namespace App\Services;

use App\DTOs\Reaction\ReactToPostDTO;
use App\DTOs\Reaction\ReactToCommentDTO;
use App\Models\PostReaction;
use App\Models\CommentReaction;
use App\Repositories\Contracts\ReactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
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
                    return null;
                } else {
                    // Update reaction type: counter remains unchanged
                    $this->reactionRepository->updatePostReaction($reaction, $dto->reaction_type);
                    return $reaction->fresh();
                }
            } else {
                // New reaction: create reaction, increment counter
                $newReaction = $this->reactionRepository->createPostReaction($dto->toArray());
                DB::table('posts')->where('id', $dto->post_id)->increment('reactions_count');
                return $newReaction;
            }
        });
    }

    public function reactToComment(ReactToCommentDTO $dto): ?CommentReaction
    {
        return DB::transaction(function () use ($dto) {
            // Verify comment exists
            $commentExists = DB::table('comments')->where('id', $dto->comment_id)->exists();
            if (!$commentExists) {
                abort(404, 'Comment not found');
            }

            // Find existing reaction
            $reaction = $this->reactionRepository->findCommentReaction($dto->user_id, $dto->comment_id);

            if ($reaction) {
                if ($reaction->reaction_type === $dto->reaction_type) {
                    // Toggle off
                    $this->reactionRepository->deleteCommentReaction($reaction);
                    DB::table('comments')->where('id', $dto->comment_id)->decrement('reactions_count');
                    return null;
                } else {
                    // Update type
                    $this->reactionRepository->updateCommentReaction($reaction, $dto->reaction_type);
                    return $reaction->fresh();
                }
            } else {
                // New reaction
                $newReaction = $this->reactionRepository->createCommentReaction($dto->toArray());
                DB::table('comments')->where('id', $dto->comment_id)->increment('reactions_count');
                return $newReaction;
            }
        });
    }

    public function getPostReactions(int $postId, ?string $type = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->reactionRepository->getPostReactions($postId, $type, $perPage);
    }

    public function getCommentReactions(int $commentId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->reactionRepository->getCommentReactions($commentId, $perPage);
    }

    public function getPostReactionTypesForUser(int $userId, array $postIds): array
    {
        return $this->reactionRepository->getPostReactionTypesForUser($userId, $postIds);
    }

    public function getCommentReactionTypesForUser(int $userId, array $commentIds): array
    {
        return $this->reactionRepository->getCommentReactionTypesForUser($userId, $commentIds);
    }
}
