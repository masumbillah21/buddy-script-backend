<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Post\CreatePostDTO;
use App\DTOs\Post\UpdatePostDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use App\Services\ReactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService,
        protected ReactionService $reactionService
    ) {}

    #[OA\Get(
        path: "/api/posts",
        summary: "Retrieve Public Social Feed",
        description: "Fetch a paginated list of public social posts",
        security: [["sanctum" => []]],
        tags: ["Posts"],
        parameters: [
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", required: false, schema: new OA\Schema(type: "integer", default: 20))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Social feed retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Post")),
                        new OA\Property(property: "links", type: "object"),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $posts = $this->postService->getFeed((int) $request->input('per_page', 20));

        $currentUserId = $request->user('sanctum')?->id;
        if ($currentUserId && $posts->isNotEmpty()) {
            $myReactions = $this->reactionService->getPostReactionTypesForUser(
                $currentUserId,
                $posts->pluck('id')->toArray()
            );

            foreach ($posts as $post) {
                $post->setAttribute('my_reaction', $myReactions[$post->id] ?? null);
            }
        }

        $posts->load('user');

        return PostResource::collection($posts)
            ->response();
    }

    #[OA\Get(
        path: "/api/users/{userId}/posts",
        summary: "Retrieve User Profile Feed",
        description: "Fetch a paginated feed of posts created by a specific user. Authenticated users can view their own private posts; public profiles show public posts only.",
        security: [["sanctum" => []]],
        tags: ["Posts"],
        parameters: [
            new OA\Parameter(name: "userId", in: "path", description: "Target User ID", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", required: false, schema: new OA\Schema(type: "integer", default: 20))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "User feed retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Post"))
                    ]
                )
            ),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function userFeed(Request $request, int $userId): JsonResponse
    {
        $currentUserId = $request->user('sanctum')?->id;
        $posts = $this->postService->getUserFeed($userId, $currentUserId, (int) $request->input('per_page', 20));

        if ($currentUserId && $posts->isNotEmpty()) {
            $myReactions = $this->reactionService->getPostReactionTypesForUser(
                $currentUserId,
                $posts->pluck('id')->toArray()
            );

            foreach ($posts as $post) {
                $post->setAttribute('my_reaction', $myReactions[$post->id] ?? null);
            }
        }

        $posts->load('user');

        return PostResource::collection($posts)
            ->response();
    }

    #[OA\Post(
        path: "/api/posts",
        summary: "Create New Post",
        description: "Publish a new text and/or image post",
        security: [["sanctum" => []]],
        tags: ["Posts"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "content", type: "string", nullable: true, example: "Hello World!"),
                    new OA\Property(property: "image_path", type: "string", nullable: true, example: "uploads/posts/image.jpg"),
                    new OA\Property(property: "visibility", type: "string", enum: ["public", "private"], default: "public")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Post created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", ref: "#/components/schemas/Post")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required_without:image_path|nullable|string',
            'image_path' => 'required_without:content|nullable|string|max:255',
            'visibility' => 'sometimes|string|in:public,private',
        ]);

        $dto = CreatePostDTO::fromRequest($request);
        $post = $this->postService->createPost($dto);

        return (new PostResource($post->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: "/api/posts/{id}",
        summary: "Get Post Details",
        description: "Retrieve a specific post by ID. Enforces visibility validation.",
        security: [["sanctum" => []]],
        tags: ["Posts"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", description: "Post ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Post retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", ref: "#/components/schemas/Post")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Forbidden - private post access"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $currentUserId = request()->user('sanctum')?->id;
        $post = $this->postService->getPost($id, $currentUserId);

        if ($currentUserId) {
            $myReactions = $this->reactionService->getPostReactionTypesForUser($currentUserId, [$id]);
            $post->setAttribute('my_reaction', $myReactions[$id] ?? null);
        }

        return (new PostResource($post->load('user')))
            ->response();
    }

    #[OA\Put(
        path: "/api/posts/{id}",
        summary: "Update Post",
        description: "Modify content, image path, or visibility of an existing post",
        security: [["sanctum" => []]],
        tags: ["Posts"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", description: "Post ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "content", type: "string", nullable: true, example: "Updated content"),
                    new OA\Property(property: "image_path", type: "string", nullable: true, example: "uploads/posts/new_image.jpg"),
                    new OA\Property(property: "visibility", type: "string", enum: ["public", "private"])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Post updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", ref: "#/components/schemas/Post")
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Unauthorized to update this post"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function update(Request $request, Post $post): JsonResponse
    {
        $request->validate([
            'content' => 'sometimes|nullable|string',
            'image_path' => 'sometimes|nullable|string|max:255',
            'visibility' => 'sometimes|string|in:public,private',
        ]);

        $dto = UpdatePostDTO::fromRequest($request);
        $updatedPost = $this->postService->updatePost($post, $dto, $request->user()->id);

        return (new PostResource($updatedPost))
            ->response();
    }

    #[OA\Delete(
        path: "/api/posts/{id}",
        summary: "Delete Post",
        description: "Delete an existing post (and its comments/reactions)",
        security: [["sanctum" => []]],
        tags: ["Posts"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", description: "Post ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Post deleted successfully"),
            new OA\Response(response: 403, description: "Unauthorized to delete this post"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->postService->deletePost($post, $request->user()->id);

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }
}

// Swagger Schemas for Post
namespace App\Http\Controllers\Api;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Post",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "user", ref: "#/components/schemas/User"),
        new OA\Property(property: "content", type: "string", nullable: true, example: "Hello World!"),
        new OA\Property(property: "image_path", type: "string", nullable: true, example: "uploads/posts/image.jpg"),
        new OA\Property(property: "visibility", type: "string", example: "public"),
        new OA\Property(property: "reactions_count", type: "integer", example: 12),
        new OA\Property(property: "comments_count", type: "integer", example: 4),
        new OA\Property(property: "my_reaction", type: "string", nullable: true, example: "love"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-12T13:22:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-07-12T13:22:00Z")
    ]
)]
class PostSchema {}
