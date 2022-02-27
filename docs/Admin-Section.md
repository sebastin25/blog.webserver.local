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
