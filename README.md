# Timber WebLink Extension

Bring Symfony WebLink component to Timber.

*Provides native support for managing Link HTTP headers, which are the key to improve the application performance when using HTTP/2 and preloading capabilities of modern web browsers*.

## Install

```bash
composer require hellonico/timber-weblink-extension
```

## Usage

```twig
{{ preload(asset('/css/app.css')) }}
```

Will result in:

```
HTTP/1.1 200 OK
Content-Type: text/html
...
Link: </css/app.css>; rel=preload
```

in your HTTP headers.

For more information about usage, see [https://symfony.com/doc/current/web_link.html](https://symfony.com/doc/current/web_link.html)
