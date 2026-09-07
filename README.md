<div align="center">

<img src=".wordpress-org/icon-256x256.png" alt="EasyCommerce FakerPress icon" width="96" height="96">

# EasyCommerce FakerPress — Developer Guide

**Realistic test data for an EasyCommerce store — fourteen generators for products, customers, orders, coupons and the rest, from the admin, REST, or an MCP client.**

[![Version](https://img.shields.io/badge/version-2.4.0-21759b.svg)](https://github.com/mralaminahamed/easycommerce-fakerpress)
[![WordPress](https://img.shields.io/badge/WordPress-6.6%2B-21759b.svg?logo=wordpress&logoColor=white)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%207-brightgreen.svg)](https://phpstan.org/)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](LICENSE)

</div>

> This is the **contributor / technical** guide. For the public plugin listing — features, screenshots, changelog, upgrade notices — see [`readme.txt`](readme.txt).

| Requirement      | Minimum | Tested up to |
|------------------|---------|--------------|
| **WordPress**    | 6.6     | 7.1          |
| **PHP**          | 7.4     | —            |
| **EasyCommerce** | —       | required     |

Current version **2.4.0** · License **GPL-2.0-or-later** · Tooling **Yarn** + Composer · Delivered free on WordPress.org

---

## What it is

Developing against an empty store tells you very little. Real behaviour — pagination, totals,
refunds against real orders, coupons that have actually been redeemed — only shows up once
there is data with the right shape.

This plugin generates that data for **EasyCommerce**: fourteen generators, each producing
records the store treats as genuine rather than rows pushed straight into the database. It is
built for development, testing and demos, and it is deliberately capable of removing what it
made.

---

## What ships

| Surface        | Provided                                                                       |
|----------------|---------------------------------------------------------------------------------|
| **Generators** | 14 — products, variations, reviews, attributes, customers, orders, refunds, coupons, transactions, cart sessions, locations, shipping plans, tax classes, logs |
| **REST**       | `easycommerce-fakerpress/v1` — one controller per generator                     |
| **MCP**        | Abilities exposed to an MCP client, so an agent can seed a store                |
| **Admin**      | React screen driving the same REST endpoints                                     |

---

## Architecture

### PHP — `includes/` (PSR-4 `EasyCommerceFakerPress\`)

| Dir            | Responsibility                                                       |
|----------------|-----------------------------------------------------------------------|
| `Generators/`  | One generator per record type — what to create and how it should look |
| `Controllers/` | REST controllers, one per generator, plus plugin state                |
| `Abstracts/`   | Shared base classes the generators and controllers extend             |
| `MCP/`         | Abilities and server wiring for MCP clients                           |

Generators and controllers are deliberately parallel: adding a record type means one generator
and one controller, and the admin screen picks it up without further wiring. The abstracts are
where the shared shape lives, so a new generator inherits validation and batching rather than
reimplementing them.

### JavaScript — `src/`

Compiled to `build/` by `@wordpress/scripts`. Never edit `build/`; it is what WordPress loads.

### Repo map

```
easycommerce-fakerpress.php   Bootstrap: constants, autoloader guard, plugin instance
includes/                     PHP (PSR-4 EasyCommerceFakerPress\)
src/                          Admin screen sources
build/                        Compiled assets — generated, do not edit
tests/php/                    PHPUnit
tests/e2e/                    Playwright
docs/                         Longer-form documentation
.wordpress-org/               Directory assets: icon, banners, screenshots
```

---

## Getting started

```bash
composer install     # PHP dependencies + dev tooling
yarn install         # JS dependencies
yarn build           # compile the admin screen
```

The plugin will not run without `vendor/autoload.php`. If it is missing, it says so in the
admin rather than activating and doing nothing.

---

## Build

```bash
yarn build           # production build
yarn start           # watch mode
```

---

## Testing

```bash
composer test              # PHPUnit
composer test:coverage     # with coverage
yarn test:e2e              # Playwright
yarn test:e2e:ui           # Playwright, headed
```

> `phpunit.xml.dist` points `WP_PATH` at a specific local WordPress install. On any other
> machine the suite cannot bootstrap until that path is corrected or overridden — a failure
> there is configuration, not code.

---

## Code quality

```bash
composer phpcs                 # WordPress Coding Standards
composer phpcbf                # auto-fix
composer phpstan               # static analysis, level 7
composer analyse               # phpcs + phpstan
composer phpcs:plugin-review   # the stricter directory-review ruleset
```

---

## Internationalization

```bash
composer makepot
```

Text domain `easycommerce-fakerpress`. Translations live in `languages/`.

---

## Release

```bash
composer release
```

---

## Links

- [WordPress.org listing](https://wordpress.org/plugins/easycommerce-fakerpress/)
- [Public readme](readme.txt) — features, screenshots, changelog
- [`docs/`](docs/) — longer-form documentation

---

## Contributing · Security · License

Issues and pull requests are welcome. Please run `composer analyse` and `composer test`
before opening one.

This plugin writes and deletes store data by design. Report security issues privately rather
than in a public issue.

GPL-2.0-or-later. See [`LICENSE`](LICENSE).
