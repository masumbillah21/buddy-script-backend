<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $faker = Faker::create();

        $postTypes = ['text', 'photo', 'video', 'event', 'article'];
        $visibilities = ['public', 'private'];

        // Static featured posts for all users to ensure high quality initial data
        $staticPosts = [
            [
                'title' => 'Healthy Tracking App',
                'content' => 'Building a tracking dashboard for daily workouts and nutrition plans.',
                'image_path' => '/uploads/post1.png',
                'type' => 'photo',
                'visibility' => 'public',
            ],
            [
                'title' => 'Minimalist Workspace Design',
                'content' => 'Loving the clean vibes of my upgraded remote desktop setup.',
                'image_path' => '/uploads/post2.png',
                'type' => 'photo',
                'visibility' => 'public',
            ],
            [
                'title' => 'Innovative UI Components',
                'content' => 'Refactoring social cards and reactions badges with custom layout styling.',
                'image_path' => '/uploads/post3.png',
                'type' => 'photo',
                'visibility' => 'public',
            ]
        ];

        foreach ($users as $user) {
            // Seed static posts
            foreach ($staticPosts as $postInfo) {
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

            // Seed additional 8 random posts per user to enable pagination/infinite scroll testing
            for ($i = 1; $i <= 8; $i++) {
                $type = $faker->randomElement($postTypes);
                $visibility = $faker->randomElement($visibilities);
                
                $title = null;
                $eventDate = null;
                $imagePath = null;
                
                if ($type === 'event') {
                    $title = $faker->sentence(3) . ' Event';
                    $eventDate = $faker->dateTimeBetween('now', '+1 month');
                } elseif ($type === 'article') {
                    $title = 'Article: ' . $faker->sentence(4);
                } elseif ($type === 'photo') {
                    $title = 'Photo Shared';
                    $imagePath = '/uploads/post' . rand(1, 3) . '.png';
                }

                Post::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'content' => $faker->paragraph(rand(2, 4)),
                    'image_path' => $imagePath,
                    'type' => $type,
                    'visibility' => $visibility,
                    'event_date' => $eventDate,
                ]);
            }
        }
    }
}
