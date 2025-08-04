=== EasyCommerce FakerPress ===
Contributors: mralaminahamed
Tags: ecommerce, easycommerce, faker, data-generation, testing, development, products, customers, orders, coupons
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL v3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Generate realistic fake ecommerce data (products, customers, orders, coupons) for testing and development with modern React admin interface.

== Description ==

EasyCommerce FakerPress is a professional WordPress plugin designed for developers and store owners who need realistic test data for their EasyCommerce stores. Built with modern technologies including React, PHPFaker library, and Tailwind CSS, it provides an intuitive admin interface for generating comprehensive ecommerce datasets.

**✨ Key Features:**

* **🛍️ Product Generation**: Create products with SKUs, prices, stock levels, attributes, categories, and images
* **👥 Customer Creation**: Generate WordPress users with complete billing/shipping addresses and ecommerce metadata
* **📦 Order Generation**: Create realistic orders that link existing customers and products with proper order flow
* **🎫 Coupon Creation**: Generate discount coupons with various types, amounts, and expiration dates
* **⚡ Modern Interface**: Beautiful React-based admin interface with Tailwind CSS styling
* **🎨 Tabbed Navigation**: Intuitive interface organized by data type for easy access
* **🔧 WordPress Standards**: Follows all WordPress coding standards and best practices
* **🛡️ Security First**: Proper sanitization, validation, and nonce verification throughout
* **🌐 Translation Ready**: Fully internationalized with proper text domains

**🎯 Perfect For:**

* **Developers**: Testing themes, plugins, and custom functionality
* **Store Owners**: Populating development/staging sites with realistic data
* **Agencies**: Quick setup of demo stores with comprehensive product catalogs
* **QA Teams**: Creating consistent test datasets for thorough testing

**📊 Data Generation Features:**

* **Smart Dependencies**: Orders automatically use existing products and customers
* **Realistic Content**: Leverages Faker library for authentic-looking names, descriptions, and addresses
* **Proper Relationships**: Maintains referential integrity between related data (customers → orders → products)
* **Flexible Quantities**: Generate anywhere from 1 to hundreds of items at once
* **EasyCommerce Integration**: Full compatibility with EasyCommerce data structures and metadata

**🏗️ Technical Excellence:**

* **Modern Stack**: React 18, Tailwind CSS, PHP 7.4+, WordPress APIs
* **Asset Management**: Webpack build system with proper dependency handling
* **Code Quality**: ESLint, Stylelint, PHPCS, PHPStan integration
* **Performance**: Optimized queries and efficient data generation algorithms
* **Extensibility**: Developer-friendly architecture with proper hooks and filters

== Installation ==

**Automatic Installation:**

1. Go to Plugins → Add New in your WordPress admin
2. Search for "EasyCommerce FakerPress"
3. Click Install Now and then Activate
4. Navigate to EC FakerPress in your admin menu

**Manual Installation:**

1. Download the plugin zip file
2. Upload to `/wp-content/plugins/easycommerce-fakerpress/`
3. Activate through the 'Plugins' screen in WordPress
4. Access via the new "EC FakerPress" menu item

**Development Setup:**

1. Clone the repository: `git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git`
2. Install dependencies: `composer install && yarn install`
3. Build assets: `yarn build`
4. Activate the plugin

== Requirements ==

* **WordPress**: 5.0 or higher
* **PHP**: 7.4 or higher (8.0+ recommended)
* **EasyCommerce**: Latest version (required for ecommerce functionality)
* **Memory**: 128MB minimum (256MB recommended for large datasets)
* **Disk Space**: 50MB for plugin files and dependencies

== Frequently Asked Questions ==

= Does this work with any EasyCommerce theme? =

Yes! The plugin generates standard EasyCommerce data that works with any properly coded theme. It doesn't modify frontend display, only creates backend data.

= How much data can I generate at once? =

You can generate up to 100 items per request through the interface. For larger datasets, simply run multiple generations. The plugin handles memory efficiently and won't crash your site.

= Is the generated data realistic? =

Absolutely! We use the industry-standard Faker library to create authentic-looking:
- Product names and descriptions
- Customer names and addresses
- Realistic pricing and stock levels
- Proper order workflows and statuses

= Can I delete generated data? =

The plugin focuses on data generation. To remove test data, use WordPress's standard deletion methods or specialized cleanup plugins. Always backup before generating large datasets.

= Does this affect my live store? =

**Warning**: This plugin generates real data in your database. Only use on development/staging sites, never on live production stores without proper backups.

= Is it compatible with multisite? =

Yes, the plugin works on WordPress multisite installations. Each site maintains its own generated data independently.

= Can I customize what data is generated? =

Currently, the plugin generates predefined realistic datasets. Custom data generation features are planned for future releases. Developers can extend functionality using available hooks.

= How does order generation work? =

Orders are intelligently created by:
1. Selecting random existing customers (or creating new ones if none exist)
2. Adding random products to the cart
3. Applying realistic shipping and billing information
4. Setting appropriate order statuses and dates

= What about product images? =

The plugin creates products with placeholder images. For custom images, consider using additional plugins or manual upload after generation.

= Is technical support available? =

Yes! Report issues via GitHub Issues or the WordPress.org support forum. Documentation and examples are included with the plugin.

== Screenshots ==

1. **Main Admin Interface** - Clean, tabbed interface for different data types
2. **Product Generation** - Generate products with comprehensive options
3. **Customer Creation** - Create users with complete ecommerce profiles
4. **Order Generation** - Realistic order creation with proper relationships
5. **Coupon Creation** - Flexible discount coupon generation
6. **Progress Feedback** - Real-time generation progress and success messages
7. **Mobile Responsive** - Admin interface works perfectly on all devices

== Changelog ==

= 1.0.0 =
**Release Date: August 4, 2025**

**🎉 Initial Release Features:**

* ✨ Complete data generation system for EasyCommerce
* 🛍️ Product generator with SKUs, pricing, stock, attributes, and categories
* 👥 Customer generator with WordPress users and EasyCommerce metadata
* 📦 Order generator with realistic order workflows and relationships
* 🎫 Coupon generator with flexible discount types and settings
* ⚡ Modern React-based admin interface with Tailwind CSS
* 🎨 Intuitive tabbed navigation for organized data management
* 🔧 WordPress Coding Standards compliance throughout
* 🛡️ Comprehensive security with proper sanitization and validation
* 🌐 Full internationalization support with translation-ready strings
* 📱 Mobile-responsive admin interface for any device
* ⚙️ Developer-friendly architecture with hooks and filters

**🔧 Technical Implementation:**

* Modern singleton pattern with PHP 8+ compatibility
* EasyCommerce feature compatibility declarations
* Proper dependency checking and admin notices
* Asset management with Webpack and dependency injection
* Comprehensive error handling and user feedback
* Performance optimized with efficient database operations
* Code quality tools: ESLint, Stylelint, PHPCS, PHPStan
* Professional build system with hot module replacement

**📚 Documentation & Support:**

* Comprehensive README with installation and usage instructions
* Developer documentation with architecture overview
* Example code and integration patterns
* GitHub repository with issue tracking
* WordPress.org support forum integration

== Upgrade Notice ==

= 1.0.0 =
Initial release of EasyCommerce FakerPress! Generate realistic EasyCommerce test data with our modern React interface. Perfect for developers, agencies, and store owners who need comprehensive ecommerce datasets for testing and development.

== Privacy & Data Handling ==

**Data Storage:**
* All generated data is stored in your WordPress database using standard EasyCommerce tables
* No external services are contacted during data generation
* No personal data is transmitted outside your server

**Generated Data:**
* Uses Faker library to create fictional but realistic-looking data
* Customer data includes fake names, addresses, and contact information
* All generated content is clearly test data, not real customer information

**Recommendations:**
* Only use on development/staging environments
* Always backup your database before generating large datasets
* Never use on live production sites without understanding the implications

== Developer Information ==

**Extending the Plugin:**

The plugin provides several hooks for developers:

```php
// Modify product generation
add_filter( 'ecfp_product_data', 'custom_product_data' );

// Customize customer creation
add_action( 'ecfp_after_customer_created', 'custom_customer_setup' );

// Modify order generation
add_filter( 'ecfp_order_meta', 'custom_order_meta' );
```

**File Structure:**
```
easycommerce-fakerpress/
├── easycommerce-fakerpress.php      # Main plugin file
├── class-easycommerce-fakerpress.php # Main plugin class
├── includes/                         # Generator classes
├── src/admin/                       # React components
├── build/                           # Compiled assets
└── vendor/                          # Composer dependencies
```

**Contributing:**
* GitHub: https://github.com/mralaminahamed/easycommerce-fakerpress
* Issues: Report bugs and request features
* Pull Requests: Code contributions welcome
* Standards: WordPress Coding Standards + PSR-4

== Support ==

* 📚 **Documentation**: Comprehensive guides included in plugin
* 🐛 **Bug Reports**: GitHub Issues or WordPress.org support forum
* 💡 **Feature Requests**: Submit via GitHub Issues
* 👨‍💻 **Development**: Extensive hooks and developer documentation
* 📧 **Contact**: Available for custom development projects

== About the Author ==

Developed by **Al Amin Ahamed** - Full-stack WordPress developer specializing in EasyCommerce, React, and modern web technologies.

* 🌐 **Website**: https://alaminahamed.com
* 💼 **GitHub**: https://github.com/mralaminahamed
* 📧 **Email**: me@alaminahamed.com
* 💼 **Services**: Custom WordPress development, EasyCommerce solutions, React applications