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
