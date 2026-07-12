<?php

namespace App\DTOs\Reaction;

use Illuminate\Http\Request;

class ReactToCommentDTO
{
    public function __construct(
        public readonly int $comment_id,
        public readonly int $user_id,
        public readonly string $reaction_type
    ) {}

    public static function fromRequest(Request $request, int $commentId): self
    {
        return new self(
            comment_id: $commentId,
            user_id: $request->user()->id,
            reaction_type: $request->input('reaction_type')
        );
    }

    public function toArray(): array
    {
        return [
            'comment_id' => $this->comment_id,
            'user_id' => $this->user_id,
            'reaction_type' => $this->reaction_type,
        ];
    }
}
