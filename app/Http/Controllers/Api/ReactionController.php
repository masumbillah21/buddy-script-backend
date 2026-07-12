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
use OpenApi\Attributes as OA;

class ReactionController extends Controller
{
    public function __construct(
        protected ReactionService $reactionService
    ) {}

    #[OA\Post(
        path: "/api/posts/{postId}/react",
        summary: "React to Post",
        description: "Add, update, or remove reaction on a post. Sending the exact same reaction_type will toggle it off (delete it).",
        security: [["sanctum" => []]],
        tags: ["Reactions"],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", description: "Target Post ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["reaction_type"],
                properties: [
                    new OA\Property(property: "reaction_type", type: "string", enum: ["like", "love", "haha", "wow", "sad", "angry"], example: "like")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Reaction registered or updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Reaction registered"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Reaction", nullable: true)
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function reactToPost(Request $request, int $postId): JsonResponse
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

    #[OA\Post(
        path: "/api/comments/{commentId}/react",
        summary: "React to Comment",
        description: "Add, update, or remove reaction on a comment/reply. Sending the exact same reaction_type toggles it off.",
        security: [["sanctum" => []]],
        tags: ["Reactions"],
        parameters: [
            new OA\Parameter(name: "commentId", in: "path", description: "Target Comment ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["reaction_type"],
                properties: [
                    new OA\Property(property: "reaction_type", type: "string", enum: ["like", "love", "haha", "wow", "sad", "angry"], example: "love")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Reaction updated or toggled successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Reaction registered"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Reaction", nullable: true)
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Comment not found")
        ]
    )]
    public function reactToComment(Request $request, int $commentId): JsonResponse
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

    #[OA\Get(
        path: "/api/posts/{postId}/reactions",
        summary: "Get Post Reaction List",
        description: "Fetch a paginated list of users who reacted to a post. Optionally filter by reaction_type.",
        tags: ["Reactions"],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", description: "Target Post ID", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "reaction_type", in: "query", description: "Filter by type (like, love, etc.)", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", required: false, schema: new OA\Schema(type: "integer", default: 20))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Reaction list retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/ReactionUser"))
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function postReactions(Request $request, int $postId): JsonResponse
    {
        $type = $request->input('reaction_type');
        $reactions = $this->reactionService->getPostReactions($postId, $type, (int) $request->input('per_page', 20));

        return ReactionUserResource::collection($reactions)
            ->response();
    }

    #[OA\Get(
        path: "/api/comments/{commentId}/reactions",
        summary: "Get Comment Reaction List",
        description: "Fetch a paginated list of users who reacted to a specific comment/reply.",
        tags: ["Reactions"],
        parameters: [
            new OA\Parameter(name: "commentId", in: "path", description: "Target Comment ID", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", required: false, schema: new OA\Schema(type: "integer", default: 20))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Reaction list retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/ReactionUser"))
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Comment not found")
        ]
    )]
    public function commentReactions(Request $request, int $commentId): JsonResponse
    {
        $reactions = $this->reactionService->getCommentReactions($commentId, (int) $request->input('per_page', 20));

        return ReactionUserResource::collection($reactions)
            ->response();
    }
}

// Swagger Schemas for Reactions
namespace App\Http\Controllers\Api;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Reaction",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "user", ref: "#/components/schemas/User"),
        new OA\Property(property: "reaction_type", type: "string", example: "like"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-12T13:22:00Z")
    ]
)]
class ReactionSchema {}

#[OA\Schema(
    schema: "ReactionUser",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "first_name", type: "string", example: "John"),
        new OA\Property(property: "last_name", type: "string", example: "Doe"),
        new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com"),
        new OA\Property(property: "reaction_type", type: "string", example: "like"),
        new OA\Property(property: "reacted_at", type: "string", format: "date-time", example: "2026-07-12T13:22:00Z")
    ]
)]
class ReactionUserSchema {}
