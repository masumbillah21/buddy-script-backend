<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Buddy Script Backend API Service',
        'documentation_url' => url('/api/documentation'),
    ]);
});

Route::get('/api/docs', function () {
    return redirect('/api/documentation');
});

Route::get('/docs', function () {
    return redirect('/api/documentation');
});
