[<Go Back](/README.md)

# Forms and Authentication

## Build a Register User Page

Primero ocuparemos crear un controlador para el registro usando `php artisan make:controller RegisterController`, una vez creado el controlador agregaremos una funcion create() para retornar la vista de registro y la funcion store() que utilizaremos para guardar los datos a la DB.

```php
public function create()
{
     return view('register.create');
}

public function store()
{
    $attributes = request()->validate([
        'name' => 'required|max:255',
        'username' => 'required|min:3|max:255',
        'email' => 'required|email|max:255',
        'password' => 'required|min:7|max:255',
    ]);

    User::create($attributes);

    return redirect('/');
}
```

Ya con el controlador y las funciones que necesitaremos creadas, agregaremos las rutas

```php
Route::get('register', [RegisterController::class, 'create']);
Route::post('register', [RegisterController::class, 'store']);
```

Seguidamente en nuestro modelo de usuario reemplazamos nuestra variable `protected $fillable[...];` por `protected $guarded = [];`para que nos permita asignar masivamente todos los atributos.

Y para terminar crearemos nuestra vista `/resources/views/register/create.blade.php`, donde agregaremos `@csrf`, dentro del form, para que el middleware encargado de la protección CSRF valide el request

```php
<x-layout>
    <section class="px-6 py-8">
        <main class="max-w-lg mx-auto mt-10 bg-gray-100 border border-gray-200 p-6 rounded-xl">
            <h1 class="text-center font-bold text-xl">Register!</h1>
            <form method="POST" action="/register" class="mt-10">
                @csrf
                <div class="mb-6">
                    <label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="name">
                        Name
                    </label>
                    <input class="border border-gray-400 p-2 w-full" type="text" name="name" id="name" required>
                </div>
                <div class="mb-6">
                    <label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="username">
                        Username
                    </label>
                    <input class="border border-gray-400 p-2 w-full" type="text" name="username" id="username" required>
                </div>
                <div class="mb-6">
                    <label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="email">
                        Email
                    </label>
                    <input class="border border-gray-400 p-2 w-full" type="email" name="email" id="email" required>
                </div>
                <div class="mb-6">
                    <label class="block mb-2 uppercase font-bold text-xs text-gray-700" for="password">
                        Password
                    </label>
                    <input class="border border-gray-400 p-2 w-full" type="password" name="password" id="password"
                        required>
                </div>
                <div class="mb-6">
                    <button type="submit" class="bg-blue-400 text-white rounded py-2 px-4 hover:bg-blue-500">
                        Submit
                    </button>
                </div>
            </form>
        </main>
    </section>
</x-layout>
```

## Automatic Password Hashing With Mutators

Para que nuestra contraseña se guarde encriptada, usaremos `bcrypt()` y mutators para que de esta forma cada vez que recibamos un atributo 'password', se corra el codigo que vienen en el mutator, que en este caso seria el encriptar la contraseña. Esto lo hacemos en `/app/Models/User.php`

```php
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
```

## Failed Validation and Old Input Data

En caso de fallar la validación a la hora de crear un usuario, ya sea por el name, username, email o password, necesitamos mostrar un mensaje al respecto para que el usuario sepa cual fue el error, esto lo logramos agregando lo siguiente dentro del div que contiene el respectivo input y la variable `$message` tiene el error que nos esta regresando la validación para `name`.

```php
@error('name')
    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
@enderror
```

Para que el input siga teniendo los datos ingresados cuando dio el error, agregaremos el atributo `value="{{ old('name') }}"` a los input donde sea necesario que siga mostrando los datos ya ingresados anteriormente

Ya que usuario y email son unique, tendremos que agregar una validación para que no permita agregar a la DB si ya existen.

```php
public function store()
{
    $attributes = request()->validate([
        'name' => 'required|max:255',
        'username' => 'required|min:3|max:255|unique:users,username',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|min:7|max:255',
    ]);

    User::create($attributes);

    return redirect('/');
}
```

## Show a Success Flash Message

Para mostrar un mensaje informando que la cuenta ha sido creada exitosamente, pasaremos el mensaje por medio de `session()->flash()`, por lo cual modificaremos `RegisterController` para que la funcion store() retorne el mensaje. En este caso estamos usando `with()` en el return, que es otra forma de usar flash().

```php
public function store()
{
    $attributes = request()->validate([
        'name' => 'required|max:255',
        'username' => 'required|min:3|max:255|unique:users,username',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|min:7|max:255',
    ]);

    User::create($attributes);

    return redirect('/')->with('success', 'Your account has been created.');
}
```

Luego crearemos un componente nuevo llamado `flash.blade.php` el cual tendra el mensaje a mostrar.

```php
@if (session()->has('success'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
        class="fixed bg-blue-500 text-white py-2 px-4 rounded-xl bottom-3 right-3 text-sm">
        <p>{{ session('success') }}</p>
    </div>
@endif
```

En `layout.blade.php` agregamos la referencia al componente `<x-flash />`

## Login and Logout

Primero agregaremos los enlaces para registrarse, log in y log out en el view `layout.blade.php` para lo cual modificaremos una parte del codigo ya existente.

```php
<div class="mt-8 md:mt-0 flex items-center">
@auth
    <span class="text-xs font-bold uppercase">Welcome, {{ auth()->user()->name }}!</span>

    <form method="POST" action="/logout" class="text-xs font-semibold text-blue-500 ml-6">
    @csrf

        <button type="submit">Log Out</button>
                    </form>
@else
    <a href="/register" class="text-xs font-bold uppercase">Register</a>
    <a href="/login" class="ml-6 text-xs font-bold uppercase">Log In</a>
@endauth
```

Ahora agregaremos el login en la función store() en `RegisterController`, de esta forma, una vez se registra exictosamente un usuario ya estaria logueado.

```php
$user = User::create($attributes);
auth()->login($user);
```

y modificaremos la variable `public const HOME` que se encuentra en `/app/Providers/RouteServiceProvider.php` para que nos redigirija a nuestro index, `public const HOME = '/';`

Ahora crearemos un controlador para nuestras sesiones usando `php artisan make:controller SessionsController` y agregaremos la función que usaremos para hacer logout

```php
public function destroy()
{
    auth()->logout();
    return redirect('/')->with('success', 'Goodbye!');
}
```

Para terminar, agregamos el logout a nuestras rutas y modificamos las rutas de registro para que utilicen el middleware 'guest', de esta forma si el usuario se encuentra logueado, no podra acceder a ellas.

```php
Route::get('register', [RegisterController::class, 'create'])->middleware('guest');
Route::post('register', [RegisterController::class, 'store'])->middleware('guest');
Route::post('logout', [SessionsController::class, 'destroy'])->middleware('auth');
```
