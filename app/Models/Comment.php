<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['post_id', 'user_id', 'parent_id', 'content', 'reactions_count', 'replies_count'])]
class Comment extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'reactions_count' => 'integer',
        'replies_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function reactions()
    {
        return $this->hasMany(CommentReaction::class);
    }
}
