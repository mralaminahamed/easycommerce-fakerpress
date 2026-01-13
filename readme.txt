=== EasyCommerce FakerPress ===
Contributors: mralaminahamed
Tags: ecommerce, faker, data-generation, testing, development
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.0.2
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate realistic EasyCommerce test data: 10 specialized generators, real-time validation, advanced config, and seamless admin integration.

== Description ==

EasyCommerce FakerPress is a robust WordPress plugin designed to generate realistic test data for the EasyCommerce e-commerce platform. It supports developers, agencies, and store owners in creating sophisticated datasets for testing, demonstrations, and performance evaluation. Key features include:

* **10 Specialized Generators**: For products, customers, orders, coupons, variations, shipping, taxes, transactions, cart sessions, and locations.
* **Real-Time Validation**: Ensures data integrity with dependency checks and user-friendly feedback.
* **Advanced Configuration**: Nested parameters, intelligent defaults, and extensible hooks.
* **Comprehensive Hook System**: 15+ filters and actions for complete data customization and workflow integration.
* **Modern Interface**: Built with React Router v7, Tailwind CSS, and automatic WordPress admin color scheme adaptation.
* **Enterprise-Grade Architecture**: PSR-4 compliant, with native EasyCommerce model integration and 11 REST API controllers.

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

1. Modern Admin Interface: React-based tabbed navigation with WordPress admin color integration.
   *(Screenshot: Admin dashboard overview)*
2. Product Generator: Controls for attributes, variations, categories, and inventory.
   *(Screenshot: Product generation form)*
3. Customer Generator: Profile creation with demographics and loyalty tracking.
   *(Screenshot: Customer profile form)*
4. Order Generator: Workflow simulation including payments and shipping.
   *(Screenshot: Order creation interface)*
5. Coupon Generator: Rule configuration for discounts and restrictions.
   *(Screenshot: Coupon setup panel)*
6. Product Variation Generator: Attribute-based variation and stock management.
   *(Screenshot: Variations editor)*
7. Shipping Plan Generator: Regional methods and cost calculations.
   *(Screenshot: Shipping configuration)*
8. Tax Generator: Jurisdiction-based rates and rules.
   *(Screenshot: Tax management form)*
9. Transaction Generator: Gateway history and status distributions.
   *(Screenshot: Transactions overview)*
10. Cart Session Generator: Abandonment and recovery simulations.
    *(Screenshot: Cart session tools)*
11. Location Generator: Geographic hierarchy with coordinates.
    *(Screenshot: Location data form)*

== Changelog ==

= 2.0.2 - December 15, 2025 =
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
