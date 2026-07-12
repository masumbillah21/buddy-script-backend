<?php

namespace App\DTOs\Comment;

use Illuminate\Http\Request;

class CreateCommentDTO
{
    public function __construct(
        public readonly int $post_id,
        public readonly int $user_id,
        public readonly ?int $parent_id,
        public readonly string $content
    ) {}

    public static function fromRequest(Request $request, int $postId): self
    {
        return new self(
            post_id: $postId,
            user_id: $request->user()->id,
            parent_id: $request->input('parent_id') ? (int) $request->input('parent_id') : null,
            content: $request->input('content')
        );
    }

    public function toArray(): array
    {
        return [
            'post_id' => $this->post_id,
            'user_id' => $this->user_id,
            'parent_id' => $this->parent_id,
            'content' => $this->content,
        ];
    }
}
