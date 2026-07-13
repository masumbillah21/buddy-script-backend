<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Comment\CreateCommentDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use App\Services\ReactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService,
        protected ReactionService $reactionService
    ) {}

    public function index(Request $request, int $postId): JsonResponse
    {
        $comments = $this->commentService->getComments($postId, (int) $request->input('per_page', 20));

        $currentUserId = $request->user('sanctum')?->id;
        if ($currentUserId && $comments->isNotEmpty()) {
            $commentIds = [];
            foreach ($comments as $comment) {
                $commentIds[] = $comment->id;
                if ($comment->relationLoaded('replies')) {
                    foreach ($comment->replies as $reply) {
                        $commentIds[] = $reply->id;
                    }
                }
            }

            $myReactions = $this->reactionService->getCommentReactionTypesForUser($currentUserId, $commentIds);

            foreach ($comments as $comment) {
                $comment->setAttribute('my_reaction', $myReactions[$comment->id] ?? null);
                if ($comment->relationLoaded('replies')) {
                    foreach ($comment->replies as $reply) {
                        $reply->setAttribute('my_reaction', $myReactions[$reply->id] ?? null);
                    }
                }
            }
        }

        return CommentResource::collection($comments)
            ->response();
    }

    public function store(Request $request, int $postId): JsonResponse
    {
        $request->validate([
            'parent_id' => 'nullable|integer',
            'content' => 'required|string',
        ]);

        $dto = CreateCommentDTO::fromRequest($request, $postId);
        $comment = $this->commentService->addComment($dto);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $this->commentService->deleteComment($comment, $request->user()->id);

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }
}
