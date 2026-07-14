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

        if ($comments->isNotEmpty()) {
            $commentIds = [];
            foreach ($comments as $comment) {
                $commentIds[] = $comment->id;
                if ($comment->relationLoaded('replies')) {
                    foreach ($comment->replies as $reply) {
                        $commentIds[] = $reply->id;
                    }
                }
            }

            // Get distinct reaction types for comments
            $reactionTypes = \Illuminate\Support\Facades\DB::table('comment_reactions')
                ->whereIn('comment_id', $commentIds)
                ->select('comment_id', 'reaction_type')
                ->distinct()
                ->get()
                ->groupBy('comment_id')
                ->map(fn($items) => $items->pluck('reaction_type')->toArray())
                ->toArray();

            $currentUserId = $request->user('sanctum')?->id;
            $myReactions = $currentUserId 
                ? $this->reactionService->getCommentReactionTypesForUser($currentUserId, $commentIds)
                : [];

            foreach ($comments as $comment) {
                $comment->setAttribute('my_reaction', $myReactions[$comment->id] ?? null);
                $comment->setAttribute('reaction_types', $reactionTypes[$comment->id] ?? []);
                if ($comment->relationLoaded('replies')) {
                    foreach ($comment->replies as $reply) {
                        $reply->setAttribute('my_reaction', $myReactions[$reply->id] ?? null);
                        $reply->setAttribute('reaction_types', $reactionTypes[$reply->id] ?? []);
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
            'content' => 'required|string|max:5000',
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
