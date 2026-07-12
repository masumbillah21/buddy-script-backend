<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Comment\CreateCommentDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    #[OA\Get(
        path: "/api/posts/{postId}/comments",
        summary: "Retrieve Post Comments",
        description: "Fetch a paginated list of top-level comments and their immediate replies for a specific post",
        security: [["sanctum" => []]],
        tags: ["Comments"],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", description: "Target Post ID", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", required: false, schema: new OA\Schema(type: "integer", default: 20))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Comments retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Comment"))
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function index(Request $request, int $postId): JsonResponse
    {
        $comments = $this->commentService->getComments($postId, (int) $request->input('per_page', 20));

        return CommentResource::collection($comments)
            ->response();
    }

    #[OA\Post(
        path: "/api/posts/{postId}/comments",
        summary: "Add Comment or Reply",
        description: "Submit a new top-level comment or a replies comment by supplying a parent_id",
        security: [["sanctum" => []]],
        tags: ["Comments"],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", description: "Target Post ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["content"],
                properties: [
                    new OA\Property(property: "content", type: "string", example: "This is a great comment!"),
                    new OA\Property(property: "parent_id", type: "integer", nullable: true, example: 5)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Comment or reply added successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", ref: "#/components/schemas/Comment")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Bad Request - invalid parent comment reference"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
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

    #[OA\Delete(
        path: "/api/comments/{id}",
        summary: "Delete Comment",
        description: "Delete an existing comment or reply (cascade deletes nested replies)",
        security: [["sanctum" => []]],
        tags: ["Comments"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", description: "Comment ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Comment deleted successfully"),
            new OA\Response(response: 403, description: "Unauthorized to delete this comment"),
            new OA\Response(response: 404, description: "Comment not found")
        ]
    )]
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $this->commentService->deleteComment($comment, $request->user()->id);

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }
}

// Swagger Schemas for Comment
namespace App\Http\Controllers\Api;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Comment",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "post_id", type: "integer", example: 1),
        new OA\Property(property: "user", ref: "#/components/schemas/User"),
        new OA\Property(property: "parent_id", type: "integer", nullable: true, example: null),
        new OA\Property(property: "content", type: "string", example: "This is a comment"),
        new OA\Property(property: "reactions_count", type: "integer", example: 2),
        new OA\Property(property: "replies_count", type: "integer", example: 1),
        new OA\Property(property: "replies", type: "array", items: new OA\Items(ref: "#/components/schemas/Comment"), nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-12T13:22:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-07-12T13:22:00Z")
    ]
)]
class CommentSchema {}
