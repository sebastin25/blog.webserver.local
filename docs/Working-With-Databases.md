[<Go Back](/README.md)

# Working With Databases

## Environment Files and Database Connections

Todo token o configuración que sea privada, debe ir en .env

Ej: config de base de datos, tokens de aws, información del servidor de correos, etc

Una vez configurada correctamente la config de la base de datos en .env, haciendo de cuenta que la base de datos ya esta creada, usamos el comando `php artisan migrate` para crear las tablas iniciales.

## Migrations: The Absolute Basics

Los archivos de migraciones se encuentran ubicados en `/database/migrations/`.

En el archivo de migraciones de "users", cambiamos la columna "name" a "username" y seguidamente usamos el comando `php artisan migrate:flesh`. Se debe tener cuidado con este comando ya que hace un drop en todas las tablas.
