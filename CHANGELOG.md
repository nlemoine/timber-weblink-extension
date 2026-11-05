# Changelog

## [2.0.0](https://github.com/nlemoine/timber-weblink-extension/compare/1.0.0...2.0.0) (2026-11-05)


### ⚠ BREAKING CHANGES

* an empty URI, including false passed from a template, now throws instead of sending an empty link.
* WordPress 6.9 is required. On older versions, no header is sent. The header is sent on wp_finalized_template_enhancement_output_buffer instead of wp_footer.
* the Composer package is now n5s/timber-weblink-extension. Projects must replace hellonico/timber-weblink-extension with it.
* new WebLinkExtension() no longer hooks into wp_footer, and WebLinkExtension::send_headers() is replaced by n5s\TimberWebLink\send_headers(). Add n5s\TimberWebLink\get_extension() to your own Twig environments instead.
* HelloNico\Twig\WebLinkExtension is now n5s\TimberWebLink\WebLinkExtension, and HelloNico\Timber\add_weblink_extension() is now n5s\TimberWebLink\add_weblink_extension(). The extension is added on timber/twig instead of timber/loader/twig. PHP 8.2, Timber 2 and Twig 3 are required.

### Features

* send the Link header once the page output is complete ([de859a0](https://github.com/nlemoine/timber-weblink-extension/commit/de859a0ab8a41ccba345d7eab80ece03a09e2b8c))


### Bug Fixes

* add the extension once to a Twig environment ([0618448](https://github.com/nlemoine/timber-weblink-extension/commit/0618448b185670f1fb6557684d499d018dbb333c))
* register the hooks when Composer's autoloader loads before WordPress ([76dea58](https://github.com/nlemoine/timber-weblink-extension/commit/76dea5822d317b37ee58c5cc6c102e4f9d06f8a7))
* send each identical link once ([49f71f6](https://github.com/nlemoine/timber-weblink-extension/commit/49f71f6af576e0d0fb2e41390138187dbe6f2668))
* send the links of every template in one Link header ([62a4bc9](https://github.com/nlemoine/timber-weblink-extension/commit/62a4bc965d217bde06512aa934a8a1cbc2af0c7f))
* throw on an empty URI ([bd05eac](https://github.com/nlemoine/timber-weblink-extension/commit/bd05eacf7ae3f67797cc2cf1620d006e819f4cec))


### Miscellaneous Chores

* align the Composer description and keywords with the repository ([fd15dc5](https://github.com/nlemoine/timber-weblink-extension/commit/fd15dc5bd9ee1a3aeab6ca590eda18476227fef4))


### Code Refactoring

* move the extension to the n5s\TimberWebLink namespace ([d7d6392](https://github.com/nlemoine/timber-weblink-extension/commit/d7d63921ab78ae3c3c0bc3a2824a848e54742b1e))


### Build System

* rename the Composer package to n5s/timber-weblink-extension ([7c0c764](https://github.com/nlemoine/timber-weblink-extension/commit/7c0c764c503436017051d0944be7ac061e9abc4e))
