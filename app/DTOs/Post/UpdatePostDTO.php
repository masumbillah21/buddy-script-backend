<?php

namespace App\DTOs\Post;

use Illuminate\Http\Request;

class UpdatePostDTO
{
    public function __construct(
        public readonly array $data
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = [];
        if ($request->has('content')) {
            $data['content'] = $request->input('content');
        }
        if ($request->has('image_path')) {
            $data['image_path'] = $request->input('image_path');
        }
        if ($request->has('visibility')) {
            $data['visibility'] = $request->input('visibility');
        }
        return new self($data);
    }
}
