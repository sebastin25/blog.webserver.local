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

## Search (The Cleaner Way)

Crearemos un controller usando `php artisan make:controller PostController` lo cual lo creara en `/app/Http/Controllers/`

```php
class PostController extends Controller
{
    public function index()
    {

        return view('posts', [
            'posts' => Post::latest()
                ->with('category', 'author')
                ->filter(request(['search']))
                ->get(),
            'categories' => Category::all()
        ]);
    }

    public function show(Post $post)
    {
        return view('post', [
            'post' => $post
        ]);
    }

    public function showCategory(Category $category)
    {
        return view('posts', [
            'posts' => $category->posts->load(['category', 'author']),
            'currentCategory' => $category,
            'categories' => Category::all()
        ]);
    }

    public function showAuthor(User $author)
    {
        return view('posts', [
            'posts' => $author->posts->load(['category', 'author']),
            'categories' => Category::all()
        ]);
    }
}
```

Nuestros rutas quedarian asi

```php
Route::get('/', [PostController::class, 'index'])->name('home');

Route::get('/posts/{post:slug}', [PostController::class, 'show']);

Route::get('/categories/{category:slug}', [PostController::class, 'showCategory'])->name('category');

Route::get('/authors/{author:username}', [PostController::class, 'showAuthor']);
```

y para terminar, agregamos una funcion nueva en `/app/Models/Post.php`

```php
public function scopeFilter($query, array $filters)
{
    $query->when($filters['search'] ?? false, fn ($query, $search) =>
    $query
        ->where('title', 'like', '%' . $search . '%')
        ->orWhere('body', 'like', '%' . $search . '%'));
}
```
