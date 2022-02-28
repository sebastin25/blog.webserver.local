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

## Validate and Store Post Thumbnails

modificamos `/database/migrations/2022_02_14_202409_create_posts_table.php` para agregar la columna thumbnail

```php
public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id');
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('thumbnail')->nullable();
            $table->text('excerpt');
            $table->text('body');
            $table->timestamps();
            $table->timestamp('published_at')->nullable();
        });
    }
```

Agregamos una función store en `PostController` para guardar los post en la DB

```php
public function store()
    {

        $attributes = request()->validate([
            'title' => 'required',
            'thumbnail' => 'required|image',
            'slug' => ['required', Rule::unique('posts', 'slug')],
            'excerpt' => 'required',
            'body' => 'required',
            'category_id' => ['required', Rule::exists('categories', 'id')]
        ]);

        $attributes['user_id'] = auth()->id();
        $attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails');

        Post::create($attributes);

        return redirect('/');
    }
```

Modificamos `/config/filesystems.php` para que utilice `'default' => env('FILESYSTEM_DRIVER', 'public')`

Creamos el link entre `/storage/app/public/` y `/public/storage/` creando un symlink con el comando `php artisan storage:link`. Al estar usando vagrant, se debe correr la terminal como administrador antes de conectarse por ssh o usar el comando desde la maquina host.

Luego corremos para que creen nuevamente las tablas de la db `php artisan migrate:fresh --seed`

Modificamos `/resources/views/posts/create.blade.php` con un titulo y file select que ocupamos para el thumbnail

```php
<x-layout>
    <section class="py-8 max-w-md mx-auto">
        <h1 class="text-lg font-bold mb-4">
            Publish New Post
        </h1>

        <x-panel>
            <form method="POST" action="/admin/posts" enctype="multipart/form-data">

                // Code...

                <div class="mb-6">
                    <label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="thumbnail">
                        Thumbnail
                    </label>

                    <input class="border border-gray-400 p-2 w-full" type="file" name="thumbnail" id="thumbnail"
                        required>

                    @error('thumbnail')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                // Code...

            </form>
        </x-panel>
    </section>
</x-layout>
```

Seguidamente modificamos la vista `/resources/views/posts/show.blade.php` para que use la imagen que le agregamos al post

```php
<img src="{{ asset('storage/' . $post->thumbnail) }}" alt="" class="rounded-xl">
```

y los componentes `/resources/views/post-featured.blade.php` y `/resources/views/post-card.blade.php`

```php
<img src="{{ asset('storage/' . $post->thumbnail) }}" alt="Blog Post illustration" class="rounded-xl">
```

## Extract Form-Specific Blade Components

Creamos los siguientes componentes

`/resources/views/components/form/button.blade.php`

```php
<x-form.field>
    <button type="submit"
        class="bg-blue-500 text-white uppercase font-semibold text-xs py-2 px-10 rounded-2xl hover:bg-blue-600">
        {{ $slot }}
    </button>
</x-form.field>
```

`/resources/views/components/form/error.blade.php`

```php
@props(['name'])

@error($name)
    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
@enderror
```

`/resources/views/components/form/field.blade.php`

```php
<div class="mt-6">
    {{ $slot }}
</div>
```

`/resources/views/components/form/input.blade.php`

```php
@props(['name', 'type' => 'text'])

<x-form.field>
    <x-form.label name="{{ $name }}" />

    <input class="border border-gray-400 p-2 w-full" type="{{ $type }}" name="{{ $name }}"
        id="{{ $name }}" value="{{ old($name) }}" required>

    <x-form.error name="{{ $name }}" />
</x-form.field>
```

`/resources/views/components/form/label.blade.php`

```php
@props(['name'])

<label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="{{ $name }}">
    {{ ucwords($name) }}
</label>
```

`/resources/views/components/form/textarea.blade.php`

```php
@props(['name'])
<x-form.field>
    <x-form.label name="{{ $name }}" />

    <textarea class="border border-gray-400 p-2 w-full" name="{{ $name }}" id="{{ $name }}"
        required>{{ old($name) }}</textarea>

    <x-form.error name="{{ $name }}" />
</x-form.field>
```

Modificamos las siguientes vistas para que usen los nuevos componentes

`/resources/views/posts/_add-comment-form.blade.php`

```php
<x-form.button>Submit</x-form.button>
```

`/resources/views/posts/create.blade.php`

```php
<x-layout>
    <section class="py-8 max-w-md mx-auto">
        <h1 class="text-lg font-bold mb-4">
            Publish New Post
        </h1>

        <x-panel>
            <form method="POST" action="/admin/posts" enctype="multipart/form-data">
                @csrf

                <x-form.input name="title" />
                <x-form.input name="slug" />
                <x-form.input name="thumbnail" type="file" />
                <x-form.textarea name="excerpt" />
                <x-form.textarea name="body" />
                <x-form.field>
                    <x-form.label name="category" />
                    <select name="category_id" id="category_id">
                        @foreach (\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ ucwords($category->name) }}</option>
                        @endforeach
                    </select>
                    <x-form.error name="category" />
                </x-form.field>
                <x-form.button>Publish</x-form.button>
            </form>
        </x-panel>
    </section>
</x-layout>
```

## Extend the Admin Layout

Creamos un componente `setting.blade.php`

```php
@props(['heading'])

<section class="py-8 max-w-4xl mx-auto">
    <h1 class="text-lg font-bold mb-8 pb-2 border-b">
        {{ $heading }}
    </h1>
    <div class="flex">
        <aside class="w-48">
            <h4 class="font-semibold mb-4">Links</h4>
            <ul>
                <li>
                    <a href="/admin/dashboard"
                        class="{{ request()->is('admin/dashboard') ? 'text-blue-500' : '' }}">Dashboard</a>
                </li>
                <li>
                    <a href="/admin/posts/create"
                        class="{{ request()->is('admin/posts/create') ? 'text-blue-500' : '' }}">New Post</a>
                </li>
            </ul>
        </aside>
        <main class="flex-1">
            <x-panel>
                {{ $slot }}
            </x-panel>
        </main>
    </div>
</section>
```

Modificamos `dropdown.blade.php` para que utilice la clase relative

```php
<div x-data="{show: false}" @click.away="show = false" class="relative">
```

Modificamos `/form/input.blade.php` para que requiera `$attributes`

```php
 <input class="border border-gray-200 p-2 w-full rounded" name="{{ $name }}" id="{{ $name }}" value="{{ old($name) }}" required {{ $attributes }}>
```

Modificamos `/form/textarea.blade.php` para que requiera `$attributes`

```php
<textarea class="border border-gray-200 p-2 w-full rounded" name="{{ $name }}" id="{{ $name }}" required {{ $attributes }}>{{ old($name) }}</textarea>
```

Modificamos `layout.blade.php` para darle formato a la pagina y utilizar los componentes nuevos

```php
@auth
    <x-dropdown>
        <x-slot name="trigger">
            <button class="text-xs font-bold uppercase">Welcome, {{ auth()->user()->name }}!</button>
        </x-slot>

        <x-dropdown-item href="/admin/dashboard">Dashboard</x-dropdown-item>
        <x-dropdown-item href="/admin/posts/create" :active="request()->is('admin/posts/create')">New Post
        </x-dropdown-item>
        <x-dropdown-item href="#" x-data="{}"@click.prevent="document.querySelector('#logout-form').submit()">Log Out</x-dropdown-item>

        <form id="logout-form" method="POST" action="/logout" class="hidden">
            @csrf
        </form>
    </x-dropdown>
@else
    <a href="/register" class="text-xs font-bold uppercase {{ request()->is('register') ? 'text-blue-500' : '' }}">Register</a>
    <a href="/login" class="ml-6 text-xs font-bold uppercase {{ request()->is('login') ? 'text-blue-500' : '' }}">Log In</a>
@endauth
```

Modificamos `/posts/create.blade.php` para que use los componentes en lugar de inputs

```php
<x-layout>
    <x-setting heading="Publish New Post">
        <form method="POST" action="/admin/posts" enctype="multipart/form-data">
            @csrf

            <x-form.input name="title" />
            <x-form.input name="slug" />
            <x-form.input name="thumbnail" type="file" />
            <x-form.textarea name="excerpt" />
            <x-form.textarea name="body" />
            <x-form.field>
                <x-form.label name="category" />
                <select name="category_id" id="category_id">
                    @foreach (\App\Models\Category::all() as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ ucwords($category->name) }}</option>
                    @endforeach
                </select>
                <x-form.error name="category" />
            </x-form.field>
            <x-form.button>Publish</x-form.button>
        </form>
    </x-setting>
</x-layout>
```

Modificamos `/register/create.blade.php` para que use los componentes en lugar de inputs

```php
<x-layout>
    <section class="px-6 py-8">
        <main class="max-w-lg mx-auto mt-10">
            <x-panel>
                <h1 class="text-center font-bold text-xl">Register!</h1>

                <form method="POST" action="/register" class="mt-10">
                    @csrf

                    <x-form.input name="name" />
                    <x-form.input name="username" />
                    <x-form.input name="email" type="email" />
                    <x-form.input name="password" type="password" autocomplete="new-password" />
                    <x-form.button>Sign Up</x-form.button>
                </form>
            </x-panel>
        </main>
    </section>
</x-layout>

```

Modificamos `/sessions/create.blade.php` para que use los componentes en lugar de inputs

```php
<x-layout>
    <section class="px-6 py-8">
        <main class="max-w-lg mx-auto mt-10">
            <x-panel>
                <h1 class="text-center font-bold text-xl">Log In!</h1>

                <form method="POST" action="/login" class="mt-10">
                    @csrf

                    <x-form.input name="email" type="email" autocomplete="username" />
                    <x-form.input name="password" type="password" autocomplete="current-password" />
                    <x-form.button>Log In</x-form.button>
                </form>
            </x-panel>
        </main>
    </section>
</x-layout>
```

## Create a Form to Edit and Delete Posts

Creamos la vista `/resources/views/admin/posts/index.blade.php`

```php
<x-layout>
    <x-setting heading="Manage Posts">
        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($posts as $post)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <a href="/posts/{{ $post->slug }}">
                                                        {{ $post->title }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="/admin/posts/{{ $post->id }}/edit"
                                                class="text-blue-500 hover:text-blue-600">Edit</a>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form method="POST" action="/admin/posts/{{ $post->id }}">
                                                @csrf
                                                @method('DELETE')

                                                <button class="text-xs text-gray-400">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </x-setting>
</x-layout>

```

Creamos la vista `/resources/views/admin/posts/edit.blade.php`

```php
<x-layout>
    <x-setting :heading="'Edit Post: ' . $post->title">
        <form method="POST" action="/admin/posts/{{ $post->id }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <x-form.input name="title" :value="old('title', $post->title)" required />
            <x-form.input name="slug" :value="old('slug', $post->slug)" required />

            <div class="flex mt-6">
                <div class="flex-1">
                    <x-form.input name="thumbnail" type="file" :value="old('thumbnail', $post->thumbnail)" />
                </div>

                <img src="{{ asset('storage/' . $post->thumbnail) }}" alt="" class="rounded-xl ml-6" width="100">
            </div>

            <x-form.textarea name="excerpt" required>{{ old('excerpt', $post->excerpt) }}</x-form.textarea>
            <x-form.textarea name="body" required>{{ old('body', $post->body) }}</x-form.textarea>

            <x-form.field>
                <x-form.label name="category" />

                <select name="category_id" id="category_id" required>
                    @foreach (\App\Models\Category::all() as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                            {{ ucwords($category->name) }}</option>
                    @endforeach
                </select>

                <x-form.error name="category" />
            </x-form.field>

            <x-form.button>Update</x-form.button>
        </form>
    </x-setting>
</x-layout>
```

Movemos la vista `/resources/views/posts/create.blade.php` a `/resources/views/admin/posts/create.blade.php` y agregamos los required en sus tags

```php
<x-layout>
    <x-setting heading="Publish New Post">
        <form method="POST" action="/admin/posts" enctype="multipart/form-data">
            @csrf

            <x-form.input name="title" required />
            <x-form.input name="slug" required />
            <x-form.input name="thumbnail" type="file" required />
            <x-form.textarea name="excerpt" required />
            <x-form.textarea name="body" required />
            <x-form.field>
                <x-form.label name="category" />
                <select name="category_id" id="category_id" required>
                    @foreach (\App\Models\Category::all() as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ ucwords($category->name) }}</option>
                    @endforeach
                </select>
                <x-form.error name="category" />
            </x-form.field>
            <x-form.button>Publish</x-form.button>
        </form>
    </x-setting>
</x-layout>
```

Creamos un controlador nuevo `AdminPostController` y movemos las funciones create() y store() que se encuentran en `PostController`

```php
public function index()
    {
        return view('admin.posts.index', [
            'posts' => Post::paginate(50)
        ]);
    }

    public function create()
    {
        return view('admin.posts.create');
    }

    public function store()
    {
        $attributes = request()->validate([
            'title' => 'required',
            'thumbnail' => 'required|image',
            'slug' => ['required', Rule::unique('posts', 'slug')],
            'excerpt' => 'required',
            'body' => 'required',
            'category_id' => ['required', Rule::exists('categories', 'id')]
        ]);

        $attributes['user_id'] = auth()->id();
        $attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails');

        Post::create($attributes);

        return redirect('/');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', ['post' => $post]);
    }

    public function update(Post $post)
    {
        $attributes = request()->validate([
            'title' => 'required',
            'thumbnail' => 'image',
            'slug' => ['required', Rule::unique('posts', 'slug')->ignore($post->id)],
            'excerpt' => 'required',
            'body' => 'required',
            'category_id' => ['required', Rule::exists('categories', 'id')]
        ]);

        if (isset($attributes['thumbnail'])) {
            $attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails');
        }

        $post->update($attributes);

        return back()->with('success', 'Post Updated!');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('success', 'Post Deleted!');
    }
```

Agregamos las rutas nuevas

```php
Route::post('admin/posts', [AdminPostController::class, 'store'])->middleware('admin');
Route::get('admin/posts/create', [AdminPostController::class, 'create'])->middleware('admin');
Route::get('admin/posts', [AdminPostController::class, 'index'])->middleware('admin');
Route::get('admin/posts/{post}/edit', [AdminPostController::class, 'edit'])->middleware('admin');
Route::patch('admin/posts/{post}', [AdminPostController::class, 'update'])->middleware('admin');
Route::delete('admin/posts/{post}', [AdminPostController::class, 'destroy'])->middleware('admin');
```

Modificamos el href que se usa en el componente `category-button.blade.php`

```php
@props(['category'])

<a href="/?category={{ $category->slug }}"
    class="px-3 py-1 border border-blue-300 rounded-full text-blue-300 text-xs uppercase font-semibold"
    style="font-size: 10px">{{ $category->name }}</a>
```

Modificamos el href que se usa en `layout.blade.php`

```php
<x-dropdown-item href="/admin/posts" :active="request()->is('admin/posts')">Dashboard</x-dropdown-item>
```

Modificamos el href para ver todos los post como admin y las clases que usa el `<aside>` en el componente `setting.blade.php`

```php
<aside class="w-48 flex-shrink-0">
            <h4 class="font-semibold mb-4">Links</h4>
            <ul>
                <li>
                    <a href="/admin/posts" class="{{ request()->is('admin/posts') ? 'text-blue-500' : '' }}">All
                        Posts</a>
                </li>
                <li>
                    <a href="/admin/posts/create"
                        class="{{ request()->is('admin/posts/create') ? 'text-blue-500' : '' }}">New Post</a>
                </li>
            </ul>
        </aside>
```

Modificamos la vista `/register/create.blade.php` para que sus input sean required

```php
<x-form.input name="name" required />
<x-form.input name="username" required />
<x-form.input name="email" type="email" required />
<x-form.input name="password" type="password" autocomplete="new-password" required />
```

Modificamos la vista `/sessions/create.blade.php` para que sus input sean required

```php
<x-form.input name="email" type="email" autocomplete="username" required />
<x-form.input name="password" type="password" autocomplete="current-password" required />
```

## Group and Store Validation Logic

Creamos una nueva función en `AdminPostController` la cual se encargara de hacer las validaciones de $post

```php
protected function validatePost(?Post $post = null): array
    {
        $post ??= new Post();

        return request()->validate([
            'title' => 'required',
            'thumbnail' => $post->exists ? ['image'] : ['required', 'image'],
            'slug' => ['required', Rule::unique('posts', 'slug')->ignore($post)],
            'excerpt' => 'required',
            'body' => 'required',
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'published_at' => 'required'
        ]);
    }
```

Modificamos update() para que utilice la función de validación

```
public function update(Post $post)
    {
        $attributes = $this->validatePost($post);

        if ($attributes['thumbnail'] ?? false) {
            $attributes['thumbnail'] = request()->file('thumbnail')->store('thumbnails');
        }

        $post->update($attributes);

        return back()->with('success', 'Post Updated!');
    }
```

Y store()

```php
public function store()
    {
        Post::create(array_merge($this->validatePost(), [
            'user_id' => request()->user()->id,
            'thumbnail' => request()->file('thumbnail')->store('thumbnails')
        ]));

        return redirect('/');
    }
```

## All About Authorization

Modificamos `/app/Providers/AppServiceProvider.php`

```php
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

//Code..
public function boot()
{
    Model::unguard();

    //Básicamente lo mismo que crear un middleware para validar admin
    Gate::define('admin', function (User $user) {
         return $user->username === 'sebastin25';
    });


    //Permite utilizar @admin @endadmin en las vistas .blade.php y realiza la validación sobre si es admin
    Blade::if('admin', function () {
         return optional(request()->user())->can('admin');
    });
}
```

Modificamos `layout.blade.php` para que los enlaces a dashboard y new post solo lo muestre si se es admin

```php
@admin
    <x-dropdown-item href="/admin/posts" :active="request()->is('admin/posts')">Dashboard</x-dropdown-item>
    <x-dropdown-item href="/admin/posts/create" :active="request()->is('admin/posts/create')">New Post</x-dropdown-item>
@endadmin
```

Eliminamos `/app/Http/Middleware/MustBeAdministrator.php` y su referencia en `/app/Http/Kernel.php`

Reemplazamos la rutas de admin por la siguiente, donde definimos un grupo al cual se le aplica el mismo middleware. luego creamos un Route::resource que nos creara todas las rutas excepto show que no la estamos usando.

```php
// Admin Section
Route::middleware('can:admin')->group(function () {
    Route::resource('admin/posts', AdminPostController::class)->except('show');
});
```
