<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;


Route::get('/', [PostController::class, 'index'])->name('home');

Route::get('/posts/{post:slug}', [PostController::class, 'show']);

Route::get('/categories/{category:slug}', [PostController::class, 'showCategory'])->name('category');

Route::get('/authors/{author:username}', [PostController::class, 'showAuthor']);
