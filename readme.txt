=== EasyCommerce FakerPress ===
Contributors: mralaminahamed
Tags: ecommerce, faker, data-generation, testing, development
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.1.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate realistic EasyCommerce test data with 14 generators, modern SaaS UI, run history, settings, and sample data sync from GitHub.

== Description ==

EasyCommerce FakerPress is a robust WordPress plugin designed to generate realistic test data for the EasyCommerce e-commerce platform. It supports developers, agencies, and store owners in creating sophisticated datasets for testing, demonstrations, and performance evaluation. Key features include:

* **14 Specialized Generators**: Products, customers, orders, coupons, variations, shipping plans, tax classes, transactions, cart sessions, locations, product reviews, attributes, refunds, and logs.
* **Modern SaaS Admin UI**: Clean white interface with blue/indigo accents; homepage stats bar and generator grid; two-panel generator page with sticky action panel and run history sidebar.
* **Generation Run History**: Per-generator run log in browser localStorage; recent runs shown in sidebar; all-time stats on homepage.
* **Settings Page**: Configure default count, locale, seed, and metadata preference; adjustable max run history; sample data sync from GitHub; About card.
* **Sample Data Sync**: One-click download of locale-specific reference data (75+ locales) from the companion sample-data repository.
* **Our Plugins Page**: Discover other plugins by the same author pulled live from WordPress.org.
* **Playwright E2E Suite**: 131 automated tests covering all generators, field types, and UI interactions.
* **Comprehensive Hook System**: 15+ filters and actions for complete data customization and workflow integration.
* **Enterprise-Grade Architecture**: PSR-4 compliant, native EasyCommerce model integration, 14 REST API controllers.

This plugin is ideal for enterprise development, QA testing, integration validation, and scalable performance assessments in non-production environments.

**Extensibility & Customization**:
The plugin provides a comprehensive hook system allowing developers to customize every aspect of data generation:
- **Data Filters**: Modify generated data before creation with 10+ `easycommerce_fakerpress_*_data_before_create` filters
- **Result Filters**: Customize returned data with `easycommerce_fakerpress_*_generation_result` filters
- **Workflow Actions**: Integrate with generation process using strategic `easycommerce_fakerpress_after_*_created` actions
- **API Integration**: Filter REST responses with `easycommerce_fakerpress_rest_response` for complete API customization

**Data Generation Highlights**:
- **Products**: Includes attributes, variations, categories, pricing strategies, and inventory tracking.
- **Customers**: Features demographics, purchase history, loyalty tiers, and behavioral segmentation.
- **Orders**: Covers payment processing, shipping calculations, tax breakdowns, and fulfillment workflows.
- **Coupons**: Supports discount rules, usage limits, restrictions, and targeting logic.

Generated data leverages the Faker library for authenticity while adhering to real-world e-commerce patterns, ensuring compatibility with EasyCommerce updates and extensions.

== Installation ==

### Automatic Installation
1. Navigate to **Plugins → Add New** in your WordPress admin dashboard.
2. Search for "EasyCommerce FakerPress".
3. Click **Install Now**, then **Activate**.
4. Access the plugin via the new **EC FakerPress** menu item.

### Manual Installation
1. Download the plugin ZIP file.
2. Upload it to `/wp-content/plugins/easycommerce-fakerpress/`.
3. Run `composer install` in the plugin directory.
4. Activate via the **Plugins** screen.
5. Access via the **EC FakerPress** menu.

### Development Setup
1. Clone the repository: `git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git`.
2. Install dependencies: `composer install && yarn install`.
3. Build assets: `yarn build`.
4. Activate the plugin.

**Requirements**:
- WordPress 5.0+
- PHP 7.4+ (8.0+ recommended)
- EasyCommerce plugin (latest version required)
- Minimum 256MB memory (512MB for large datasets)
- 100MB disk space for files and data

== Frequently Asked Questions ==

= What's new in version 2.1.0? =
This is the largest release since 2.0.0. It delivers a complete admin UI redesign in a modern SaaS style, three new generators (Attribute, Refund, Log), an expanded Settings page, a sample data sync feature backed by the companion GitHub repository, an "Our Plugins" page, and a 131-test Playwright e2e suite. The generator architecture was replaced — the 757-line GeneratorBase monolith is gone, replaced by focused ParamsPanel and ActionPanel components with parameter config centralised in generators.ts.

= What's new in version 2.0.4? =
This release includes bug fixes and code improvements. Key updates include fixed route vs type mismatch in generators, extracted shared type definitions for better code reuse, removed duplicate Window interface declarations, added missing default values in TransactionGenerator, removed console statements from production code, and improved asset path configuration.

= What's new in version 2.0.3? =
This release adds the Product Review generator and fixes Order data structure issues. Key updates include realistic product review generation with rating distribution, WordPress comments integration for reviews, proper Order_Notes model usage, and enhanced data integrity across all generators.

= What's new in version 2.0.2? =
This release brings UI enhancements, Tailwind CSS v4 upgrade, and improved user experience. Key updates include a "Get Started" plugin action link, modern CSS features with better performance, fixed visual inconsistencies, and updated build system compatibility.

= What's new in version 2.0.0? =
This major release includes complete parameter schema alignment between frontend forms and backend API validation. All 10 generators now have perfectly aligned parameter structures, ensuring reliable data generation. Key updates include TypeScript migration, comprehensive parameter validation, and enterprise-grade frontend-backend integration.

= How does EasyCommerce integration work? =
The plugin utilizes native EasyCommerce models (e.g., Product, Customer, Order) for generation, ensuring data validation, relationship integrity, and compatibility with future updates. Direct database queries are avoided to maintain business logic enforcement.

= Can I generate data with complex relationships? =
Yes. Examples include linking orders to existing customers/products with inventory adjustments, modeling purchase history for loyalty progression, and validating coupon rules against categories and user data.

= How realistic is the generated data? =
Data is crafted using Faker for authentic details (e.g., addresses, names) combined with e-commerce-specific logic, such as seasonal pricing, geographic accuracy, and customer lifecycle patterns.

= Is it safe for production use? =
**Caution**: Use exclusively in development or staging environments. Always back up your database prior to generation, start with small datasets, and avoid live sites without thorough testing.

= Can I customize generation? =
Yes, extensively! The plugin includes a comprehensive hook system with 15+ filters and actions for complete customization. Use `easycommerce_fakerpress_*_data_before_create` filters to modify data before creation, `easycommerce_fakerpress_*_generation_result` filters for result customization, and `easycommerce_fakerpress_after_*_created` actions for workflow integration. Configuration panels support quantities/patterns, and abstract classes enable extending generators.

= What about performance for large datasets? =
Optimizations include batch processing, memory-efficient algorithms, and resumable progress tracking to handle extensive datasets without timeouts.

= How do I remove generated data? =
Employ WordPress deletion tools for items, bulk cleanup plugins, or targeted database queries (for advanced users). Back up data before any removal.

== Screenshots ==

1. Homepage: StatsBar with live generation counts and GeneratorGrid grouped by category (Core, Advanced, Enhanced) with Popular badges.
2. Products Generator: Full two-panel layout — sidebar with category nav, params panel with product type selector, price range, categories and inventory settings, sticky action panel with count, locale, seed and Generate button.
3. Customers Generator: ChipField pill-selects for customer types and age groups, address preference toggles, purchase history options.
4. Orders Generator: Order status selector, customer distribution controls, items per order range, and payment method configuration.
5. Coupons Generator: Discount type chip-selects (Percentage, Fixed, Free Shipping, Products), discount range inputs, usage limit toggle.
6. Settings Page: Generation Defaults card (count, locale, seed, include-metadata), Run History limit, Sample Data sync with status indicator.
7. Our Plugins Page: Live WordPress.org plugin cards fetched from the API with ratings, active install counts, and direct links.
8. Product Variations Generator: Variable product type selection, price variance range, stock management settings.
9. Product Reviews Generator: Link reviews to specific products, realistic rating distribution with verified purchase support.
10. Shipping Plans Generator: Shipping type chip-selects, cost and coverage range, calculation method and delivery timeframe controls.
11. Attributes Generator: Attribute type chips (Text, Color, Image) for generating product attributes used in variations and filtering.

== Changelog ==

= 2.1.0 - April 26, 2026 =
* **Admin UI redesign**: Complete overhaul to a Modern SaaS style (clean white, blue/indigo accents, Linear/Notion aesthetic)
* **New homepage**: StatsBar with 4 live stat cards (products, customers, orders, total) + GeneratorGrid grouped by category with Popular badges
* **New generator page layout**: Sticky top-bar, collapsible sidebar with category nav and per-generator recent run history, two-panel params + action layout
* **New component architecture**: Replaced 757-line GeneratorBase monolith and 14 per-generator TSX files with focused ParamsPanel, ActionPanel, and GeneratorSidebar components; parameterConfig centralised in generators.ts
* **Run history**: Per-generator run log persisted to localStorage (configurable max, FIFO); recent runs shown in sidebar; all-time stats on homepage
* **3 new generators**: Attribute, Refund, Log (total 14)
* **Settings page**: Default count, locale, seed, include-metadata toggle; configurable max runs per generator; sample data sync; About card with version and links; Reset Settings button
* **Sample data sync**: Download / force-re-sync locale-specific reference data from the companion GitHub repository via new GET (status) + POST (sync/force) REST endpoints
* **Our Plugins page**: Fetches and displays the author's other WordPress.org plugins with live data
* **Global sticky nav**: Generators / Settings / Our Plugins links with correct active-state matching
* **Playwright e2e suite**: 131 automated tests covering home page, generator page layout, ActionPanel interactions, all 6 field types, and all 14 generators
* **Bug fixes**: Category matching now works in all locales (`CATEGORY_ORDER` wrapped in `__()`); single ActionPanel instance (was duplicated causing Playwright strict-mode failures); `a:focus` scoped to plugin container and preserves keyboard outline; RangeField error colour; nav active state for nested routes

= 2.0.3 - January 15, 2026 =
* **Product Review Generator**: Added new generator for creating realistic product reviews with ratings
* **Review Rating System**: Implemented weighted rating distribution favoring higher ratings (realistic patterns)
* **WordPress Comments Integration**: Leverages WordPress comment system for review storage
* **Verified Purchase Tracking**: Reviews can be marked as verified purchases for enhanced credibility
* **Order Generator Data Structure Fix**: Corrected Order generator to match EasyCommerce Order model expectations
* **Order Notes Integration**: Added proper order notes creation using Order_Notes model
* **Controller Pattern Alignment**: Updated Product_Review controller to match existing controller patterns
* **API Schema Consistency**: Added proper resource-specific properties and parameter validation

= 2.0.2 - January 15, 2026 =
* **New Features**: Added "Get Started" plugin action link for easier access
* **Tailwind CSS v4**: Upgraded to Tailwind CSS v4.1.18 with improved performance
* **UI Improvements**: Fixed visual inconsistencies in success messages and navigation
* **Technical Updates**: Enhanced build system and dependency compatibility

= 2.0.1 - November 13, 2025 =
* **Bug Fixes**: Minor bug fixes and improvements
* **Code Quality**: Code quality enhancements and optimizations
* **Documentation**: Updated version references and documentation

= 2.0.0 - November 11, 2025 =
* **Major Release**: Complete parameter schema alignment for all 10 generators
* **Frontend-Backend Integration**: Perfect alignment between React forms and REST API validation
* **Type Safety**: Full TypeScript migration with proper interfaces and validation
* **Parameter Fixes**: Corrected array vs string mismatches, naming inconsistencies, and missing parameters
* **API Reliability**: Guaranteed valid data submission from frontend to backend endpoints
* **Code Quality**: Enhanced error handling, linting compliance, and build verification
* **Breaking Changes**: Parameter schemas updated - review custom integrations before upgrading

= 1.0.4 - November 10, 2025 =
* **Hook System Enhancement**: Added comprehensive filter and action hooks for complete data customization and workflow integration
* **Extensibility Improvements**: Implemented 15+ hooks including data filters, result filters, and strategic actions
* **API Customization**: Added REST response filtering for complete API extensibility
* **Code Quality**: Maintained PHPStan level 8 compliance and WordPress coding standards

= 1.0.2 - October 29, 2025 =
* **Performance Improvements**: Optimized data generation algorithms for better memory usage and faster processing
* **Bug Fixes**: Fixed validation issues and improved error handling across all generators
* **Compatibility Updates**: Enhanced compatibility with latest EasyCommerce plugin features
* **UI Enhancements**: Refined interface components and improved user experience

= 1.0.0 - October 15, 2025 =
* **Validation Enhancements**: Added real-time dependency checks via new REST controller; integrated UI indicators and error handling.
* **Code Quality**: Achieved PHPStan level 8 compliance; fixed controller schemas and enhanced PHPDoc.
* **Development Tools**: Optimized build system, updated documentation, and refined .gitignore.

= 0.9.0 - September 15, 2025 (Initial Release) =
* **Core Generators**: Introduced 10 specialized tools for products, customers, orders, coupons, variations, shipping, taxes, transactions, carts, and locations.
* **User Experience**: Implemented WordPress admin color integration, advanced parameter system, responsive React interface, and real-time feedback.
* **Architecture**: Adopted PSR-4 structure, 10+ REST controllers, and native EasyCommerce model integration.
* **Technical Foundations**: Built with React 18, Tailwind CSS, Webpack 5; includes validation, state management, and extensibility hooks.

== Upgrade Notice ==

= 2.1.0 =
Major feature release. Complete admin UI redesign, 3 new generators, Settings page, sample data sync, Our Plugins page, and full Playwright e2e test suite. No database migrations required. Custom hooks and REST API usage are unchanged.

= 2.0.3 =
Product Review generator and Order data structure fixes. Recommended upgrade for enhanced functionality and data integrity.

= 2.0.2 =
UI enhancements and Tailwind CSS v4 upgrade. Recommended upgrade for improved user experience.

= 2.0.1 =
Minor bug fixes and improvements. Recommended upgrade for all users.

= 2.0.0 =
* **Major Update**: Complete parameter schema alignment. Test custom integrations in staging before upgrading. Parameter structures have changed for all generators - review API usage and custom hooks.

= 1.0.4 =
Performance improvements and bug fixes. Recommended upgrade for all users. No data migration required.

= 1.0.0 =
This version includes significant architectural updates for improved stability and validation. Upgrade recommended for all users; test in staging first, especially if using custom hooks. No data migration required.

== Other Notes ==

**Privacy & Data Handling**:
- Data is stored solely in your WordPress database; no external transmissions occur.
- Generated content is fictional and compliant with privacy standards.
- **Security Tip**: Restrict to non-production use and maintain regular backups.

**Contributing**:
- Repository: [GitHub](https://github.com/mralaminahamed/easycommerce-fakerpress)
- Report issues or request features via GitHub Issues.
- Submit pull requests with tests, adhering to WordPress Coding Standards and PSR-4.

**Support**:
- Documentation: Included in the plugin and GitHub wiki.
- Forums: WordPress.org support threads.
- Professional assistance: Available for custom integrations.
