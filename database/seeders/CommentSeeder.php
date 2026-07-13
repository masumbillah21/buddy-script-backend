<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $posts = Post::all();

        if ($users->isEmpty() || $posts->isEmpty()) {
            return;
        }

        foreach ($posts as $post) {
            // Check if comments already exist for this post to avoid double seeding
            if (Comment::where('post_id', $post->id)->exists()) {
                continue;
            }

            // Comment 1 by Dylan Field
            $user1 = $users->where('email', 'dylan@figma.com')->first() ?? $users->first();
            $comment1 = Comment::create([
                'post_id' => $post->id,
                'user_id' => $user1->id,
                'content' => "Wow, this looks absolutely amazing! Thanks for sharing.",
                'parent_id' => null,
            ]);
            $post->increment('comments_count');

            // Comment 2 by Steve Jobs
            $user2 = $users->where('email', 'steve@apple.com')->first() ?? $users->first();
            $comment2 = Comment::create([
                'post_id' => $post->id,
                'user_id' => $user2->id,
                'content' => "Great job! The design is extremely premium and polished.",
                'parent_id' => null,
            ]);
            $post->increment('comments_count');

            // Nested Reply to Comment 2 by Masum Billah
            $user3 = $users->where('email', 'mbillah21@gmail.com')->first() ?? $users->first();
            Comment::create([
                'post_id' => $post->id,
                'user_id' => $user3->id,
                'content' => "Thank you Steve! Appreciate the feedback.",
                'parent_id' => $comment2->id,
            ]);
            $comment2->increment('replies_count');
            $post->increment('comments_count');
        }
    }
}
