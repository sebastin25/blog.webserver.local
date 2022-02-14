<?php

use App\Models\Post;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('posts', [
        'posts' => Post::all()
    ]);
});

Route::get('/posts/{post:slug}', function (Post $post) { //Post::where('slug', $post)-> firstOrFail();
    return view('post', [
        'post' => $post
    ]);
});
