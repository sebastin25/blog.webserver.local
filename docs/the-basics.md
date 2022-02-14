[<Go Back](/README.md)

# The Basics

## How a Route Loads a View

Se utiliza el `return view('welcome');` para retornar la vista welcome que se encuentra en `/resources/views/` y no es necesario incluir \*.blade.php para que funcione

```php
Route::get('/', function () {
    return view('welcome');
});
```

Se puede retornar varios tipos de datos, por ejemplo json

```php
Route::get('/json', function () {
    return ['foo' => 'bar'];
});
```

## Include CSS and JavaScript

Podemos agregar css y js creando los respectivos archivos .js y .css en `/public/` , en este caso se utilizo app.js y app.css

app.js

```js
alert("I am here");
```

app.cs

```css
body {
    background: navy;
    color: white;
}
```

Luego agregamos en welcome.blade.php que se encuentra en `/resources/views/` las referencias hacia los archivos

```html
<link rel="stylesheet" href="/app.css" />
<script src="/app.js"></script>
```

## Make a Route and Link to it

Para crear una nueva ruta y linkear hacia ella, necesitamos crear una vista nueva en `/resources/views/` a la cual llamaremos post.blade.php y deberemos agregar una ruta nueva en `/routes/web.php`

```php
Route::get('/post', function () {
    return view('post');
});
```

`/post` es la ruta para acceder a la vista 'post', por la cual si quisieramos ver esta vista, ingresariamos desde http://blog.webserver.local/post

## Store Blog Posts as HTML Files

Creamos una carpeta llamada posts en `/resources/` la cual tendra los archivos .html de nuestros post. En `/routes/web.php` modificaremos la ruta `/post `para que quede de la siguiente manera:

```php
Route::get('/posts/{post}', function ($slug) {

    $path = __DIR__ . "/../resources/posts/{$slug}.html";

    if (!file_exists($path)) {
        return redirect('/');
    }

    $post = file_get_contents($path);

    return view('post', [
        'post' => $post
    ]);
});
```

Estamos modificando la ruta `/post` a `/posts/{post}` para que de esta manera podamos recibir por medio del URI cual seria el directorio o nombre del post, ej: http://blog.webserver.local/posts/my-first-post. Este directorio o nombre del post que estamos recibiendo, lo asignamos a la variable `$slug` desde `function ($slug)`, seguidamente tenemos una variable `$path` en la cual tenemos la dirección del directorio donde se encuentra el archivo, junto con la variable `$slug` dando el nombre del archivo seguido del formato.

Asignamos a la variable `$post` los contenidos del archivo que se encuentra en `$path` y para terminar retornamos la vista `'post'` a la cual estamos diciendo que son los datos que contiene la variable $post`

## Route Wildcard Constraints

Para agregar restricciones en los directorios o nombre de post que podemos recibir, se realizaria de la siguiente manera:

```php
Route::get('/posts/{post}', function ($slug) {

    $path = __DIR__ . "/../resources/posts/{$slug}.html";

    if (!file_exists($path)) {
        return redirect('/');
    }

    $post = file_get_contents($path);

    return view('post', [
        'post' => $post
    ]);
})->where('post', '[A-z_\-]+');
```

Notese que al final tenemos `->where('post', '[A-z_\-]+');` , esto se esta utilizando para especificar que `'post'` solo puede recibir los caracteres que cumplan con la expresion regular que se esta utilizando. Tambien se podrian utilizar helpers como `->whereAlphaNumeric('post');`

## Use Caching for Expensive Operations

Para agregar caching, lo podemos hacer de la siguiente manera:

```php
Route::get('/posts/{post}', function ($slug) {

    $path = __DIR__ . "/../resources/posts/{$slug}.html";

    if (!file_exists($path)) {
        return redirect('/');
    }

    $post = cache()->remember("posts.{$slug}", 5, function() use ($path) {
        return file_get_contents($path);
    });

    return view('post', [
        'post' => $post
    ]);
})->where('post', '[A-z_\-]+');
```

Notese que estamos usando

```php
cache()->remember("posts.{$slug}", 5, function() use ($path) {
        return file_get_contents($path)
```

donde `"posts.{$slug}"` son los datos que estamos guardando, `5` son los segundos y en `function() use ($path)` estamos asignado una variable para que pueda recibir datos. En lugar de usar segundos, podemos usar `now()->addMinutes(5)` o alguna de sus variables para horas, dias, semanas, etc

Tambien es posible hacerlo de la siguiente manera para que quede un poco mas limpio el codigo.

```php
    $post = cache()->remember("posts.{$slug}", 1200, fn () => file_get_contents($path));
```

## Use the Filesystem Class to Read a Directory

Usaremos clases para separar parte de la logica en funciones que podremos llamar en el archivo de routas para obtener la información que necesitaremos, por lo cual comenzamos creando una archivo `Post.php` en `/app/Models/` el cual llevara lo siguiente:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;

class Post
{
    public static function all()
    {
        $files = File::files(resource_path("posts/"));

        return array_map(fn ($file) => $file->getContents(), $files);
    }

    public static function find($slug)
    {

        if (!file_exists($path = resource_path("posts/{$slug}.html"))) {
            throw new ModelNotFoundException();
        }

        return cache()->remember("posts.{$slug}", 1200, fn () => file_get_contents($path));
    }
}

```

Notese que al principio del archivo tenemos 2 lineas `use`, estas se usan para importar esos helpers de Laravel. Luego tendremos la clase Post la cual contiene 2 funciones.

`all()` la utilizamos para retornar todos los archivos de los posts que se encuentran en la carpeta `posts/` y luego retornamos un array que contiene los contenidos de esos posts.

`find($slug)` lo utilizamos para buscar un post en especifico, reemplazando el codigo que teniamos en la ruta. Primero revisa si hay un archivo existe con el nombre que se le esta pasando y si no es asi, tira una excepción.
Si el archivo existe, retorna el cache con los contenidos del post.

El archivo de rutas quedaria asi:

```php
Route::get('/', function () {
    return view('posts', [
        'posts' => Post::all()
    ]);
});

Route::get('/posts/{post}', function ($slug) {

return view('post', [
        'post' => Post::find($slug)
    ]);
})->where('post', '[A-z_\-]+');
```

Recordar que es importante asegurarse que se este importando la clase Post con un `use App\Models\Post;`
