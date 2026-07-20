=== EasyCommerce FakerPress ===
Contributors: mralaminahamed
Tags: test data, dummy data, sample data, demo content, faker
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Requires Plugins: easycommerce
Stable tag: 2.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fill an EasyCommerce store with realistic test data. Generate dummy products, orders, customers and coupons in seconds, with a live preview.

== Description ==

EasyCommerce FakerPress generates realistic test data for EasyCommerce stores. It creates dummy products, orders, customers, coupons and ten other record types on demand, so you can build, test and demo a store that looks real without typing a single product by hand.

It is a developer tool for local, staging and demo sites. It is not meant for production stores.

Point it at a generator, set how many records you want, and click Generate. A live preview shows exactly what the run will produce before anything is written to your database.

**What it generates**

Fourteen generators, covering the records an EasyCommerce store actually holds:

* Products — pricing, categories, inventory, attributes and variations
* Customers — profiles with addresses, demographics and purchase history
* Orders — complete order histories with payments, shipping and tax
* Coupons — discount codes with rules, usage limits and restrictions
* Product Variations — variable attributes, price variance and stock settings
* Shipping Plans — methods, zones and rate tables
* Tax Classes — tax rules by region and product type
* Transactions — payment records across multiple gateways and statuses
* Cart Sessions — abandoned cart scenarios and session data
* Attributes — Text, Color and Image attribute types for variations and filtering
* Refunds — refunds against existing completed or processing orders
* Logs — activity entries for orders, products, customers and system events
* Locations — countries, states and cities
* Product Reviews — ratings linked to real products and customers

**Why the data holds together**

Generated records are created through EasyCommerce's own models rather than by writing rows directly to the database. Validation, relationships and inventory changes all run exactly as they would for a real order. That means orders link to genuine customers and products, refunds attach to orders that can actually be refunded, and reviews belong to products that exist.

FakerPHP supplies the names, addresses and text, layered with commerce-specific logic for pricing, geography and customer lifecycle. Optional sample data sync adds locale-specific product names and addresses for 75+ locales.

**Built for repeatable testing**

* Live preview — see real generated rows before committing anything
* Seeds — reuse a seed to reproduce an identical dataset on demand
* Batch queue — line up several generators and run them in sequence
* Run history — per-generator log with all-time stats on the dashboard
* Locales — generate region-appropriate data for 75+ locales
* Light and dark themes, five accent palettes, keyboard-driven command palette

**For developers**

* 15+ filters and actions for customising data before and after creation
* 14 REST controllers under the `easycommerce-fakerpress/v1` namespace
* Abstract generator classes you can extend with your own generators
* 131 Playwright end-to-end tests

Key hooks:

* `easycommerce_fakerpress_*_data_before_create` — change generated data before it is created
* `easycommerce_fakerpress_after_*_created` — react once an item exists
* `easycommerce_fakerpress_*_generation_result` — reshape what a run returns
* `easycommerce_fakerpress_rest_response` — filter REST API responses

**Important:** this plugin writes large volumes of fake data into your store. Use it on development or staging sites only, and back up your database before generating large datasets.

== Installation ==

= Automatic =
1. Go to Plugins > Add New in your WordPress admin.
2. Search for "EasyCommerce FakerPress".
3. Click Install Now, then Activate.
4. Open the EC FakerPress menu item.

= Manual =
1. Download the plugin ZIP file.
2. Upload it to /wp-content/plugins/easycommerce-fakerpress/.
3. Activate via the Plugins screen.
4. Open the EC FakerPress menu item.

= Requirements =
* WordPress 6.6+
* PHP 7.4+ (8.0+ recommended)
* [EasyCommerce](https://wordpress.org/plugins/easycommerce/) installed and active
* 256MB memory minimum (512MB recommended for large datasets)

== Frequently Asked Questions ==

= How do I generate dummy products for an EasyCommerce store? =

Open EC FakerPress in your WordPress admin, choose the Products generator, set how many products you want, and click Generate. The preview table shows sample rows before you commit. The same flow applies to every other generator.

= Does this work with WooCommerce? =

No. This plugin generates data for [EasyCommerce](https://wordpress.org/plugins/easycommerce/) only, and WordPress will not let it activate unless EasyCommerce is installed and active. It does not create WooCommerce products, orders or customers.

= Is it safe to run on a live site? =

No. Use it on development, staging or demo sites only. It writes large volumes of fictional records directly into your store, and there is no one-click undo. Back up your database before any large run.

= How do I delete the test data afterwards? =

Use the WordPress or EasyCommerce bulk deletion tools. For large datasets, targeted database queries against a backup are faster. The plugin does not currently ship a bulk delete feature, which is the main reason to keep it off production.

= Can I generate the same data twice? =

Yes. Set a seed value in the run bar and the same seed always produces the same dataset. This is what makes generated data usable in automated tests and reproducible bug reports.

= How realistic is the generated data? =

Names, addresses and text come from FakerPHP, combined with commerce-specific logic for pricing strategies, geographic distribution and customer lifecycle patterns. Turning on sample data sync adds locale-specific product names and reference data for 75+ locales.

= Can orders be linked to real customers and products? =

Yes. Orders attach to existing customers and products and adjust inventory as they are created. Refunds require existing completed or processing orders, and product reviews link to products and customers that already exist.

= Can I change the data before it is saved? =

Yes. The plugin exposes 15+ filters and actions. Use `easycommerce_fakerpress_*_data_before_create` to alter values before creation, or extend the abstract generator classes to add a generator of your own.

= What is sample data sync? =

An optional download of locale-specific reference data — product names, customer tags, addresses — from the companion GitHub repository. It makes generated content read more naturally in non-English locales. It runs only when you ask for it, and sends no data about your site.

= Where is the run history stored? =

In your browser's localStorage, not your database. Clearing it has no effect on generated records. You can clear it from the Danger Zone on the Settings page.

== Screenshots ==

1. Dashboard: Stat cards with sparklines, a recent-activity feed, and a generator grid grouped by category — all driven by real run history.
2. Generator page: Two-column layout with schema-driven config controls on the left and a live preview table on the right, plus a sticky run bar.
3. Live preview: Real faker sample rows that refresh as you change settings, with a Shuffle button to re-roll the seed (no data is persisted).
4. Command palette: Press Cmd/Ctrl+K to quick-jump to any generator or page.
5. Batch queue: Queue multiple generators and run them sequentially from the batch tray with live progress.
6. Tweaks panel: Switch theme (light/dark), accent palette, and density — applied live and persisted.
7. Dark mode: The full admin in dark theme, scoped to the plugin so WordPress chrome stays untouched.
8. Settings page: Generation defaults, run-history limit, sample-data sync status, About card, and Danger Zone on the new card system.
9. Our Plugins page: Live WordPress.org plugin cards with ratings, active-install counts, and direct links.
10. Product Reviews Generator: Target a specific product by ID, configure count, locale, seed, and metadata options.
11. Locations Generator: Region chip selects, max countries, state and city generation toggles, cities-per-state range, and coordinate generation.

== Changelog ==

The four most recent releases are listed below. For the complete version history, see the [full changelog on GitHub](https://github.com/mralaminahamed/easycommerce-fakerpress/blob/trunk/CHANGELOG.md).

= 2.3.0 - July 20, 2026 =
* Order generation no longer fails for customers whose address was saved at checkout — a stored address came back in a shape the generator rejected, and the whole batch stopped instead of skipping one item
* Generated orders are now spread over a configurable date range instead of all landing on today, so the EasyCommerce date-range reports have data to show
* Generated orders carry the address, tax and shipping meta EasyCommerce reads, so they render complete in the admin
* Aligned generated statuses, attributes, tax classes, coupon rules and shipping tiers with the current EasyCommerce database schema
* A run where every item failed now reports why, instead of claiming success with zero items created
* Headings follow the active theme, fixing unreadable page titles in dark mode
* Live preview shows up to 25 rows and scrolls within its own card
* New brand mark, refreshed WordPress.org icon and banners
* Minimum WordPress version corrected to 6.6, and compatibility declared with WordPress 7.0
* Removed a deprecation notice raised for every generated field

= 2.2.0 - June 11, 2026 =
* Complete admin UI redesign on a new design-token system — light/dark themes, 5 accent palettes, and comfortable/compact density, all scoped so WordPress chrome is never restyled
* New dashboard with stat cards, sparklines and a recent-activity feed driven by real run history
* Live preview — real faker rows rendered before anything is written, re-rollable with Shuffle
* Command palette (Cmd/Ctrl+K), batch queue, and a redesigned generator page with a sticky run bar
* Refreshed brand assets and a recolorable admin menu icon

= 2.1.0 - April 26, 2026 =
* Three new generators — Attributes, Refunds and Logs, bringing the total to 14
* Run history with per-generator logs and all-time dashboard stats
* Settings page — default count, locale, seed, metadata toggle, and run-history limit
* Sample data sync for locale-specific reference data across 75+ locales
* Playwright end-to-end suite — 131 tests across all generators and field types
* Component architecture reworked; parameter config centralised in generators.ts

= 2.0.4 - February 26, 2026 =
* Shared TypeScript type definitions extracted into their own module
* Webpack configuration updated with a TerserPlugin config for WordPress compatibility
* Build output renamed from index to app, with the asset path updated to match
* Dependencies updated, including PHPStan 2.1.40

== Upgrade Notice ==

= 2.3.0 =
Fixes order generation failing for customers with a saved address, and spreads generated orders over a date range so the EasyCommerce reports show data. Generators realigned with the current EasyCommerce schema. Minimum WordPress is now 6.6.

= 2.2.0 =
Complete admin UI redesign with a new design-token system, dashboard, live preview, command palette, batch queue, and dark mode. No database migrations required. REST API, generator parameters, and custom hooks are unchanged.

= 2.1.0 =
Major feature release. Complete admin UI redesign, 3 new generators, Settings page, sample data sync, Our Plugins page, and Playwright e2e suite. No database migrations required. REST API and custom hooks are unchanged.

= 2.0.0 =
Breaking change: parameter schemas updated for all generators. Review custom REST API integrations and hooks before upgrading. Test in staging first.

== External services ==

This plugin connects to two external services. Neither is contacted on activation, and no personal or store data is ever transmitted.

**1. GitHub — sample data repository**

The Settings page offers an optional "Sample Data Sync" action that downloads locale-specific reference data (product names, addresses, customer tags for 75+ locales) used to make generated content more realistic.

Service: GitHub
Endpoint: [https://github.com/mralaminahamed/easycommerce-fakerpress-sample-data/archive/refs/heads/trunk.zip](https://github.com/mralaminahamed/easycommerce-fakerpress-sample-data/archive/refs/heads/trunk.zip)
When data is sent: Only when an administrator clicks "Sync Sample Data" on the plugin Settings page, or when a generator requires sample data that has not been downloaded yet.
Data sent: An unauthenticated HTTP GET request. No site, user, or store data is included — only the request itself (and the IP address and user agent inherent to any HTTP request).
Terms of Service: [https://docs.github.com/en/site-policy/github-terms/github-terms-of-service](https://docs.github.com/en/site-policy/github-terms/github-terms-of-service)
Privacy Policy: [https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement](https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement)

**2. WordPress.org — plugin directory API**

The "Our Plugins" admin page lists the plugin author's other WordPress.org plugins with live ratings and install counts.

Service: WordPress.org Plugin Directory API
Endpoint: [https://api.wordpress.org/plugins/info/1.2/](https://api.wordpress.org/plugins/info/1.2/)
When data is sent: Only when an administrator opens the "Our Plugins" page in the plugin admin. The request is made by the browser.
Data sent: A query for plugins by the author "mralaminahamed". No site, user, or store data is included — only the request itself (and the IP address and user agent inherent to any HTTP request).
Terms of Service: [https://wordpress.org/about/](https://wordpress.org/about/)
Privacy Policy: [https://wordpress.org/about/privacy/](https://wordpress.org/about/privacy/)

== Source code ==

The minified JavaScript and CSS in `build/` is compiled from the TypeScript and CSS sources in `src/`, which are not included in the distributed plugin package. The complete, human-readable source is public:

[github.com/mralaminahamed/easycommerce-fakerpress](https://github.com/mralaminahamed/easycommerce-fakerpress)

Build steps:

`composer install`
`yarn install`
`yarn build`

Build tooling: webpack (via @wordpress/scripts), TypeScript, and Tailwind CSS. Configuration files (`webpack.config.js`, `tsconfig.json`, `postcss.config.js`) are in the repository root.

== Other Notes ==

**Privacy**

All generated data is stored in your own WordPress database and is never transmitted anywhere. Generated content is fictional and does not represent real individuals or transactions. The plugin does not collect analytics, does not phone home, and does not send any site, user, or store data to a third party.

The plugin makes two outbound requests, both administrator-initiated and both carrying no site data — see the "External services" section for the full disclosure.

**Contributing**

Development happens on [GitHub](https://github.com/mralaminahamed/easycommerce-fakerpress). Report bugs and request features on the [issue tracker](https://github.com/mralaminahamed/easycommerce-fakerpress/issues), and read the [contributing guide](https://github.com/mralaminahamed/easycommerce-fakerpress/blob/trunk/CONTRIBUTING.md) before opening a pull request — it covers local setup, coding standards, and the checks that run on every change.

Found a security issue? Please follow the [security policy](https://github.com/mralaminahamed/easycommerce-fakerpress/blob/trunk/SECURITY.md) and report it privately rather than in a public issue.

**Support**

* [WordPress.org support forum](https://wordpress.org/support/plugin/easycommerce-fakerpress/) — questions and help with using the plugin
* [GitHub issue tracker](https://github.com/mralaminahamed/easycommerce-fakerpress/issues) — bug reports and feature requests
* [Full changelog](https://github.com/mralaminahamed/easycommerce-fakerpress/blob/trunk/CHANGELOG.md) — the complete version history
