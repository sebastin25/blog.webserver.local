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
