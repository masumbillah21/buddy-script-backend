<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReactionController;
use Illuminate\Support\Facades\Route;

// Public Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Post routes
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    Route::get('/users/{userId}/posts', [PostController::class, 'userFeed']);

    // Comment routes
    Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Reaction routes
    Route::post('/posts/{postId}/react', [ReactionController::class, 'reactToPost']);
    Route::post('/comments/{commentId}/react', [ReactionController::class, 'reactToComment']);
    Route::get('/posts/{postId}/reactions', [ReactionController::class, 'postReactions']);
    Route::get('/comments/{commentId}/reactions', [ReactionController::class, 'commentReactions']);
});
