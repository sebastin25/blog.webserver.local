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

## Include CSS and JavaScript

Podemos agregar css y js creando los respectivos archivos .js y .css en /public/ , en este caso se utilizo app.js y app.css

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

Luego agregamos en welcome.blade.php que se encuentra en /resources/views/ las referencias hacia los archivos

```html
<link rel="stylesheet" href="/app.css" />
<script src="/app.js"></script>
```

## Make a Route and Link to it

Para crear una nueva ruta y linkear hacia ella, necesitamos crear una vista nueva en /resources/views/ a la cual llamaremos post.blade.php y deberemos agregar una ruta nueva en /routes/web.php

```php
Route::get('/post', function () {
    return view('post');
});
```

'/post' es la ruta para acceder a la vista 'post', por la cual si quisieramos ver esta vista, ingresariamos desde http://blog.webserver.local/post
