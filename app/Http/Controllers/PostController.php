<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {

        return view('posts.index', [
            'posts' => Post::latest()
                ->with('category', 'author')
                ->filter(request(['search', 'category']))
                ->get()
        ]);
    }

    public function show(Post $post)
    {
        return view('posts.show', [
            'post' => $post
        ]);
    }

    public function showCategory(Category $category)
    {
        return view('posts.show', [
            'posts' => $category->posts->load(['category', 'author']),
            'currentCategory' => $category,
            'categories' => Category::all()
        ]);
    }

    public function showAuthor(User $author)
    {
        return view('posts.show', [
            'posts' => $author->posts->load(['category', 'author'])
        ]);
    }
}
