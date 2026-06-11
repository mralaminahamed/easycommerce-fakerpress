=== EasyCommerce FakerPress ===
Contributors: mralaminahamed
Tags: ecommerce, faker, data-generation, testing, development
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.2.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate realistic EasyCommerce test data with 14 generators, modern SaaS UI, run history, settings, and sample data sync from GitHub.

== Description ==

EasyCommerce FakerPress populates your EasyCommerce store with realistic fake data for development, testing, and demos. Choose a generator, configure the parameters, and click Generate.

**Core Generators**

* Products — Products with pricing, categories, inventory, attributes, and variations
* Customers — Customer profiles with addresses, demographics, and purchase history
* Orders — Complete order histories with payments, shipping, and tax calculations
* Coupons — Discount codes with rules, usage limits, and restrictions

**Advanced Generators**

* Product Variations — Variable product attributes, price variance, and stock settings
* Shipping Plans — Shipping methods, zones, and rate tables
* Tax Classes — Tax rules for different regions and product types
* Transactions — Payment records with multiple gateways and status distribution
* Cart Sessions — Shopping cart abandonment scenarios and session data
* Attributes — Product attribute types (Text, Color, Image) for variations and filtering
* Refunds — Refund records against existing completed or processing orders
* Logs — Activity log entries for orders, products, customers, and system events

**Enhanced Generators**

* Locations — Geographic data including countries, states, and cities
* Product Reviews — Product reviews with ratings linked to existing products and customers

**Key Features**

* Run History — Per-generator run log in browser localStorage with all-time stats on the dashboard
* Settings Page — Default count, locale, seed, metadata preference, and configurable run history limit
* Sample Data Sync — One-click download of locale-specific reference data (75+ locales) from the companion GitHub repository
* Our Plugins Page — Browse the author's other WordPress.org plugins with live data
* Hook System — 15+ filters and actions for complete data customization and workflow integration
* REST API — 14 REST controllers under the easycommerce-fakerpress/v1 namespace
* Playwright E2E Suite — 131 automated tests covering all generators, field types, and UI interactions

**Extensibility**

The plugin exposes a full hook system for developers:

* `easycommerce_fakerpress_*_data_before_create` — Modify generated data before creation (10+ filters)
* `easycommerce_fakerpress_*_generation_result` — Customize returned generation results
* `easycommerce_fakerpress_after_*_created` — Hook into the post-creation workflow
* `easycommerce_fakerpress_rest_response` — Filter REST API responses

**Important**: Use only in development or staging environments. Back up your database before generating large datasets.

== Installation ==

= Automatic =
1. Go to Plugins > Add New in your WordPress admin.
2. Search for "EasyCommerce FakerPress".
3. Click Install Now, then Activate.
4. Access the plugin via the EC FakerPress menu item.

= Manual =
1. Download the plugin ZIP file.
2. Upload it to /wp-content/plugins/easycommerce-fakerpress/.
3. Run composer install in the plugin directory.
4. Activate via the Plugins screen.
5. Access via the EC FakerPress menu.

= Requirements =
* WordPress 5.0+
* PHP 7.4+ (8.0+ recommended)
* EasyCommerce plugin (must be active)
* 256MB memory minimum (512MB recommended for large datasets)

== Frequently Asked Questions ==

= What is EasyCommerce FakerPress? =
A WordPress plugin that generates realistic fake data for EasyCommerce stores. It is intended for development, QA testing, and demo environments — not production sites.

= How does the EasyCommerce integration work? =
The plugin uses native EasyCommerce models (Product, Customer, Order, etc.) to create data. This ensures validation, relationship integrity, and compatibility with future EasyCommerce updates. Direct database inserts are avoided.

= Can I generate data with relationships? =
Yes. Orders can be linked to existing customers and products with inventory adjustments. Refunds require existing completed or processing orders. Product reviews are linked to existing products and customers.

= How realistic is the generated data? =
Data uses FakerPHP for authentic names, addresses, and content, combined with e-commerce-specific logic such as pricing strategies, geographic distribution, and customer lifecycle patterns. Sample data sync adds locale-specific product names, addresses, and other reference data.

= Is it safe for production? =
No. Use only in development or staging environments. Always back up your database before generating data.

= Can I customize the generated data? =
Yes. Use the hook system to filter data before creation, modify results, or trigger custom actions after items are created. Abstract generator classes can also be extended for custom generators.

= What is sample data sync? =
The Settings page includes a sync feature that downloads locale-specific reference data (product names, customer tags, addresses, and more) from the companion GitHub repository. This improves the realism of generated content across 75+ locales.

= How do I remove generated data? =
Use WordPress or EasyCommerce bulk deletion tools. For large-scale cleanup, targeted database queries (with a backup) may be more efficient. The plugin does not currently include a bulk delete feature.

= What happens to the run history? =
Run history is stored in browser localStorage. It does not affect your database. You can clear it from the Settings page under Danger Zone.

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

= 2.2.0 - June 11, 2026 =
* Complete admin UI redesign — Linear/Vercel-style SaaS interface built on a new design-token system (self-hosted Geist fonts, light/dark themes, 5 accent palettes, comfortable/compact density), all scoped to the plugin so WordPress chrome is never restyled
* New dashboard — Stat cards with sparklines, recent-activity feed, and a generator grid grouped by category, all driven by real run history
* Redesigned generator page — Two-column config + live preview layout with a sticky run bar (count stepper, seed, metadata, add-to-batch, generate)
* Live preview — Read-only REST preview route returns real faker rows (no persistence); the preview table refreshes as you change settings and re-rolls on Shuffle
* Command palette (Cmd/Ctrl+K) — Quick-jump to any generator or page
* Batch queue — Queue multiple generators and run them sequentially from the batch tray, with progress and toasts
* Tweaks panel — Live theme, accent, and density controls persisted to the browser
* Redesigned Settings and Our Plugins pages on the new card system
* Refreshed brand assets — New logo, recolorable WP admin menu icon, and updated WordPress.org icon and banners

= 2.1.0 - April 26, 2026 =
* Complete admin UI redesign — Modern SaaS style with clean white, blue and indigo accents
* New dashboard — Stats bar with 4 live stat cards and generator grid grouped by category with Popular badges
* New generator page layout — Sticky top-bar, collapsible sidebar with category nav and per-generator run history, two-panel params and action layout
* New component architecture — Replaced 757-line GeneratorBase monolith with focused ParamsPanel, ActionPanel, and GeneratorSidebar components; parameter config centralized in generators.ts
* Run history — Per-generator run log in localStorage (configurable max, FIFO); recent runs in sidebar; all-time stats on dashboard
* 3 new generators — Attributes, Refunds, Logs (total now 14)
* Settings page — Default count, locale, seed, metadata toggle; configurable max runs per generator; sample data sync; About card; Reset Settings
* Sample data sync — Download or force re-sync locale-specific reference data from the companion GitHub repository via REST endpoints
* Our Plugins page — Fetches and displays the author's other WordPress.org plugins with live data
* Global sticky nav — Generators, Settings, Our Plugins links with correct active-state matching
* Playwright e2e suite — 131 automated tests covering the home page, generator page layout, ActionPanel interactions, all 6 field types, and all 14 generators
* Bug fixes — Category matching in all locales; single ActionPanel DOM instance; scoped focus styles; RangeField error colour; nested route active state

= 2.0.3 - January 15, 2026 =
* New Product Review generator with weighted rating distribution and verified purchase support
* WordPress comments integration for review storage
* Order generator data structure fix to match EasyCommerce Order model
* Proper order notes creation using the Order_Notes model
* Controller pattern and API schema consistency improvements

= 2.0.2 - January 15, 2026 =
* Added "Get Started" plugin action link
* Upgraded to Tailwind CSS v4
* Fixed visual inconsistencies in success messages and navigation
* Build system and dependency compatibility updates

= 2.0.1 - November 13, 2025 =
* Minor bug fixes and code quality improvements

= 2.0.0 - November 11, 2025 =
* Complete parameter schema alignment for all 10 generators
* Full TypeScript migration with proper interfaces and validation
* Corrected array vs string mismatches and naming inconsistencies across all REST controllers
* Breaking change: parameter schemas updated — review custom integrations before upgrading

= 1.0.4 - November 10, 2025 =
* Added 15+ filter and action hooks for complete data customization
* REST response filtering for API extensibility

= 1.0.2 - October 29, 2025 =
* Performance improvements for memory usage and processing speed
* Bug fixes for validation and error handling across all generators
* Compatibility updates for latest EasyCommerce features

= 1.0.0 - October 15, 2025 =
* Real-time dependency checks via new REST controller
* PHPStan level 8 compliance
* Build system and documentation improvements

= 0.9.0 - September 15, 2025 =
* Initial release with 10 core generators
* WordPress admin color integration
* React 18 interface with real-time feedback
* PSR-4 architecture with native EasyCommerce model integration

== Upgrade Notice ==

= 2.1.0 =
Major feature release. Complete admin UI redesign, 3 new generators, Settings page, sample data sync, Our Plugins page, and Playwright e2e suite. No database migrations required. REST API and custom hooks are unchanged.

= 2.0.0 =
Breaking change: parameter schemas updated for all generators. Review custom REST API integrations and hooks before upgrading. Test in staging first.

== Other Notes ==

**Privacy**

All data is stored in your WordPress database. No external transmissions occur. Generated content is fictional and does not represent real individuals or transactions.

**Contributing**

Repository: https://github.com/mralaminahamed/easycommerce-fakerpress

Report issues and request features via GitHub Issues. Pull requests welcome — follow WordPress Coding Standards and PSR-4, and include tests.

**Support**

WordPress.org support forums and GitHub Issues. Documentation is included in the plugin README and GitHub repository.
