<?php

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostReaction;
use App\Models\CommentReaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register successfully', function () {
    $response = $this->postJson('/api/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'first_name', 'last_name', 'email', 'created_at']
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);
});

test('user can login successfully', function () {
    $user = User::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'jane@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => ['id', 'first_name', 'last_name', 'email'],
            'token'
        ]);
});

test('user can create a post and retrieve it from the public feed', function () {
    $user = User::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);

    $token = $user->createToken('test')->plainTextToken;

    // Create public post
    $responsePublic = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/posts', [
            'content' => 'This is a public post',
            'visibility' => 'public',
        ]);

    $responsePublic->assertStatus(201)
        ->assertJsonPath('data.content', 'This is a public post')
        ->assertJsonPath('data.visibility', 'public');

    // Create private post
    $responsePrivate = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/posts', [
            'content' => 'This is a private post',
            'visibility' => 'private',
        ]);

    $responsePrivate->assertStatus(201);

    // Guest cannot retrieve feed
    $this->getJson('/api/posts')->assertStatus(401);

    // Retrieve public feed (authenticated access)
    $feedResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/posts');
    $feedResponse->assertStatus(200)
        ->assertJsonCount(1, 'data') // only public post is visible
        ->assertJsonPath('data.0.content', 'This is a public post');

    // User profile feed (authorized owner) shows both public and private
    $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson("/api/users/{$user->id}/posts");
    $profileResponse->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('guest cannot access post details', function () {
    $user = User::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);

    $post = Post::create([
        'user_id' => $user->id,
        'content' => 'Private thoughts',
        'visibility' => 'private',
    ]);

    $this->getJson("/api/posts/{$post->id}")
        ->assertStatus(401);
});

test('different user cannot see private post', function () {
    $user1 = User::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);
    $user2 = User::create([
        'first_name' => 'Bob',
        'last_name' => 'Smith',
        'email' => 'bob@example.com',
        'password' => Hash::make('password123'),
    ]);

    $post = Post::create([
        'user_id' => $user1->id,
        'content' => 'Private thoughts',
        'visibility' => 'private',
    ]);

    $token2 = $user2->createToken('test')->plainTextToken;
    $this->withHeader('Authorization', 'Bearer ' . $token2)
        ->getJson("/api/posts/{$post->id}")
        ->assertStatus(403);
});

test('author can see private post', function () {
    $user1 = User::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);

    $post = Post::create([
        'user_id' => $user1->id,
        'content' => 'Private thoughts',
        'visibility' => 'private',
    ]);

    $token1 = $user1->createToken('test')->plainTextToken;
    $this->withHeader('Authorization', 'Bearer ' . $token1)
        ->getJson("/api/posts/{$post->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.content', 'Private thoughts');
});

test('comments increment and decrement counts correctly', function () {
    $user = User::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $post = Post::create([
        'user_id' => $user->id,
        'content' => 'A post about comments',
        'visibility' => 'public',
    ]);

    // 1. Add top-level comment
    $comment1Response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'First comment',
        ]);

    $comment1Response->assertStatus(201);
    $comment1Id = $comment1Response->json('data.id');

    // Post comments_count should be 1
    $this->assertEquals(1, $post->fresh()->comments_count);

    // 2. Add nested reply comment
    $comment2Response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'Reply to first comment',
            'parent_id' => $comment1Id,
        ]);

    $comment2Response->assertStatus(201);

    // Post comments_count should be 2, parent replies_count should be 1
    $this->assertEquals(2, $post->fresh()->comments_count);
    $this->assertEquals(1, Comment::find($comment1Id)->replies_count);

    // 3. Delete top-level comment (which cascade deletes the reply)
    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->deleteJson("/api/comments/{$comment1Id}")
        ->assertStatus(200);

    // Post comments_count should return back to 0 (since parent + child were deleted)
    $this->assertEquals(0, $post->fresh()->comments_count);
    $this->assertDatabaseMissing('comments', ['id' => $comment1Id]);
});

test('post reactions manage atomic counters and toggle states correctly', function () {
    $user = User::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'password' => Hash::make('password123'),
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $post = Post::create([
        'user_id' => $user->id,
        'content' => 'A post to react to',
        'visibility' => 'public',
    ]);

    // 1. Add reaction 'like'
    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson("/api/posts/{$post->id}/react", [
            'reaction_type' => 'like',
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.reaction_type', 'like');

    $this->assertEquals(1, $post->fresh()->reactions_count);

    // 2. Change reaction to 'love' (counter should remain 1)
    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson("/api/posts/{$post->id}/react", [
            'reaction_type' => 'love',
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.reaction_type', 'love');

    $this->assertEquals(1, $post->fresh()->reactions_count);

    // 3. Toggle off reaction by sending 'love' again
    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson("/api/posts/{$post->id}/react", [
            'reaction_type' => 'love',
        ])
        ->assertStatus(200)
        ->assertJsonPath('data', null);

    $this->assertEquals(0, $post->fresh()->reactions_count);
});
