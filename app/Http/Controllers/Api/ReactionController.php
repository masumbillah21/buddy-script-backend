<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Reaction\ReactToPostDTO;
use App\DTOs\Reaction\ReactToCommentDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReactionResource;
use App\Http\Resources\ReactionUserResource;
use App\Services\ReactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function __construct(
        protected ReactionService $reactionService
    ) {}

    public function reactToPost(Request $request, string $postId): JsonResponse
    {
        $request->validate([
            'reaction_type' => 'required|string|in:like,love,haha,wow,sad,angry',
        ]);

        $dto = ReactToPostDTO::fromRequest($request, $postId);
        $reaction = $this->reactionService->reactToPost($dto);

        if ($reaction) {
            return response()->json([
                'message' => 'Reaction registered',
                'data' => new ReactionResource($reaction->load('user')),
            ]);
        }

        return response()->json([
            'message' => 'Reaction removed',
            'data' => null,
        ]);
    }

    public function reactToComment(Request $request, string $commentId): JsonResponse
    {
        $request->validate([
            'reaction_type' => 'required|string|in:like,love,haha,wow,sad,angry',
        ]);

        $dto = ReactToCommentDTO::fromRequest($request, $commentId);
        $reaction = $this->reactionService->reactToComment($dto);

        if ($reaction) {
            return response()->json([
                'message' => 'Reaction registered',
                'data' => new ReactionResource($reaction->load('user')),
            ]);
        }

        return response()->json([
            'message' => 'Reaction removed',
            'data' => null,
        ]);
    }

    public function postReactions(Request $request, string $postId): JsonResponse
    {
        $type = $request->input('reaction_type');
        $reactions = $this->reactionService->getPostReactions($postId, $type, (int) $request->input('per_page', 20));

        return ReactionUserResource::collection($reactions)
            ->response();
    }

    public function commentReactions(Request $request, string $commentId): JsonResponse
    {
        $reactions = $this->reactionService->getCommentReactions($commentId, (int) $request->input('per_page', 20));

        return ReactionUserResource::collection($reactions)
            ->response();
    }
}
