[<Go Back](/README.md)

# Search

## Search (The Messy Way)

Agregaremos una barra de busqueda a `_post-header.blade.php`

```php
<div class="relative flex lg:inline-flex items-center bg-gray-100 rounded-xl px-3 py-2">
    <form method="GET" action="#">
        <input type="text" name="search" placeholder="Fisomething"
         class="bg-transparent placeholder-blafont-semibold text-sm" value="{{ request('search}}">
    </form>
</div>
```

y luego modificaremos nuesta ruta

```php
Route::get('/', function () {
    $posts = Post::latest()->with('category', 'author');
    if (request('search')) {
        $posts
            ->where('title', 'like', '%' . request('search') . '%')
            ->orWhere('body', 'like', '%' . request('search') . '%');
    }
    return view('posts', [
        'posts' => $posts->get(),
        'categories' => Category::all()
    ]);
})->name('home');
```
