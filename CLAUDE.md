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
  - `Generators/` — 11 concrete generators (Product, Customer, Order, Coupon, Product_Variation, Shipping_Plan, Tax_Class, Transaction, Cart_Session, Location, Product_Review)
  - `Controllers/` — matching REST controllers, one per generator; REST base is the plural resource name
  - `MCP/MCP_Server.php` + `MCP/Abilities/` — MCP tool registration (requires mcp-adapter plugin)

### Data flow
```
React component → REST POST /easycommerce-fakerpress/v1/{resource}/generate
  → Controller::generate() validates params
  → Generator::generate() uses FakerPHP + EasyCommerce models
  → returns { id, message, metadata }
```

### React/JS layer
- `src/admin/components/App.tsx` — React Router v7 root
- `src/admin/components/Pages/` — `HomePage`, `GeneratorPage`, `RootLayout`
- `src/admin/components/Generators/` — one component per generator (extends `GeneratorBase.tsx`)
- `src/admin/components/ui/` — Radix UI primitives wrapped with Tailwind + CVA
- `@` path alias resolves to `src/`
- WordPress admin colors injected as CSS vars (`--wp-admin-primary` etc.) and passed via `window.easycommerceFakerpressApi`

### Key conventions
- PHP: WordPress coding standards, camelCase methods, snake_case file names
- JS: TypeScript strict, functional components + hooks only, Tailwind v4 (`@theme`/`@utility` directives)
- REST: plural endpoint bases (`products`, `orders`); params validated via JSON Schema in controller `get_params()`
- Adding a generator requires: new `Generators/Foo.php`, new `Controllers/Foo.php`, new `src/admin/components/Generators/FooGenerator.tsx`, register controller in `class-easycommerce-fakerpress.php::register_rest_routes()`
- Plugin requires EasyCommerce to be active; all REST routes and the admin menu are gated behind `check_dependencies()`
