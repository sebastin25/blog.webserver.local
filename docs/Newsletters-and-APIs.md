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
