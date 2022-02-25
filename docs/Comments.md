[<Go Back](/README.md)

# Comments

## Write the Markup for a Post Comment

Ahora agregaremos la sección de comentarios, para lo cual crearemos un componete `post-comment.blade.php`.

```php
<article class="flex bg-gray-100 border border-gray-200 p-6 rounded-xl space-x-4">
    <div class="flex-shrink-0">
        <img src="https://i.pravatar.cc/60" alt="" width="60" height="60" class="rounded-xl">
    </div>

    <div>
        <header class="mb-4">
            <h3 class="font-bold">John Doe</h3>

            <p class="text-xs">
                Posted
                <time>8 months ago</time>
            </p>
        </header>

        <p>
            Pariatur officia consectetur do et duis aute aliquip et proident nisi eu. Do voluptate veniam fugiat culpa
            fugiat cillum non. Lorem cillum proident laboris pariatur magna sit nostrud proident cillum. Reprehenderit
            tempor ipsum occaecat aliqua irure ullamco ipsum cillum aliquip Lorem irure aliqua ut nisi. Cillum
            reprehenderit fugiat adipisicing proident aute.
        </p>
    </div>
</article>
```

Luego agregaremos la referencia a la vista `/posts/post-comment.blade.php`, luego del body

```php
<section class="col-span-8 col-start-5 mt-10 space-y-6">
    <x-post-comment />
    <x-post-comment />
    <x-post-comment />
    <x-post-comment />
</section>
```

## Table Consistency and Foreign Key Constraints

Crearemos una tabla para comentarios, su modelo, migración, controlador y factory usando el comando `php artisan make:model Comment -mfc`

Modificamos el archivo de migración con las columnas que necesitaremos.

```php
public function up()
{
    Schema::create('comments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->text('body');
        $table->timestamps();
    });
}
```

En este caso agregamos `constrained()->cascadeOnDelete()` para que nuestro foreign_key referencia el id de la tabla que queremos y cascadeOnDelete para que al eliminarse el post o usuario al que pertenece el comentario, se elimina también. De paso modificamos la migración de posts para agregarle `constrained()->cascadeOnDelete()` en user_id.

## Make the Comments Section Dynamic

Agregamos en nuestro modelo Post para relación con comentarios

```php
public function comments()
{
    return $this->hasMany(Comment::class);
}
```

Y en nuestro modelo Comments agregamos las relaciones con post y author

```php
public function post()
{
    return $this->belongsTo(Post::class);
}

public function author()
{
    return $this->belongsTo(User::class, 'user_id');
}
```

En `CommentFactory.php` modificamos la función definition() para que retorne los datos de prueba que ocupamos.

```php
public function definition()
{
    return [
        'post_id' => Post::factory(),
        'user_id' => User::factory(),
        'body' => $this->faker->paragraph()
    ];
}
```

En nuestra vista `show.blade.php` reemplazamos los `<x-post-comment>` que teníamos.

```php
 @foreach ($post->comments as $comment)
    <x-post-comment :comment="$comment" />
@endforeach
```

Luego modificamos `post-comment.blade.php` para que utilice los datos en nuestra DB

```php
@props(['comment'])

<article class="flex bg-gray-100 border border-gray-200 p-6 rounded-xl space-x-4">
    <div class="flex-shrink-0">
        <img src="https://i.pravatar.cc/60?u={{ $comment->id }}" alt="" width="60" height="60" class="rounded-xl">
    </div>

    <div>
        <header class="mb-4">
            <h3 class="font-bold">{{ $comment->author->username }}</h3>

            <p class="text-xs">
                Posted
                <time>{{ $comment->created_at }}</time>
            </p>
        </header>

        <p>
            {{ $comment->body }}
        </p>
    </div>
</article>
```
