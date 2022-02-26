[<Go Back](/README.md)

# Newsletters and APIs

## Mailchimp API Tinkering

En `layout.blade.php` modificaremos el url del enlace "Subscribe for Updates" para que nos dirija al footer de la pagina.

```php
<a href="#newsletter" class="bg-blue-500 ml-3 rounded-full text-xs font-semibold text-white uppercase py-3 px-5"> Subscribe for Updates </a>
```

Luego agregaremos al inicio de la vista css para cambiar el comportamiento del scroll.

```php
<style>
    html {
        scroll-behavior: smooth;
    }
</style>
```

Nos registraremos en [mailchimp](https://login.mailchimp.com/signup/). Una vez terminado el registro, ingresamos a la pagina y completamos la información que se nos solicitara.

Una vez en la pagina principal,daremos click en nuestra imagen de perfil, Account, Extras, API keys, Create a Key.

![mailchimp api key](/docs/images/mailchimp_api_key.gif)

Luego agregaremos nuestra app key en nuestro archivo .env

```php
MAILCHIMP_KEY=//KEY
```

Luego agregaremos las credenciales en el archivo `/config/services.php`

```php
'mailchimp' => [
    'key' => env('MAILCHIMP_KEY'),
],
```

Siguiendo las instrucciones que se encuentran en su [QuickStart](https://mailchimp.com/developer/marketing/guides/quick-start/), instalamos el paquete de composer requerido

```php
composer require mailchimp/marketing
```

Ahora agregamos una nueva ruta que usaremos para comprobar si hace ping al endpoint de mailchimp

```php
Route::get('ping', function () {

    $mailchimp = new MailchimpMarketing\ApiClient();

    $mailchimp->setConfig([
        'apiKey' => config('services.mailchimp.key'),
        'server' => 'us14',
    ]);

    $response = $mailchimp->ping->get();

    ddd($response);
});
```

lo cual nos regresa un json

```json
{
    "health_status": "Everything's Chimpy!"
}
```

Ahora modificaremos la ruta para agregar un nuevo miembro a la lista

```php
$response = $mailchimp->lists->addListMember("03842ef6ab", [
    "email_address" => "sebastin25@yahoo.es",
    "status" => "subscribed",
]);
```

Esto nos regresa un json

![Mailchimp add member Json Response](/docs/images/mailchimp_add_contact_json_response.png)

y si revisamos en nuestra cuenta de mailchimp, podremos observar que tenemos un nuevo contacto

![mailchimp contacts](/docs/images/mailchimp_contacts.png)

## Make the Newsletter Form Work

Modificaremos nuestra ruta para hacerla dinámica y que su nombre sea acorde a lo que queremos que haga, agregar miembros al newsletter. También agregamos una validación para el email que recibirá y en caso de no pasar la validación, mostrar un mensaje de error.

```php
Route::post('newsletter', function () {

    request()->validate(['email' => 'required|email']);

    $mailchimp = new MailchimpMarketing\ApiClient();

    $mailchimp->setConfig([
        'apiKey' => config('services.mailchimp.key'),
        'server' => 'us14',
    ]);

    try {
        $response = $mailchimp->lists->addListMember("03842ef6ab", [
            "email_address" => request('email'),
            "status" => "subscribed",
        ]);
    } catch (\Exception $e) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => 'This email could not be added to our newsletter list.'
        ]);
    }

    return redirect('/')->with('success', 'You are now signed up for out newsletter!');
});
```

Lo siguiente sera modificar `layout.blade.php` para agregarle @csrf al form, un name al input del email y donde mostraremos el mensaje de error.

```php
<form method="POST" action="/newsletter" class="lg:flex text-sm">
    @csrf

    <div class="lg:py-3 lg:px-5 flex items-center">
        <label for="email" class="hidden lg:inline-block">
            <img src="/images/mailbox-icon.svg" alt="mailbox letter">
        </label>
        <div>
            <input id="email" name="email" type="text" placeholder="Your email address" class="lg:bg-transparent py-2 lg:py-0 pl-4 focus-within:outline-none">
            @error('email')
                <span class="text-xs text-red-500">{{ $message }}</span>
            @enderror
        </div>
    </div>
    <button type="submit" class="transition-colors duration-300 bg-blue-500 hover:bg-blue-600 mt-4 lg:mt-0 lg:ml-3 rounded-full text-xs font-semibold text-white uppercase py-3 px-8"> Subscribe
    </button>
</form>
```

## Extract a Newsletter Service

Modificamos `/config/services.php` y agregamos `MAILCHIMP_LIST_SUBSCRIBERS` a nuestro archivo .env

```php
'mailchimp' => [
        'key' => env('MAILCHIMP_KEY'),
        'lists' => [
            'subscribers' => env('MAILCHIMP_LIST_SUBSCRIBERS')
        ]
    ]
```

Luego crearemos `/app/Services/Newsletter.php`

```php
<?php

namespace App\Services;

use MailchimpMarketing\ApiClient;

class Newsletter
{
    public function subscribe(string $email, string $list = null)
    {
        $list ??= config('services.mailchimp.lists.subscribers');

        return $this->client()->lists->addListMember($list, [
            'email_address' => $email,
            'status' => 'subscribed'
        ]);
    }

    protected function client()
    {
        return (new ApiClient())->setConfig([
            'apiKey' => config('services.mailchimp.key'),
            'server' => 'us6'
        ]);
    }
}
```

Creamos un controlador `NewsletterController`

```php
public function __invoke(Newsletter $newsletter)
    {
        request()->validate(['email' => 'required|email']);

        try {
            $newsletter->subscribe(request('email'));
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'email' => 'This email could not be added to our newsletter list.'
            ]);
        }

        return redirect('/')
            ->with('success', 'You are now signed up for our newsletter!');
    }
```

Modificamos la ruta

```php
Route::post('newsletter', NewsletterController::class);
```
