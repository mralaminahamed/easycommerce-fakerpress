# Contributing to EasyCommerce FakerPress

Thanks for taking the time to contribute. This guide covers the setup, conventions, and checks this project expects.

## Reporting bugs and requesting features

Open an issue on the [issue tracker](https://github.com/mralaminahamed/easycommerce-fakerpress/issues). For bugs, include the WordPress version, PHP version, EasyCommerce version, plugin version, the generator involved, and the steps that reproduce the problem.

> [!IMPORTANT]
> Do not report security vulnerabilities through public issues. Follow the [security policy](SECURITY.md) instead.

## Local setup

Requires PHP 7.4+, Composer, Node.js 20+, Yarn, and a WordPress site with [EasyCommerce](https://wordpress.org/plugins/easycommerce/) active.

```bash
git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git
cd easycommerce-fakerpress
composer install
yarn install
yarn build      # or: yarn start, for webpack watch mode
```

## Before opening a pull request

CI runs PHPCS and PHPStan on every push, so run both locally first:

```bash
composer phpcs      # WordPress coding standards (composer phpcbf auto-fixes)
composer phpstan    # Static analysis, level 8
composer test       # PHPUnit
yarn test:e2e       # Playwright end-to-end suite
```

## Coding conventions

**PHP** follows WordPress Coding Standards with PSR-4 autoloading under the `EasyCommerceFakerPress\` namespace. Methods are camelCase, file names are snake_case. All code must pass PHPStan level 8.

**TypeScript** is strict mode, functional components and hooks only, styled with Tailwind v4. The `@` path alias resolves to `src/`.

**REST endpoints** use plural resource bases (`products`, `orders`) under the `easycommerce-fakerpress/v1` namespace, with parameters validated via JSON Schema in each controller's `get_params()`.

## Adding a generator

Four things are required, and a generator is incomplete without all of them:

1. `includes/Generators/Foo.php` — extends `Abstracts\Generator`
2. `includes/Controllers/Foo.php` — extends `Abstracts\Controller`
3. A config entry in `src/admin/lib/generators.ts`, which drives the admin UI
4. Controller registration in `class-easycommerce-fakerpress.php::register_rest_routes()`

Add PHPUnit coverage for the generator and Playwright coverage for its UI.

## Commits

This project uses [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/):

```
type(scope): short description
```

Types in use: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `chore`, `ci`, `build`. Use a specific scope, such as `fix(generators):` or `docs(readme):`. See [`.github/git-commit-instructions.md`](.github/git-commit-instructions.md) for the full convention.

Branch names follow the same shape as the commit type — for example `fix/generator-status-enums` or `docs/add-screenshots`.

## Documentation

`README.md` is for developers and contributors; `readme.txt` is the WordPress.org listing and holds the canonical changelog. When a change affects end users, update `readme.txt`. Keep facts such as version numbers and compatibility in a single place rather than duplicating them across both files.

If a change adds an outbound HTTP request, it must be disclosed in the `== External services ==` section of `readme.txt` and in the External Services table in `README.md`. WordPress.org requires this.

## License

Contributions are licensed under [GPL-2.0-or-later](LICENSE), matching the plugin.
