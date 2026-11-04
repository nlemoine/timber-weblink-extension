# Timber WebLink Extension

[![Latest Version](https://img.shields.io/packagist/v/hellonico/timber-weblink-extension.svg?style=flat-square)](https://github.com/nlemoine/timber-weblink-extension/releases)
[![Packagist](https://img.shields.io/packagist/dt/hellonico/timber-weblink-extension.svg?style=flat-square)](https://packagist.org/packages/hellonico/timber-weblink-extension)
[![Coverage](https://img.shields.io/codecov/c/github/nlemoine/timber-weblink-extension?style=flat-square)](https://codecov.io/gh/nlemoine/timber-weblink-extension)

Brings the [Symfony WebLink](https://symfony.com/doc/current/web_link.html) Twig functions to [Timber](https://timber.github.io/docs/v2/).

Resources declared in your templates with `preload()`, `preconnect()` and the other functions are sent in a `Link` HTTP header, so the browser can start fetching them before it parses the HTML.

### Requirements

- PHP 8.2+
- Timber 2.0+

### Installation

```bash
composer require hellonico/timber-weblink-extension
```

The extension adds itself to Timber's Twig environment on the `timber/twig` filter: there is nothing to set up. It also works when Composer's autoloader is loaded before WordPress, as in [Bedrock](https://roots.io/bedrock/).

### Usage

```twig
<link rel="stylesheet" href="{{ preload(theme.link ~ '/css/app.css', {as: 'style'}) }}">
```

Sends:

```
Link: <https://example.com/wp-content/themes/my-theme/css/app.css>; rel="preload"; as="style"
```

| Function | Relation |
| ------------- | ------------- |
| `preload(uri, attributes)` | `preload` |
| `dns_prefetch(uri, attributes)` | `dns-prefetch` |
| `preconnect(uri, attributes)` | `preconnect` |
| `prefetch(uri, attributes)` | `prefetch` |
| `prerender(uri, attributes)` | `prerender` |
| `link(uri, rel, attributes)` | any relation |

Each function returns the URI unchanged, so you can call it where the URI is printed. Attributes are optional: `{as: 'font', type: 'font/woff2', crossorigin: true}` adds `as="font"; type="font/woff2"; crossorigin` to the link.

### When the header is sent

The links of every template rendered during the request are sent in one `Link` header on the `wp_footer` action. Headers can only be sent before the page is printed. This works when your base template calls `{{ function('wp_footer') }}` and is rendered with `Timber::render()`, which prints the page once it is compiled.

Links added after `wp_footer` runs are not sent, and neither is the header once the page output has started, for example when a PHP template calls `wp_footer()` after Timber has printed some markup.

To send the header at another time, remove the action and call the function yourself:

```php
remove_action('wp_footer', 'n5s\TimberWebLink\send_headers', PHP_INT_MAX);

\n5s\TimberWebLink\send_headers();
```

### Upgrading

From 1.x, see [UPGRADE-2.0](UPGRADE-2.0.md).

### Contributing

See [CONTRIBUTING](CONTRIBUTING.md).
