<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Casts\Attribute;

#[Fillable(['user_id', 'content', 'image_path', 'video_path', 'title', 'type', 'event_date', 'visibility', 'reactions_count', 'comments_count'])]
class Post extends Model
{
    use HasFactory;

    protected $casts = [
        'reactions_count' => 'integer',
        'comments_count' => 'integer',
        'event_date' => 'datetime',
    ];

    /**
     * Get the post's image absolute URL.
     */
    protected function imagePath(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') ? $value : url($value)) : null
        );
    }

    /**
     * Get the post's video absolute URL.
     */
    protected function videoPath(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') ? $value : url($value)) : null
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class);
    }
}
