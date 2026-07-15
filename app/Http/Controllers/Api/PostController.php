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
        $items = collect($posts->items());

        if ($currentUserId && $items->isNotEmpty()) {
            $myReactions = $this->reactionService->getPostReactionTypesForUser(
                $currentUserId,
                $items->pluck('id')->toArray()
            );

            foreach ($posts as $post) {
                $post->setAttribute('my_reaction', $myReactions[$post->id] ?? null);
            }
        }
        return PostResource::collection($posts)
            ->response();
    }

    public function userFeed(Request $request, string $userId): JsonResponse
    {
        $currentUserId = $request->user('sanctum')?->id;
        $posts = $this->postService->getUserFeed($userId, $currentUserId, (int) $request->input('per_page', 20));

        $items = collect($posts->items());

        if ($currentUserId && $items->isNotEmpty()) {
            $myReactions = $this->reactionService->getPostReactionTypesForUser(
                $currentUserId,
                $items->pluck('id')->toArray()
            );

            foreach ($posts as $post) {
                $post->setAttribute('my_reaction', $myReactions[$post->id] ?? null);
            }
        }
        return PostResource::collection($posts)
            ->response();
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'nullable|string|max:10000',
            'image' => 'nullable|file|image|max:10240',
            'video' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/ogg,video/webm|max:51200',
            'image_path' => 'nullable|string|max:255',
            'video_path' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'type' => 'sometimes|string|in:text,photo,video,event,article',
            'event_date' => 'nullable|date',
            'visibility' => 'sometimes|string|in:public,private',
        ]);

        $imagePath = $request->input('image_path');
        if ($request->hasFile('image')) {
            if (!file_exists(public_path('uploads'))) {
                mkdir(public_path('uploads'), 0777, true);
            }
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $imagePath = '/uploads/' . $filename;
        }

        $videoPath = $request->input('video_path');
        if ($request->hasFile('video')) {
            if (!file_exists(public_path('uploads'))) {
                mkdir(public_path('uploads'), 0777, true);
            }
            $file = $request->file('video');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $videoPath = '/uploads/' . $filename;
        }

        $request->merge([
            'image_path' => $imagePath,
            'video_path' => $videoPath,
        ]);

        $dto = CreatePostDTO::fromRequest($request);
        $post = $this->postService->createPost($dto);

        return (new PostResource($post->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): JsonResponse
    {
        $currentUserId = request()->user('sanctum')?->id;
        $post = $this->postService->getPost($id, $currentUserId);

        if ($currentUserId) {
            $myReactions = $this->reactionService->getPostReactionTypesForUser($currentUserId, [$id]);
            $post->setAttribute('my_reaction', $myReactions[$id] ?? null);
        }

        return (new PostResource($post->loadMissing('user')))
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
