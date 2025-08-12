<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::post('/', [PostController::class, 'store']);
    Route::get('/user/{user_id}', [PostController::class, 'byUser']);
    Route::get('/{post}', [PostController::class, 'show']);
    Route::put('/{post}', [PostController::class, 'update']);
    Route::delete('/{post}', [PostController::class, 'destroy']);
    Route::post('/{post}/like', [PostController::class, 'like']);
    Route::get('/{post}/comments', [PostController::class, 'getComments']);
    Route::post('/{post}/comments', [PostController::class, 'storeComment']);
});
