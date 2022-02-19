[<Go Back](/README.md)

# Integrate the Design

## Convert the HTML and CSS to Blade

Primero tenemos que descargar los archivos html,css y imagenes requeridas para este capitulo desde el [repositorio](https://github.com/laracasts/Laravel-From-Scratch-HTML-CSS). Una vez extraido, la carpeta 'images' la copiaremos a `/public/`.

Reemplazaremos el codigo html que tenemos en`/resources/views/components/layout.blade.php` con el codigo html que se encuentra dentro de `/Laravel-From-Scratch-HTML-CSS-main/index.html`. Una vez hecho esto, arreglaremos las etiquetas <img> de tal forma que la carpeta a la que el src hace referencia quede de esta forma: `src="/images/..."` (se reemplaza lo puntos por nombre del archivo), de esta forma las imagenes funcionaran correctamente.

```php
Incorrecto

 <img src="./images/logo.svg" alt="Laracasts Logo" width="165" height="16">


Correcto
 <img src="/images/logo.svg" alt="Laracasts Logo" width="165" height="16">
```

Ahora cortaremos el codigo html que se encuentra dentro del tag `<main>` y agregaremos en su lugar `{{ $slot }}` entre el tag de `<nav>` y `<footer>`. Seguidamente en `/resources/views/posts.blade.php`, borraremos el codigo html que contiene y agregaremos un tag `<x-layout>` y un tag `<x-slot name='slot'>` dentro del cual pegaremos el codigo.

```php
<x-layout>
    <x-slot name='slot'>
        {{-- pegamos el codigo aqui --}}
    </x-slot>
</x-layout>
```

Ahora crearemos 2 componentes en `/resources/views/components/` a los cuales llamaremos `post-card.blade.php` y `post-features-card.blade.php`.

copiaremos 1 de los tag `<article>` que se encuentra dentro de `<div class="lg:grid lg:grid-cols-2">` y pegaremos el codigo en `/resources/views/components/post-card.blade.php`, seguidamente borraremos todos los `<article>` que se encuentran dentro de `<div class="lg:grid lg:grid-cols-2">` y `<div class="lg:grid lg:grid-cols-3">` y dentro de estos `<div>` escribiremos el tag `<x-post-card/>` segun la cantidad de articulos que habian en el div.

Lo siguiente sera cortar el `<article>` que se encuentra dentro de `<main class="max-w-6xl mx-auto mt-6 lg:mt-20 space-y-6">` y antes de `<div class="lg:grid lg:grid-cols-2">`, agregar en su lugar `<x-post-featured-card />`, y luego pegarlo en `/resources/views/components/post-featured-card.blade.php`.

Ahora crearemos un partial view en `/resources/views/` al que llamaremos `_post-header.blade.php`, una vez creado el archivo cortaremos el `<header>`, agregaremos `@include('_post-header')` en su lugar y pegaremos el header que cortamos en el partial view que acabamos de crear. `post.blade.php` deberia quedar de esta manera

```php
<x-layout>
    <x-slot name='slot'>

        @include('_post-header')

        <main class="max-w-6xl mx-auto mt-6 lg:mt-20 space-y-6">

            <x-post-featured-card />

            <div class="lg:grid lg:grid-cols-2">
                <x-post-card />
                <x-post-card />
            </div>

            <div class="lg:grid lg:grid-cols-3">
                <x-post-card />
                <x-post-card />
                <x-post-card />
            </div>
        </main>
    </x-slot>
</x-layout>
```
