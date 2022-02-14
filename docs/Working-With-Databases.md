[<Go Back](/README.md)

# Working With Databases

## Environment Files and Database Connections

Todo token o configuración que sea privada, debe ir en .env

Ej: config de base de datos, tokens de aws, información del servidor de correos, etc

Una vez configurada correctamente la config de la base de datos en .env, haciendo de cuenta que la base de datos ya esta creada, usamos el comando `php artisan migrate` para crear las tablas iniciales.

## Migrations: The Absolute Basics

Los archivos de migraciones se encuentran ubicados en `/database/migrations/`.

En el archivo de migraciones de "users", cambiamos la columna "name" a "username" y seguidamente usamos el comando `php artisan migrate:flesh`. Se debe tener cuidado con este comando ya que hace un drop en todas las tablas.

## Eloquent and the Active Record Pattern

Revertimos los cambios en el archivo de migraciones de "users" y hacemos la migración nuevamente.

Cada tabla en la DB tendra un correspondiente modelo en plural, por ejemplo: users -> user

Se puede crear un usuario utilizando `php artisan tinker` y siguiendo los siguientes pasos:

```php
$user = new App\Models\User;

$user->name = 'JeffreyWay';
$user->email ='jeffrey@laracast.com';
$user->password = bcrypt('!password'); //bcrypt se usa para encriptar la contraséña
$user->save(); // Guarda en la DB

$user = new User; // tinker asocia User como un alias del modelo, permitiendonos hacerlo tambien de esta forma
$user->name = 'Sally';
$user->email ='sally@example.com';
$user->password = bcrypt('sally');
$user->save();
```

## Make a Post Model and Migration

Elinamos el modelo o clase 'Post.php' que teniamos, ya que usaremos un modelo eloquent.

Crearemos un modelo nuevo usando `php artisan make:migration`, podemos usar `php artisan help make:migration` para ver información sobre el comando.

![php artisan help make:migration](./images/php_artisan_help_make_migration.png)

Ya que hemos visto la ayuda, sabemos que el comando que ocupamos es
`php artisan make:migration create_posts_table`, ahora si revisamos `/database/migrations/` podemos ver que tenemos creado un nuevo archivo de migraciones.

Ahora en el archivo de migraciones nuevo que se ha creado llamado `/database/migrations/2022_02_14_202409_create_posts_table.php`, podemos ver que tenemos una funcion up() la cual se utiliza para crear la tabla y especificar las columnas a utilizar. Modificaremos esta funcion para que la tabla tenga las columnas que necesitamos y usaremos `php artisan migrate`:

```php
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('excerpt');
            $table->text('body');
            $table->timestamps();
            $table->timestamp('published_at')->nullable();
        });
    }
```

Ahora crearemos un modelo 'Post' usando el comando `php artisan make:model Post` lo cual crea `/app/Models/Post.php`.

Agregaremos 2 posts a la DB utilizando `php artisan tinker`:

```php
$post = new App\Models\Post;
$post->title = 'My first Post';
$post->excerpt ='Reprehenderit amet non dolore culpa reprehenderit consectetur velit veniam occaecat deserunt do elit culpa. Sunt culpa minim veniam dolor dolor Lorem culpa consequat exercitation amet ea. Amet non veniam dolor officia excepteur eiusmod ipsum aliqua labore nulla eiusmod aliquip excepteur enim. Pariatur excepteur deserunt velit deserunt ullamco dolore minim laboris velit aliquip veniam ut. Dolor dolore quis et nulla anim commodo. Esse elit cillum ex nisi officia duis ad duis velit amet esse sunt mollit occaecat.';
$post->body = 'Nostrud nostrud laborum sunt ut commodo aliqua nostrud incididunt non labore consequat in reprehenderit nulla. Ex minim mollit eiusmod dolore incididunt excepteur Lorem laboris ipsum dolor magna deserunt irure. Sint pariatur deserunt enim irure ex culpa proident sit laborum sit sunt consequat ex nostrud. Exercitation exercitation velit occaecat consequat pariatur id dolor exercitation ad cillum. Sint consectetur enim irure adipisicing incididunt et cillum ea laborum irure. Exercitation occaecat culpa irure anim ullamco elit enim.

Elit dolore reprehenderit sunt aliqua. Eu aliqua pariatur magna ullamco dolore consequat. Adipisicing adipisicing cillum aliqua laborum ea esse in anim ad cupidatat sint sunt.';
$post->save();
```

Ya con el post agregado, el siguiente paso seria cambiar en las vistas y en las rutas para que utilicemos `$id` y no `$slug`, ya que ahora lo que recibiremos son IDs.
