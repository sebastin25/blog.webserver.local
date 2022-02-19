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

## Blade Components and CSS Grids

Ya que queremos obtener posts dinamicamente, crearemos un componente al que llamaremos `posts-grid.blade.php` el cual tendra el codigo necesario para mostrar dinamicamente nuestros posts.

Ahora modificaremos `posts.blade.php` para que quede de la siguiente forma:

```php
<x-layout>
    <x-slot name='slot'>

        @include('_post-header')

        <main class="max-w-6xl mx-auto mt-6 lg:mt-20 space-y-6">
            {{-- Queremos que solo muestre los posts, si $posts no es 0 --}}
            @if ($posts->count(1))
            {{-- Queremos que el componente tenga acceso a la variable $posts --}}
                <x-posts-grid :posts="$posts" />
            @else
                <p class="text-center"> No posts yet. Please check back later.</p>
            @endif
        </main>
    </x-slot>
</x-layout>
```

Ahora, en el componente que acabamos de crear, tendremos que agregar el componente para `post-featured-card` y los `post-card`

```php
{{-- Le pasamos el primer $post, que se encuentra en el array $ posts, al componente --}}

<x-post-featured-card :post="$posts[0]" />

{{-- Ya que no queremos mostrar div vacios, revisamos si hay mas de 1 post --}}

@if ($posts->count() > 1)
    <div class="lg:grid lg:grid-cols-6">

    {{-- Por cada post que haya en el array de posts agregaremos el componente, saltandonos el primero ya que se esta usando anteriormente como "featured post" --}}

        @foreach ($posts->skip(1) as $post)

        {{-- Le pasamos un atributo class al componente, el cual cambiara la cantidad de columnas que puede usar cada post dependiendo de si son los 2 primeros post del array $posts --}}
            <x-post-card :post="$post" class="{{ $loop->iteration < 3 ? 'col-span-3' : 'col-span-2' }}" />
        @endforeach
    </div>
@endif

```

Ahora modificaremos la vista `post-featured-card` donde le agregaremos al inicio ` @props(['post'])`, para que de esta forma pueda usar la variable 'post' que se le esta pasando al componente. Luego modificaremos el archivo para mostrar los datos que tenemos en `$post`, en el caso del tiempo usaremos `$post->created_at->diffForHumans()` para mostrar cuando fue publicado el post de una forma mas legible para el usuario.

```php

@props(['post'])

<article
    class="transition-colors duration-300 hover:bg-gray-100 border border-black border-opacity-0 hover:border-opacity-5 rounded-xl">
    <div class="py-6 px-5 lg:flex">
        <div class="flex-1 lg:mr-8">
            <img src="/images/illustration-1.png" alt="Blog Post illustration" class="rounded-xl">
        </div>

        <div class="flex-1 flex flex-col justify-between">
            <header class="mt-8 lg:mt-0">
                <div class="space-x-2">
                    <a href="/categories/{{ $post->category->slug }}"
                        class="px-3 py-1 border border-blue-300 rounded-full text-blue-300 text-xs uppercase font-semibold"
                        style="font-size: 10px">{{ $post->category->name }}</a>
                </div>

                <div class="mt-4">
                    <h1 class="text-3xl">
                        <a href="/posts/{{ $post->slug }}">
                            {{ $post->title }}
                        </a>
                    </h1>

                    <span class="mt-2 block text-gray-400 text-xs">
                        Published <time>{{ $post->created_at->diffForHumans() }}</time>
                    </span>
                </div>
            </header>

            <div class="text-sm mt-2">
                <p>
                    {{ $post->excerpt }}
                </p>
            </div>

            <footer class="flex justify-between items-center mt-8">
                <div class="flex items-center text-sm">
                    <img src="/images/lary-avatar.svg" alt="Lary avatar">
                    <div class="ml-3">
                        <h5 class="font-bold">{{ $post->author->name }}</h5>
                        <h6>Mascot at Laracasts</h6>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <a href="/posts/{{ $post->slug }}"
                        class="transition-colors duration-300 text-xs font-semibold bg-gray-200 hover:bg-gray-300 rounded-full py-2 px-8">Read
                        More</a>
                </div>
            </footer>
        </div>
    </div>
</article>
```

Ahora modificaremos la vista `post-card` donde le agregaremos al inicio ` @props(['post'])`, para que de esta forma pueda usar la variable 'post' que se le esta pasando al componente. Luego modificaremos el archivo para mostrar los datos que tenemos en `$post` y ya que les estaremos pasando el attributo 'class', deberemos usar `<article {{ $attributes->merge(['class' =>'....']) }}>` para hacer un merge del atributo 'class' que le estamos pasando con el atributo 'class' ya existe del articulo.

```php
 @props(['post'])

 <article
     {{ $attributes->merge(['class' =>'transition-colors duration-300 hover:bg-gray-100 border border-black border-opacity-0 hover:border-opacity-5 rounded-xl']) }}>
     <div class="py-6 px-5">
         <div>
             <img src="/images/illustration-3.png" alt="Blog Post illustration" class="rounded-xl">
         </div>

         <div class="mt-8 flex flex-col justify-between">
             <header>
                 <div class="space-x-2">
                     <a href="/categories/{{ $post->category->slug }}"
                         class="px-3 py-1 border border-blue-300 rounded-full text-blue-300 text-xs uppercase font-semibold"
                         style="font-size: 10px"> {{ $post->category->name }}</a>
                 </div>

                 <div class="mt-4">
                     <h1 class="text-3xl">
                         {{ $post->title }}
                     </h1>

                     <span class="mt-2 block text-gray-400 text-xs">
                         Published <time>{{ $post->created_at->diffForHumans() }}</time>
                     </span>
                 </div>
             </header>

             <div class="text-sm mt-4">
                 <p>
                     {{ $post->excerpt }}
                 </p>
             </div>

             <footer class="flex justify-between items-center mt-8">
                 <div class="flex items-center text-sm">
                     <img src="/images/lary-avatar.svg" alt="Lary avatar">
                     <div class="ml-3">
                         <h5 class="font-bold">{{ $post->author->name }}</h5>
                         <h6>Mascot at Laracasts</h6>
                     </div>
                 </div>

                 <div>
                     <a href="/posts/{{ $post->slug }}"
                         class="transition-colors duration-300 text-xs font-semibold bg-gray-200 hover:bg-gray-300 rounded-full py-2 px-8">Read
                         More</a>
                 </div>
             </footer>
         </div>
     </div>
 </article>
```

## Convert the Blog Post Page

Ahora copiaremos el codigo html de `/Laravel-From-Scratch-HTML-CSS-main/post.html` a nuestra vista `post`, sobreescribiendo lo que se encuentra dentro del tag `<x-slot name='slot'>`,eliminamos lo que no sea necesario como las barras de navegación, headers, footers, etc, arreglamos las imagenes y mostramos los datos segun '$post'

Ya que estamos mostrando repetidamente la categoria en varias de las vistas, crearemos un componente `category-button` el cual usaremos solo para cargar las categorias.

`category-button.blade.php`

```php
@props(['category'])

<a href="/categories/{{ $category->slug }}"
    class="px-3 py-1 border border-blue-300 rounded-full text-blue-300 text-xs uppercase font-semibold"
    style="font-size: 10px">{{ $category->name }}</a>
```

`post.blade.php`

```php
<x-layout>
    <x-slot name='slot'>
        <section class="px-6 py-8">
            <main class="max-w-6xl mx-auto mt-10 lg:mt-20 space-y-6">
                <article class="max-w-4xl mx-auto lg:grid lg:grid-cols-12 gap-x-10">
                    <div class="col-span-4 lg:text-center lg:pt-14 mb-10">
                        <img src="/images/illustration-1.png" alt="" class="rounded-xl">
                        <p class="mt-4 block text-gray-400 text-xs">
                            Published <time>{{ $post->created_at->diffForHumans() }}</time>
                        </p>
                        <div class="flex items-center lg:justify-center text-sm mt-4">
                            <img src="/images/lary-avatar.svg" alt="Lary avatar">
                            <div class="ml-3 text-left">
                                <h5 class="font-bold">{{ $post->author->name }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-8">
                        <div class="hidden lg:flex justify-between mb-6">
                            <a href="/"
                                class="transition-colors duration-300 relative inline-flex items-center text-lg hover:text-blue-500">
                                <svg width="22" height="22" viewBox="0 0 22 22" class="mr-2">
                                    <g fill="none" fill-rule="evenodd">
                                        <path stroke="#000" stroke-opacity=".012" stroke-width=".5"
                                            d="M21 1v20.16H.84V1z">
                                        </path>
                                        <path class="fill-current"
                                            d="M13.854 7.224l-3.847 3.856 3.847 3.856-1.184 1.184-5.04-5.04 5.04-5.04z">
                                        </path>
                                    </g>
                                </svg>
                                Back to Posts
                            </a>
                            <div class="space-x-2">
                                <x-category-button :category="$post->category" />
                            </div>
                        </div>
                        <h1 class="font-bold text-3xl lg:text-4xl mb-10">
                            {{ $post->title }}
                        </h1>
                        <div class="space-y-4 lg:text-lg leading-loose">
                            {{ $post->body }}
                        </div>
                    </div>
                </article>
            </main>
        </section>
    </x-slot>
</x-layout>

```

Y procederemos a modificar las vistas que muestran categorias para que utilicen el componente creado para ello.

## A Small JavaScript Dropdown Detour

Ahora usaremos la libreria [ Alpine.js](https://github.com/alpinejs/alpine) para que dropdown de los filtros funcione, por lo cual deberemos seguir las instrucciones en su web para agregarlo al proyecto.

Nuestro dropdown debera quedar de la siguiente manera:

```php
<div class="relative lg:inline-flex bg-gray-100 rounded-xl">
    <div x-data="{show: false}" @click.away="show = false">
        <button @click="show = !show" class="py-2 pl-3 pr-9 text-sm font-semibold w-full lg:w-32 text-left flex lg:inline-flex">
        {{ isset($currentCategory) ? ucwords($currentCategory->name) : 'Categories' }}
        <svg class="transform -rotate-90 absolute pointer-events-none" style="right: 12px;"
        width="22" height="22" viewBox="0 0 22 22">
            <g fill="none" fill-rule="evenodd">
                <path stroke="#000" stroke-opacity=".012" stroke-width=".5" d="M21 1v20.16H.84V1z">
                </path>
                <path fill="#222" d="M13.854 7.224l-3.847 3.856 3.847 3.856-1.184 1.184-5.04-5.04 5.04-5.04z">
                 </path>
            </g>
        </svg>
        </button>
        <div x-show="show" class="py-2 absolute bg-gray-100 mt-2 rounded-xl w-full z-50"
        style="display: none">
            <a href="/" class="block text-left px-3 text-sm leading-6 hover:bg-blue-500 focus:bg-blue-500 hover:text-white focus:text-white">All<a>
                @foreach ($categories as $category)
                    <a href="/categories/{{ $category->slug }}"
                    class="block text-left px-3 text-sm leading-6 hover:bg-blue-500 focus:bg-blue-500                 hover:text-white focus:text-white {{ isset($currentCategory) && $currentCategory->is($category) ? 'bg-blue-500 text-white' : '' }}">
                        {{ ucwords($category->name) }}</a>
                @endforeach
    </div>
</div>
```

y deberemos modificar nuestras rutas para poder pasar 'currentCategory' y 'categories'

```php
Route::get('/', function () {
    return view('posts', [
        'posts' => Post::latest()->with('category', 'author')->get(),
        'categories' => Category::all()
    ]);
});

Route::get('/categories/{category:slug}', function (Category $category) {
    return view('posts', [
        'posts' => $category->posts->load(['category', 'author']),
        'currentCategory' => $category,
        'categories' => Category::all()
    ]);
});

Route::get('/authors/{author:username}', function (User $author) {
    return view('posts', [
        'posts' => $author->posts->load(['category', 'author']),
        'categories' => Category::all()
    ]);
});

```
