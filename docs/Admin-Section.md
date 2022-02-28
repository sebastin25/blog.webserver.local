[<Go Back](/README.md)

# Admin Section

## Limit Access to Only Admins

Creamos un nuevo middleware usando `php artisan make:middleware MustBeAdministrator`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustBeAdministrator
{

    public function handle(Request $request, Closure $next)
    {

        if (optional(auth()->user())->username !== 'sebastin25') {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
```

Agregamos el middleware en `/app/Http/Kernel.php`

```php
protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'admin' => MustBeAdministrator::class,
        //code...
    ];
```

Creamos una vista nueva `/resources/views/posts/create.blade.php`

```php
<x-layout>
    <section class="px-6 py-8">
        Hello
    </section>
</x-layout>

```

Añadimos la función create() en `PostController`

```php
    public function create()
    {
        return view('posts.create');
    }
```

Agregamos la ruta

```php
Route::get('admin/posts/create', [PostController::class, 'create'])->middleware('admin');
```

## Create the Publish Post Form

Modificamos nuestra vista `/resources/views/posts/create.blade.php`

```php
<x-panel class="max-w-sm mx-auto">
    <form method="POST" action="/admin/posts">
        @csrf

        <div class="mb-6">
            <label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="title">
            Title
            </label>

            <input class="border border-gray-400 p-2 w-full" type="text" name="title" id="title" value="{{ old('title') }}" required>

            @error('title')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
             <label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="slug">
             Slug
            </label>

            <input class="border border-gray-400 p-2 w-full" type="text" name="slug" id="slug" value="{{ old('slug') }}" required>

            @error('slug')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="excerpt">
            Excerpt
            </label>

            <textarea class="border border-gray-400 p-2 w-full" name="excerpt" id="excerpt" required>
            {{ old('excerpt') }}
            </textarea>

            @error('excerpt')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="body">
            Body
            </label>

            <textarea class="border border-gray-400 p-2 w-full" name="body" id="body" required>{{ old('body') }}
            </textarea>

            @error('body')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="category_id">
                        Category
            </label>

            <select name="category_id" id="category_id">
                @foreach (\App\Models\Category::all() as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ ucwords($category->name) }}</option>
                @endforeach
            </select>

            @error('category')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>
        <x-submit-button>Publish</x-submit-button>
    </form>
</x-panel>
```

Agregamos una ruta nueva

```php
Route::post('admin/posts', [PostController::class, 'store'])->middleware('admin');
```

Agregamos una nueva función a `PostController`

```php
public function store()
{
    $attributes = request()->validate([
        'title' => 'required',
        'slug' => ['required', Rule::unique('posts', 'slug')],
        'excerpt' => 'required',
        'body' => 'required',
        'category_id' => ['required', Rule::exists('categories', 'id')]
        ]);

    $attributes['user_id'] = auth()->id();
    Post::create($attributes);

    return redirect('/');
}
```
