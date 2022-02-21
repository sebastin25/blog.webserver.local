[<Go Back](/README.md)

# Pagination

## Laughably Simple Pagination

Para agregar paginación, modificamos en `PostController` para que regrese los datos como paginación y nos muestre 5 posts por pagina

```php
public function index()
{
    return view('posts.index', [
        'posts' => Post::latest()
                ->with('category', 'author')
                ->filter(request(['search', 'category', 'author']))
                ->paginate(5)
    ]);
}
```

y modificamos `category-dropdown.blade.php` para que ignore el numero de pagina a la hora de hacer una busqueda

```php
<x-dropdown-item href="/?{{ http_build_query(request()->except('category', 'page')) }}"
        :active="request()->routeIs('home')">All</x-dropdown-item>
    @foreach ($categories as $category)
        <x-dropdown-item
            href="/?category={{ $category->slug }}&{{ http_build_query(request()->except('category', 'page')) }}"
            :active="request()->is('categories/' . $category->slug )">
            {{ ucwords($category->name) }}
        </x-dropdown-item>
    @endforeach
```

Para poder tener acceso a la vista de la paginación, usaremos `php artisan vendor:publish`, lo cual nos mostrara una lista de los distintos archivos de providers que podemos publicar o obtener para modificar, en nuestro caso ocupamos `laravel-pagination`

![php artisan vendor:publish](/docs/images/php_artisan_vendor_publish.png)

Una vez hecho esto, en nuestra vista index agregaremos lo siguiente para que nos muestre la paginación

```php
 @if ($posts->count(1))
    <x-posts-grid :posts="$posts" />

    {{ $posts->links() }}
 @else
```

Y para que la paginación nos sirva junto con las busquedas, tendremos que agregar `->withQueryString()` a nuestro index en `PostController`

```php
public function index()
{
     return view('posts.index', [
        'posts' => Post::latest()
                ->with('category', 'author')
                ->filter(request(['search', 'category', 'author']))
                ->paginate(3)->withQueryString()
        ]);
}
```
