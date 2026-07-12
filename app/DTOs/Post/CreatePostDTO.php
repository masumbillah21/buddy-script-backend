<?php

namespace App\DTOs\Post;

use Illuminate\Http\Request;

class CreatePostDTO
{
    public function __construct(
        public readonly int $user_id,
        public readonly ?string $content,
        public readonly ?string $image_path,
        public readonly string $visibility
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            user_id: $request->user()->id,
            content: $request->input('content'),
            image_path: $request->input('image_path'),
            visibility: $request->input('visibility', 'public')
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'content' => $this->content,
            'image_path' => $this->image_path,
            'visibility' => $this->visibility,
        ];
    }
}
