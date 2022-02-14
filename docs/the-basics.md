[<Go Back](/README.md)

# The Basics

## How a Route Loads a View

Se utiliza el `return view('welcome');` para retornar la vista welcome que se encuentra en /resources/views/ y no es necesario incluir \*.blade.php para que funcione

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
