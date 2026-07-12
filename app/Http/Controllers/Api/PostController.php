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

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService,
        protected ReactionService $reactionService
    ) {}

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

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->postService->deletePost($post, $request->user()->id);

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }
}
