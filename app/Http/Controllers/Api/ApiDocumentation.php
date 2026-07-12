<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

/**
 * Base API Specifications
 */
#[OA\Info(
    title: "Buddy Script API",
    version: "1.0.0",
    description: "API Documentation for the Buddy Script Backend API"
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "API Server Host"
)]
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class ApiDocumentation
{
    // ==========================================
    // AUTHENTICATION ENDPOINTS
    // ==========================================

    #[OA\Post(
        path: "/api/register",
        summary: "User Registration",
        description: "Register a new user account",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["first_name", "last_name", "email", "password"],
                properties: [
                    new OA\Property(property: "first_name", type: "string", example: "John"),
                    new OA\Property(property: "last_name", type: "string", example: "Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Secret123!")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User registered successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function register() {}

    #[OA\Post(
        path: "/api/login",
        summary: "User Login",
        description: "Log in with email and password to receive a bearer token",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Secret123!")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", ref: "#/components/schemas/User"),
                        new OA\Property(property: "token", type: "string", example: "1|abcdef123456...")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Invalid credentials")
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: "/api/logout",
        summary: "User Logout",
        description: "Revoke user auth tokens",
        security: [["sanctum" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(response: 200, description: "Logged out successfully"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function logout() {}

    #[OA\Get(
        path: "/api/me",
        summary: "Get Authenticated Profile",
        description: "Retrieve profile of the currently logged-in user",
        security: [["sanctum" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile retrieved",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function me() {}


    // ==========================================
    // POSTS ENDPOINTS
    // ==========================================

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
            ),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function getFeed() {}

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
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function getUserFeed() {}

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
    public function storePost() {}

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
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function showPost() {}

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
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function updatePost() {}

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
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function destroyPost() {}


    // ==========================================
    // COMMENTS ENDPOINTS
    // ==========================================

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
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function getComments() {}

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
    public function storeComment() {}

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
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Comment not found")
        ]
    )]
    public function destroyComment() {}


    // ==========================================
    // REACTIONS ENDPOINTS
    // ==========================================

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
    public function reactToPost() {}

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
    public function reactToComment() {}

    #[OA\Get(
        path: "/api/posts/{postId}/reactions",
        summary: "Get Post Reaction List",
        description: "Fetch a paginated list of users who reacted to a post. Optionally filter by reaction_type.",
        security: [["sanctum" => []]],
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
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Post not found")
        ]
    )]
    public function postReactions() {}

    #[OA\Get(
        path: "/api/comments/{commentId}/reactions",
        summary: "Get Comment Reaction List",
        description: "Fetch a paginated list of users who reacted to a specific comment/reply.",
        security: [["sanctum" => []]],
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
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Comment not found")
        ]
    )]
    public function commentReactions() {}
}

// ==========================================
// SWAGGER SCHEMAS DEFINITIONS
// ==========================================

#[OA\Schema(
    schema: "User",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "first_name", type: "string", example: "John"),
        new OA\Property(property: "last_name", type: "string", example: "Doe"),
        new OA\Property(property: "email", type: "string", format: "email", example: "john.doe@example.com"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-12T13:22:00Z")
    ]
)]
class UserSchema {}

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
