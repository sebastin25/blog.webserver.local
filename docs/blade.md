[<Go Back](/README.md)

# Blade

## Blade: The Absolute Basics

```php
{{$post->body}} // Equivalente a  <?= $post->body ?>
{!!$post->body!!} // Igual a arriba pero lo trata como html
```

```php
@foreach ($posts as $post)
@endforeach

Es lo equivalente a:

<?php foreach($posts as $post) : ?>
<?php endforeach; ?>

```

`$loop` sirve para ver la información sobre en loop en un foreach.

Existe un equivalente para casi cualquier funcion de php.
