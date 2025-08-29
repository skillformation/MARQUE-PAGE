<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimpleAuthController;
use App\Http\Controllers\BookController;

// Routes API pour l'authentification
Route::prefix('api/auth')->group(function () {
    Route::post('register', [SimpleAuthController::class, 'register']);
    Route::post('login', [SimpleAuthController::class, 'login']);
});

// Routes API pour les livres
Route::prefix('api/books')->group(function () {
    Route::get('/', [BookController::class, 'index']);
    Route::post('/', [BookController::class, 'store']);
    Route::get('/{id}', [BookController::class, 'show']);
    Route::put('/{id}', [BookController::class, 'update']);
    Route::delete('/{id}', [BookController::class, 'destroy']);
    Route::put('/{id}/progress', [BookController::class, 'updateProgress']);
});

// Route principale pour l'application
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
