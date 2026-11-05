# Contributing

## Requirements

- PHP 8.2+
- [Composer](https://getcomposer.org/)

## Setup

Clone the repository

```bash
git clone git@github.com:nlemoine/timber-weblink-extension.git
```

Install dependencies

```bash
composer install
```

## Tests

Tests use [Mantle Testkit](https://mantle.alley.com/docs/testing) and run against WordPress and Timber with SQLite, so no database is needed. WordPress is downloaded to `tmp/` on the first run, with the `php` binary found in your `PATH`: it must be PHP 8.2+ too. The `unit` suite tests the Twig extension alone, the `integration` suite renders templates through Timber.

The test bootstrap loads Composer's autoloader before WordPress, as Bedrock does, so the integration tests also check that the hooks are registered early.

PHP does not record headers on the command line, so `LinkHeaderTest` makes front-end requests in a separate PHP process with [n5s/http-cli](https://github.com/nlemoine/http-cli), which does. The front controller in `tests/fixtures/front` loads the WordPress installed by Mantle on a copy of its database, and renders the template named in the request. When PHPUnit collects code coverage with PCOV, the front controller collects it in that process too, and the test merges it into PHPUnit's coverage.

```bash
composer test
composer test:coverage
```

## Coding standards

PHP code follows the [Syde](https://github.com/inpsyde/phpcs) coding standards. PHPCompatibility also checks that the library runs on PHP 8.2.

```bash
composer cs      # check
composer cs:fix  # fix what can be fixed automatically
```

## Static analysis

```bash
composer phpstan
composer rector      # check
composer rector:fix  # apply
```

## Commits

Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/). [Release Please](https://github.com/googleapis/release-please) uses them to generate the changelog and releases, so use `feat:` and `fix:` for user-facing changes.
