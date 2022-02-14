[<Go Back](/README.md)

# Blade

## Blade: The Absolute Basics

```php
{{$post->body}} // Equivalente a  <?= $post->body ?>
{!!$post->body!!} // Igual a arriba pero lo trata como html
```

```php
@foreach ($posts as $post)
@endforeach

Es lo equivalente a:

<?php foreach($posts as $post) : ?>
<?php endforeach; ?>

```

`$loop` sirve para ver la información sobre en loop en un foreach.

Existe un equivalente para casi cualquier funcion de php.

## Blade Layouts Two Ways

Hay 2 formas de crear blade layouts:

1.  Crear layout.blade.php en `/resources/views`

el archivo layout, lleva las etiquetas que se repiten en las paginas, por ejemplo la información del header, footer, banner, etc.

las secciones se marcar con `@yield('banner')`, siendo banner el nombre que se le quiere dar a esa seccion.

En las otras vistas, cuando se quiera ingresar datos a esa sección banner, se usa

```php
@section('banner') //Para iniciar sección
    <h1>My blog</h1>
@endsection //Para terminarla.

```

2. Crear blade components en `/resources/views/components/`

en este caso para marcar las secciones se utiliza `{{$content}}`, en el caso de las vistas seria de esta forma:

```php
<x-layout>
    <x-slot name = 'content'>
</x-layout>
```

## A Few Tweaks and Consideration

Debido a los cambios que se hicieron en la forma en que obtenemos cada post, ya no es necesario `->where('post', '[A-z_\-]+')` en la ruta, por lo cual se elimina y se modifica la funcion `find()` que se encuentra en la clase `Post.php`, para que muestre un 404 al ingresar una dirección incorrecta.

```php
    public static function find($slug)
    {
        return static::all()->firstWhere('slug', $slug);
    }

    public static function findorFail($slug)
    {
        $post = static::find($slug);
        if (!$post) {
            throw new ModelNotFoundException();
        }
        return $post;
    }
```

Se debe usar `findorFall()` en las rutas donde se requiera.
