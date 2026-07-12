<?php

namespace App\DTOs\Reaction;

use Illuminate\Http\Request;

class ReactToPostDTO
{
    public function __construct(
        public readonly int $post_id,
        public readonly int $user_id,
        public readonly string $reaction_type
    ) {}

    public static function fromRequest(Request $request, int $postId): self
    {
        return new self(
            post_id: $postId,
            user_id: $request->user()->id,
            reaction_type: $request->input('reaction_type')
        );
    }

    public function toArray(): array
    {
        return [
            'post_id' => $this->post_id,
            'user_id' => $this->user_id,
            'reaction_type' => $this->reaction_type,
        ];
    }
}
