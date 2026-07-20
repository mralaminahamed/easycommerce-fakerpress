# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### JavaScript/TypeScript
```bash
yarn build          # production build → build/
yarn start          # webpack dev server (watch)
```

### PHP
```bash
composer test                                        # run all PHPUnit tests
phpunit tests/php/src/SpecificTest.php               # single test file
phpunit --filter TestClassName::testMethodName       # single test method
composer test:coverage                               # HTML coverage report
composer phpcs                                       # lint (WPCS)
composer phpcbf                                      # auto-fix
composer phpstan                                     # static analysis
```

### Release
```bash
composer release    # lint + analyse + build + makepot + zip into release/
```

## Architecture

### Entry points
- **PHP**: `easycommerce-fakerpress.php` — defines constants, loads Composer autoloader, calls `EasyCommerce_FakerPress::get_instance()->init()`
- **JS**: `src/index.tsx` → built to `build/app.js` (mounted on `#easycommerce-fakerpress-root`)

### PHP layer
- `class-easycommerce-fakerpress.php` — singleton orchestrator; registers hooks, enqueues assets, registers REST routes, boots MCP server
- `includes/` — PSR-4 namespace root `EasyCommerceFakerPress\`
  - `Abstracts/Generator.php` — base for all generators (FakerPHP wiring, Template Method pattern, logging, batch processing)
  - `Abstracts/Controller.php` — base for all REST controllers (extends `WP_REST_Controller`, namespace `easycommerce-fakerpress/v1`)
  - `Generators/` — 14 concrete generators (Product, Customer, Order, Coupon, Product_Variation, Shipping_Plan, Tax_Class, Transaction, Cart_Session, Location, Product_Review, Attribute, Refund, Log)
  - `Controllers/` — matching REST controllers, one per generator; REST base is the plural resource name
  - `MCP/MCP_Server.php` + `MCP/Abilities/` — MCP tool registration (requires mcp-adapter plugin)

### Data flow
```
React component → REST POST /easycommerce-fakerpress/v1/{resource}/generate
  → Controller::generate_items() validates params
  → Generator::generate() uses FakerPHP + EasyCommerce models
  → returns { id, message, metadata }
```

### React/JS layer
- `src/admin/components/App.tsx` — React Router v7 root
- `src/admin/components/Pages/` — `HomePage`, `GeneratorPage`, `RootLayout`
- `src/admin/components/generator/` — shared schema-driven UI (`ConfigColumn.tsx`, `RunBar.tsx`, `PreviewTable.tsx`); there is **no** per-generator component file
- `src/admin/lib/generators.ts` — single config array describing all 14 generators (drives the UI)
- `src/admin/components/ui/` — Radix UI primitives wrapped with Tailwind + CVA
- `@` path alias resolves to `src/`
- WordPress admin colors injected as CSS vars (`--wp-admin-primary` etc.) and passed via `window.easycommerceFakerpressApi`

### Key conventions
- PHP: WordPress coding standards, camelCase methods, snake_case file names
- JS: TypeScript strict, functional components + hooks only, Tailwind v4 (`@theme`/`@utility` directives)
- REST: plural endpoint bases (`products`, `orders`); params validated via JSON Schema in controller `get_params()`
- Adding a generator requires: new `includes/Generators/Foo.php`, new `includes/Controllers/Foo.php`, a config entry in `src/admin/lib/generators.ts`, and registering the controller in `class-easycommerce-fakerpress.php::register_rest_routes()`
- Plugin requires EasyCommerce to be active; all REST routes and the admin menu are gated behind `check_dependencies()`

## Documentation conventions

Three doc files, three audiences. Do not duplicate content between them — duplication is what caused every drift bug this repo has had.

- `CHANGELOG.md` — **the complete version history, and the single source of truth.** [Keep a Changelog](https://keepachangelog.com/en/2.0.0/) format: newest first, `YYYY-MM-DD` dates, and only the six headings `Added` / `Changed` / `Deprecated` / `Removed` / `Fixed` / `Security`. Mark breaking changes as `- **Breaking:** …` inside the relevant heading. Add entries under `## [Unreleased]` as work lands; on release, rename that to the version with its date and add the compare link at the bottom of the file.
- `readme.txt` — WordPress.org listing. Its `== Changelog ==` carries **only the four most recent releases**, above an absolute link to `CHANGELOG.md` on GitHub. Never a relative link: readme.txt renders on wordpress.org, so a relative path 404s. `== Upgrade Notice ==` must stay here — core reads it for the update prompt — and each entry is capped at 300 characters.
- `README.md` — GitHub, developer-facing. Links to `CHANGELOG.md`; never restates release history.

Release dates come from the git tag, not from memory. `readme.txt` dates have drifted from their tags before.

`readme.txt` is **not** GitHub-flavored markdown. It is PHP Markdown Extra plus a `wp_kses` allowlist. `[text](url)`, `**bold**`, `` `code` ``, and lists work. Tables, images, ``` fences, and `#` headings are stripped. Version entries use `= 1.2.3 =`, which renders as `<h4>`. The changelog section truncates past 5,000 words.

When a change adds an outbound HTTP request, it must be disclosed in `== External services ==` in `readme.txt` and in the External Services table in `README.md`. WordPress.org requires it.

Verify `readme.txt` after editing: https://wordpress.org/plugins/developers/readme-validator/
