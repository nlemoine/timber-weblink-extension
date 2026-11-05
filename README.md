# Timber WebLink Extension

[![Latest Version](https://img.shields.io/packagist/v/n5s/timber-weblink-extension.svg?style=flat-square)](https://github.com/nlemoine/timber-weblink-extension/releases)
[![Packagist](https://img.shields.io/packagist/dt/n5s/timber-weblink-extension.svg?style=flat-square)](https://packagist.org/packages/n5s/timber-weblink-extension)
[![Coverage](https://img.shields.io/codecov/c/github/nlemoine/timber-weblink-extension?style=flat-square)](https://codecov.io/gh/nlemoine/timber-weblink-extension)

Brings the [Symfony WebLink](https://symfony.com/doc/current/web_link.html) Twig functions to [Timber](https://timber.github.io/docs/v2/).

Resources declared in your templates with `preload()`, `preconnect()` and the other functions are sent in a `Link` HTTP header, so the browser can start fetching them before it parses the HTML.

### Requirements

- PHP 8.2+
- WordPress 6.9+
- Timber 2.0+

### Installation

```bash
composer require n5s/timber-weblink-extension
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

Each function returns the URI unchanged, so you can call it where the URI is printed. Attributes are optional: `{as: 'font', type: 'font/woff2', crossorigin: true}` adds `as="font"; type="font/woff2"; crossorigin` to the link. A link added again with the same relation and attributes, for example from a template rendered in a loop, is sent once.

An empty or `null` URI throws an error. Many WordPress functions return `false` when they find nothing, and Twig passes it as an empty string, so check the value first:

```twig
{% if post.thumbnail %}
  <link rel="preload" href="{{ preload(post.thumbnail.src, {as: 'image'}) }}">
{% endif %}
```

URIs are written in the header as they are: do not pass values that come from the request, such as query parameters.

### When the header is sent

The links of every template rendered while WordPress builds the page are sent in one `Link` header, once the page output is complete. This relies on the template enhancement output buffer added in WordPress 6.9: WordPress holds the output of the theme template until it ends, then fires the `wp_finalized_template_enhancement_output_buffer` action, while headers can still be sent.

On older WordPress versions, no header is sent. The buffer only covers front-end templates loaded by WordPress, not admin pages, REST or Ajax requests, and it is not started if your site turns it off with the `wp_should_output_buffer_template_for_enhancement` filter.

Links are added while templates render. A template that Timber serves from its cache, with the `$expires` argument of `Timber::render()` or `Timber::compile()`, does not render, so it adds no links.

To send the header at another time, remove the action and call the function yourself:

```php
remove_action('wp_finalized_template_enhancement_output_buffer', 'n5s\TimberWebLink\send_headers');

\n5s\TimberWebLink\send_headers();
```

### Upgrading

From 1.x, see [UPGRADE-2.0](UPGRADE-2.0.md).

### Contributing

See [CONTRIBUTING](CONTRIBUTING.md).
