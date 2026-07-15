<?php

namespace App\DTOs\Post;

use Illuminate\Http\Request;

class CreatePostDTO
{
    public function __construct(
        public readonly string $user_id,
        public readonly ?string $content,
        public readonly ?string $image_path,
        public readonly ?string $video_path,
        public readonly ?string $title,
        public readonly string $type,
        public readonly ?string $event_date,
        public readonly string $visibility
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            user_id: $request->user()->id,
            content: $request->input('content'),
            image_path: $request->input('image_path'),
            video_path: $request->input('video_path'),
            title: $request->input('title'),
            type: $request->input('type', 'text'),
            event_date: $request->input('event_date'),
            visibility: $request->input('visibility', 'public')
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'content' => $this->content,
            'image_path' => $this->image_path,
            'video_path' => $this->video_path,
            'title' => $this->title,
            'type' => $this->type,
            'event_date' => $this->event_date,
            'visibility' => $this->visibility,
        ];
    }
}
