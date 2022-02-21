[<Go Back](/README.md)

# Filtering

## Advanced Eloquent Query Constraints

Eliminamos la ruta `'/categories/{category:slug}'` y modificamos nuestro controlador para que filtre categorias tambien

```php
public function index()
{

return view('posts', [
    'posts' => Post::latest()
        ->with('category', 'author')
        ->filter(request(['search', 'category']))
        ->get(),
    'categories' => Category::all(),
    'currentCategory' => Category::firstWhere('slug', reque('category'))
]);
}
```

Luego modificamos el modelo de `Post`

```php
public function scopeFilter($query, array $filters)
{
    $query->when($filters['search'] ?? false, fn($query, $search) =>
    $query
        ->where('title', 'like', '%' . $search . '%')
         ->orWhere('body', 'like', '%' . $search '%'));

    $query->when(
        $filters['category'] ?? false,
           fn ($query, $category) =>
        $query->whereHas(
               'category',
                fn ($query) =>
                $query->where('slug', $category)
        )
    );
}
```

y corregimos el partial view `_post-header` para que le utilice el filtro de categorias

```php
@foreach ($categories as $category)
    <x-dropdown-item href="/?category={{ $category->slug }}" :active="request()->is('categories/' . category->slug )">
    {{ ucwords($category->name) }}
    </x-dropdown-item>
@endforeach
```

## Extract a Category Dropdown Blade Component

Creamos un nuevo componente usando `php artisan make:component CategoryDropdown`

Luego crearemos un nuevo folder llamado posts en `/resources/views/` al cual moveremos nuestras vistas `_post-header`, `post` y `posts`.

Una vez movidas las vistas, procederemos a renombrarlas de la siguiente manera:

\_post header = \_header  
post = show  
posts = index

En nuestra ahora llamada vista `index.blade.php`, modificaremos el @include para actualizarlo con el nuevo nombre de la vista

```php
  @include('posts._header')
```

En `_header.blade.php`, cortaremos `<x-dropdown>` y lo pegaremos en el componente `category=dropdown.blade.php` y en su lugar pondremos usaremos `<x-category-dropdown>`

`_header.blade.php`

```php
<header class="max-w-xl mx-auto mt-20 text-center">
    <h1 class="text-4xl">
        Latest <span class="text-blue-500">Laravel From Scratch</span> News
    </h1>
    <div class="space-y-2 lg:space-y-0 lg:space-x-4 mt-4">
        <!--  Category -->
        <div class="relative lg:inline-flex bg-gray-100 rounded-xl">
            <x-category-dropdown></x-category-dropdown>
            <!-- Search -->
            <div class="relative flex lg:inline-flex items-center bg-gray-100 rounded-xl px-3 py-2">
                <form method="GET" action="#">
                    <input type="text" name="search" placeholder="Find something"
                        class="bg-transparent placeholder-black font-semibold text-sm" value="{{ request('search') }}">
                </form>
            </div>
        </div>
</header>
```

`category=dropdown.blade.php`

```php
<x-dropdown>
    <x-slot name='trigger'>
        <button class="py-2 pl-3 pr-9 text-sm font-semibold w-full lg:w-32 text-left flex lg:inline-flex">
            {{ isset($currentCategory) ? ucwords($currentCategory->name) : 'Categories' }}
            <x-icon name='down-arrow' class=" absolute pointer-events-none" style="right: 12px;" />
        </button>
    </x-slot>
    <x-dropdown-item href="/" :active="request()->routeIs('home')">All</x-dropdown-item>
    @foreach ($categories as $category)
        <x-dropdown-item href="/?category={{ $category->slug }}"
            :active="request()->is('categories/' . $category->slug )">
            {{ ucwords($category->name) }}
        </x-dropdown-item>
    @endforeach
</x-dropdown>
```

Ahora modificaremos nuestro `PostController` para agregar por convención modificaremos nuestras vistas acorde a nuestro controlador y moveremos las peticiones de 'categories' y 'currentCategory' a `/app/View/Components/CategoryDropdown.php`.

`PostController.php`

```php
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
```

`CategoryDropdown.php`

```php
class CategoryDropdown extends Component
{
    public function render()
    {
        return view(
            'components.category-dropdown',
            [
                'categories' => Category::all(),
                'currentCategory' => Category::firstWhere('slug', request('category'))
            ]
        );
    }
}
```

## Author Filtering

Eliminamos nuestra ruta `/authors/{author:username}` de tal forma que solo nos quedarian las siguiente 2 rutas.

```php
Route::get('/', [PostController::class, 'index'])->name('home');
Route::get('/posts/{post:slug}', [PostController::class, 'show']);
```

Ahora en nuestro `PostController`, agregamos el filtro 'author' y eliminamos las otras funciones que ya no necesitamos como 'showAuthor'

```php
class PostController extends Controller
{
    public function index()
    {
        return view('posts.index', [
            'posts' => Post::latest()
                ->with('category', 'author')
                ->filter(request(['search', 'category', 'author']))
                ->get()
        ]);
    }

    public function show(Post $post)
    {
        return view('posts.show', [
            'post' => $post
        ]);
    }
}
```

En nuestro modelo `Post`, agregamos nuestro filtro para autores

```php
public function scopeFilter($query, array $filters)
{

    $query->when($filters['search'] ?? false, fn ($query, $search) =>
        $query
            ->where('title', 'like', '%' . $search . '%')
            ->orWhere('body', 'like', '%' . $search . '%'));
    $query->when(
        $filters['category'] ?? false,
        fn ($query, $category) =>
        $query->whereHas(
            'category',
            fn ($query) =>
            $query->where('slug', $category)
        )
    );
    $query->when(
        $filters['author'] ?? false,
        fn ($query, $author) =>
        $query->whereHas(
            'author',
             fn ($query) =>
            $query->where('username', $author)
        )
    );
    }
```

Y para terminar modificamos nuestras vistas y componentes para agregarle un enlace al nombre de autor para que pueda acceder al filtro. Esto lo hacemos en `post-card`, `post-featured-card` y `show`

```php
<h5 class="font-bold">
    <a href="/?author={{
        $post->author->username }}">
        {{ $post->author->name }}
     </a>
</h5>
```

## Merge Category and Search Queries

Para poder realizar busquedas combinando Category y Search queries, agregaremos un input hidden que se encargara de agregar la categoria a la busqueda

`_header.blade.php`

```php
<form method="GET" action="#">
    @if (request('category'))
        <input type="hidden" name="category" value="{{ request('category') }}">
    @endif
    <input type="text" name="search" placeholder="Find something" class="bg-transparent placeholder-black font-semibold text-sm" value="{{ request('search') }}">
</form>
```

Y para poder buscar combinando Search y Category queries, modificaremos nuestro `<x-dropdown-item>` href para que al seleccionar una categoria desde el dropdown, realice una busqueda de la categoria y el texto

`category-dropdown-blade.php`

```php
<x-dropdown-item
    href="/?category={{ $category->slug }}&{{ http_build_query(request()->except('category')) }}"
    :active="request()->is('categories/' . $category->slug )">
    {{ ucwords($category->name) }}
</x-dropdown-item>
```
