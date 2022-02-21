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
