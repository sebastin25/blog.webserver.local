<?php

use App\Models\Post;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('posts', [
        'posts' => Post::all()
    ]);
});



Route::get('/posts/{post}', function ($slug) {

    //Find a post by its slug and pass it to a view called "post"

    return view('post', [
        'post' => Post::findorFail($slug)
    ]);

    /*
return view('post', [
    'post' => file_get_contents(__DIR__, '/../resources/posts/my-first-post.html') // $post
]);
*/
    /*
$post = file_get_contents(__DIR__, '/../resources/posts/my-first-post.html');
]);
*/
    /*
    if (!file_exists($path = __DIR__ . "/../resources/posts/{$slug}.html")) {
        //dd('file does not exist');
        //ddd('file does not exist');
        //abort(404);
        return redirect('/');
    }
    /*
    $post = cache()->remember("posts.{$slug}", 1200, function () use ($path) {
        return file_get_contents($path);
    });
*/
    /*     $post = cache()->remember("posts.{$slug}", 1200, fn () => file_get_contents($path));

    return view('post', [
        'post' => $post
    ]); */
});

Route::get('/json', function () {
    return ['foo' => 'bar'];
});
