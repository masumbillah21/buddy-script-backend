<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'reactions_count' => (int) $this->reactions_count,
            'replies_count' => (int) $this->replies_count,
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'my_reaction' => $this->my_reaction ?? null,
            'reaction_types' => $this->reaction_types ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
