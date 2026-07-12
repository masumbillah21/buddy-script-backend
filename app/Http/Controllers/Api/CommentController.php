<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Comment\CreateCommentDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function index(Request $request, int $postId): JsonResponse
    {
        $comments = $this->commentService->getComments($postId, (int) $request->input('per_page', 20));

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
