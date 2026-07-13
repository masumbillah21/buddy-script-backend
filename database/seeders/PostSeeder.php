<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $postsData = [
                [
                    'title' => 'Healthy Tracking App',
                    'content' => 'Building a tracking dashboard for daily workouts and nutrition plans.',
                    'image_path' => 'uploads/post1.png',
                    'type' => 'image',
                    'visibility' => 'public',
                ],
                [
                    'title' => 'Minimalist Workspace Design',
                    'content' => 'Loving the clean vibes of my upgraded remote desktop setup.',
                    'image_path' => 'uploads/post2.png',
                    'type' => 'image',
                    'visibility' => 'public',
                ],
                [
                    'title' => 'Innovative UI Components',
                    'content' => 'Refactoring social cards and reactions badges with custom layout styling.',
                    'image_path' => 'uploads/post3.png',
                    'type' => 'image',
                    'visibility' => 'public',
                ]
            ];

            foreach ($postsData as $postInfo) {
                // Check if post already exists to prevent duplicate seeding
                $exists = Post::where('user_id', $user->id)
                    ->where('image_path', $postInfo['image_path'])
                    ->exists();

                if (!$exists) {
                    Post::create([
                        'user_id' => $user->id,
                        'title' => $postInfo['title'],
                        'content' => $postInfo['content'],
                        'image_path' => $postInfo['image_path'],
                        'type' => $postInfo['type'],
                        'visibility' => $postInfo['visibility'],
                    ]);
                }
            }
        }
    }
}
