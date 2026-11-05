# Upgrading from 1.x to 2.0

The Twig functions and their arguments are unchanged: templates need no change.

## PHP 8.2, WordPress 6.9 and Timber 2

2.0 requires PHP 8.2, WordPress 6.9, Timber 2 and Twig 3. Sites on older versions can stay on 1.x. On older WordPress versions, 2.0 sends no header.

## Composer package

The Composer package was renamed from `hellonico/timber-weblink-extension` to `n5s/timber-weblink-extension`: run `composer remove hellonico/timber-weblink-extension`, then `composer require n5s/timber-weblink-extension:^2.0`.

## Namespace

Classes and functions moved from `HelloNico\Twig` and `HelloNico\Timber` to `n5s\TimberWebLink`. The old names are not kept as aliases.

| 1.x | 2.0 |
|---|---|
| `HelloNico\Twig\WebLinkExtension` | `n5s\TimberWebLink\WebLinkExtension` |
| `HelloNico\Timber\add_weblink_extension()` | `n5s\TimberWebLink\add_weblink_extension()` |
| `WebLinkExtension::send_headers()` | `n5s\TimberWebLink\send_headers()` |

`WebLinkExtension` is now final.

## Hooks

The extension is added on the `timber/twig` filter instead of `timber/loader/twig`. Code that removes it must use the new filter and function names:

```php
remove_filter('timber/twig', 'n5s\TimberWebLink\add_weblink_extension');
remove_action('wp_finalized_template_enhancement_output_buffer', 'n5s\TimberWebLink\send_headers');
```

## When the header is sent

1.x sent the header on `wp_footer`, which only worked when the template called `wp_footer` before any output was printed. 2.0 sends it on `wp_finalized_template_enhancement_output_buffer`, once the page output is complete, so links added after `wp_footer` are sent too.

## One Link header per request

Timber creates a Twig environment for each compiled template. 1.x added a new extension to each of them, and each extension sent its own `Link` header. 2.0 adds the same extension to every environment and sends all the links in one `Link` header. A link added several times with the same relation and attributes is sent once.

## Using the extension outside Timber

`new WebLinkExtension()` no longer hooks into WordPress, so its links are never sent. To add the functions to your own Twig environment, add the shared extension instead:

```php
$twig->addExtension(\n5s\TimberWebLink\get_extension());
```
