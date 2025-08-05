=== EasyCommerce FakerPress ===
Contributors: mralaminahamed
Tags: ecommerce, easycommerce, faker, data-generation, testing, development, products, customers, orders, coupons
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate realistic fake ecommerce data with 10 specialized generators, advanced parameter configuration, WordPress admin color integration, and modern React interface.

== Description ==

EasyCommerce FakerPress is a comprehensive WordPress plugin for generating realistic EasyCommerce test data. This initial release features 10 specialized generators, advanced parameter systems, automatic WordPress admin color integration, and comprehensive business logic modeling. Perfect for developers, agencies, and store owners who need sophisticated test datasets.

**🗂️ 10 Specialized Generators:**

* **🛍️ Products**: Advanced products with attributes, variations, categories, pricing, and inventory management
* **👥 Customers**: Comprehensive profiles with demographics, purchase history, and loyalty tier progression
* **📦 Orders**: Complete orders with payment processing, shipping, tax calculations, and item metadata
* **🎫 Coupons**: Sophisticated discount coupons with usage limits, restrictions, and validity periods
* **🔄 Product Variations**: Detailed variations with attribute systems and inventory tracking
* **🚚 Shipping Plans**: Shipping methods with regional coverage, carrier selection, and cost calculations
* **💰 Tax Management**: Multi-jurisdiction tax classes with location-based rates and rule systems
* **💳 Transactions**: Payment transaction history with multiple gateways and status distributions
* **🛒 Cart Sessions**: Shopping cart sessions with abandonment scenarios and recovery simulation
* **🌍 Location Data**: Comprehensive geographic hierarchy (countries, states, cities) with coordinates

**🎨 Advanced User Experience:**

* **WordPress Admin Color Integration**: Automatically adapts to user's chosen admin color scheme
* **Advanced Parameter System**: Dynamic, nested parameters with intelligent validation
* **Enhanced Form Controls**: Modern React interface with smart form fields and proper labeling
* **Real-time Feedback**: Live generation progress with detailed status updates and error handling

**🎯 Advanced Features:**

* **📊 Realistic Business Logic**: Customer journey modeling, purchase behavior analytics, and loyalty progression
* **🔗 Smart Relationships**: Proper data relationships with referential integrity and business rule validation
* **🌍 International Support**: Multi-country addresses, currencies, phone formats, and localization
* **🎨 Modern Interface**: React 18 with Tailwind CSS, tabbed navigation, and responsive design
* **🛡️ Enterprise Security**: Comprehensive validation, sanitization, capability checks, and nonce verification
* **🔧 Developer Experience**: Abstract patterns, autoloading, extensive hooks, and comprehensive documentation

**💼 Perfect For:**

* **Enterprise Development**: Large-scale ecommerce applications requiring realistic test datasets
* **Agency Projects**: Quick setup of comprehensive demo stores with full business logic
* **QA & Testing**: Consistent, reproducible test scenarios with complex data relationships
* **Performance Testing**: Generate large datasets to test application performance and scalability
* **Integration Testing**: Validate third-party integrations with realistic ecommerce data

**📈 Data Generation Quality:**

**Products:**
* Product variations with size, color, material attributes and proper inventory tracking
* Categories and brands with WordPress taxonomy integration
* Gallery images with metadata and alt text descriptions
* Stock management with quantities, limits, and status tracking
* Pricing strategies with regular prices, sale prices, and bulk discounts

**Customers:**
* International address support with country-specific formatting
* Purchase history modeling based on customer lifecycle and behavior
* Loyalty tier progression (Bronze/Silver/Gold/Platinum) with points systems
* Marketing preferences, communication settings, and behavioral segmentation
* Realistic customer journey patterns from new to loyal customers

**Orders:**
* Complete transaction workflows with payment method variety
* Shipping calculations with carrier selection and delivery estimates
* Multi-rate tax calculations with proper geographic breakdowns
* Order fulfillment tracking with status progression and logistics
* Coupon applications with validation and discount calculations

**Coupons:**
* Percentage and fixed amount discounts with realistic value distributions
* Comprehensive rule systems (spending limits, date ranges, usage restrictions)
* Product and category restrictions with include/exclude logic
* Customer targeting (new customers, VIP members, loyalty tiers)
* Advanced features (free shipping, stackable coupons, quantity requirements)

== Installation ==

**Automatic Installation:**

1. Go to Plugins → Add New in your WordPress admin
2. Search for "EasyCommerce FakerPress"
3. Click Install Now and then Activate
4. Navigate to EC FakerPress in your admin menu

**Manual Installation:**

1. Download the plugin zip file
2. Upload to `/wp-content/plugins/easycommerce-fakerpress/`
3. Run `composer install` in the plugin directory
4. Activate through the 'Plugins' screen in WordPress
5. Access via the new "EC FakerPress" menu item

**Development Setup:**

1. Clone: `git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git`
2. Install: `composer install && npm install`
3. Build: `npm run build`
4. Activate the plugin

== Requirements ==

* **WordPress**: 5.0 or higher
* **PHP**: 7.4 or higher (8.0+ recommended)
* **EasyCommerce Plugin**: Latest version (required for ecommerce functionality)
* **Memory**: 256MB minimum (512MB recommended for large datasets)
* **Disk Space**: 100MB for plugin files, dependencies, and generated data

== Frequently Asked Questions ==

= What makes version 1.0.0 special? =

This initial release is a comprehensive EasyCommerce data generation solution featuring:
- 10 specialized generators covering all major ecommerce data types
- Advanced parameter system with dynamic, nested configuration options
- WordPress admin color integration for personalized user experience
- Complete EasyCommerce model integration with proper business logic
- Modern React interface with enhanced form controls and validation
- Enterprise-grade architecture with PSR-4 namespacing and REST API controllers

= How does EasyCommerce model integration work? =

The plugin uses native EasyCommerce models (Product, Customer, Order, Coupon) instead of direct database queries. This ensures:
- Proper data validation and business rule enforcement
- Compatibility with EasyCommerce updates and extensions
- Consistent data relationships and integrity
- Full feature support including attributes, variations, and meta data

= Can I generate data with complex relationships? =

Yes! The plugin creates realistic data relationships:
- Orders link existing customers and products with proper inventory management
- Customer purchase history affects loyalty tiers and behavior patterns
- Product variations have proper attribute relationships and stock tracking
- Coupon rules validate against actual products, categories, and customer data

= How realistic is the generated data? =

The data is highly realistic thanks to:
- Faker library integration for authentic names, addresses, and content
- Business logic modeling based on real ecommerce patterns
- Customer lifecycle progression from new to loyal customers
- Realistic pricing strategies, inventory levels, and seasonal patterns
- Geographic accuracy with proper country-specific address formats

= Is it safe for production use? =

**Warning**: This plugin generates real data in your database. Recommended usage:
- Development and staging environments only
- Always backup your database before generating large datasets
- Test with small datasets first to understand the impact
- Never use on live production stores without comprehensive backups

= Can I customize the data generation? =

Yes, through multiple methods:
- Extensive hook system for developers to modify generation logic
- Configuration options for data patterns and quantities
- Template system for customizing data structures
- Abstract base classes allow easy extension of generation patterns

= What about performance with large datasets? =

The plugin is optimized for performance:
- Efficient database operations using EasyCommerce abstractions
- Memory-conscious algorithms for large dataset generation
- Batch processing to prevent timeouts and memory exhaustion
- Progress tracking and resumable generation for very large datasets

= How do I remove generated test data? =

Options for data cleanup:
- Use WordPress standard deletion methods for individual items
- Employ specialized cleanup plugins for bulk deletion
- Database queries to remove test data (advanced users only)
- Always backup before both generation and cleanup operations

== Screenshots ==

1. **Modern Admin Interface** - React-based interface with tabbed navigation and responsive design
2. **Product Generation** - Advanced product creation with attributes, variations, and categories
3. **Customer Management** - Comprehensive customer profiles with purchase history and loyalty tracking
4. **Order Processing** - Complete order generation with payment, shipping, and tax calculations
5. **Coupon System** - Sophisticated coupon creation with extensive rule configurations
6. **Progress Monitoring** - Real-time generation progress with detailed feedback and error handling
7. **Mobile Experience** - Fully responsive admin interface optimized for all devices
8. **Data Relationships** - Visual representation of generated data relationships and dependencies

== Changelog ==

= 1.0.0 =
**Release Date: August 5, 2025**

**🎉 Initial Release - Complete EasyCommerce Data Generation Solution:**

**🗂️ 10 Specialized Generators:**
* ✨ **Products**: Advanced generation with attributes, variations, categories, pricing, and inventory management
* 👥 **Customers**: Comprehensive profiles with demographics, purchase history, and loyalty tier progression
* 📦 **Orders**: Complete orders with payment processing, shipping, tax calculations, and item metadata
* 🎫 **Coupons**: Sophisticated discount system with usage limits, restrictions, and validity periods
* 🔄 **Product Variations**: Detailed variations with attribute systems and inventory tracking
* 🚚 **Shipping Plans**: Methods with regional coverage, carrier selection, and cost calculations
* 💰 **Tax Management**: Multi-jurisdiction tax classes with location-based rates and rule systems
* 💳 **Transactions**: Payment transaction history with multiple gateways and status distributions
* 🛒 **Cart Sessions**: Shopping cart abandonment scenarios and recovery simulation
* 🌍 **Location Data**: Geographic hierarchy (countries, states, cities) with coordinates and timezone support

**🎨 Modern User Experience:**
* 🌈 **WordPress Admin Color Integration**: Automatic adaptation to user's chosen admin color scheme
* 🎛️ **Advanced Parameter System**: Dynamic, nested parameters with intelligent validation and smart defaults
* 📝 **Enhanced Form Controls**: Modern React interface with smart form fields and proper labeling
* 📋 **Tabbed Navigation**: Organized 10-generator interface with progress tracking
* ⚡ **Real-time Feedback**: Live generation progress with detailed status updates and error handling
* 📱 **Responsive Design**: Mobile-optimized interface with improved accessibility

**🏗️ Enterprise Architecture:**
* 🔧 **PSR-4 Architecture**: Modern PHP with namespacing, autoloading, and abstract base classes
* 🌐 **REST API Controllers**: 10 clean API controllers with comprehensive parameter schemas
* 🔗 **EasyCommerce Integration**: Native model usage with Order_Item_Meta and location system integration
* 📚 **WordPress Standards**: Full WPCS compliance with security best practices and proper internationalization
* 🛠️ **Extensible Design**: Hook system and abstract patterns for easy customization and extension

**🔧 Technical Excellence:**
* ⚡ **Modern Build System**: React 18, Tailwind CSS, Webpack 5 with CSS variable integration
* ✅ **Advanced Validation**: Client-side and server-side parameter validation with proper error handling
* 🔄 **State Management**: Complex form handling with nested object parameter support
* 🚀 **Performance Optimization**: Efficient database operations and memory management
* 👨‍💻 **Developer Experience**: Comprehensive documentation, modern tooling, and extensive customization hooks

== Upgrade Notice ==

= 1.0.0 =
Initial release of EasyCommerce FakerPress! Complete EasyCommerce data generation solution with 10 specialized generators, WordPress admin color integration, advanced parameter system, and modern React interface. Requires EasyCommerce plugin for full functionality.

== Privacy & Data Handling ==

**Data Storage:**
* All generated data is stored locally in your WordPress database using EasyCommerce tables
* No external services are contacted during data generation
* No personal data is transmitted outside your server environment

**Generated Content:**
* Uses Faker library to create fictional but realistic-looking data
* All customer data includes fake names, addresses, and contact information  
* Generated content is clearly test data, not real customer information
* Complies with privacy regulations as no actual personal data is involved

**Security Recommendations:**
* Use only on development/staging environments for safety
* Always backup your database before generating large datasets
* Understand data generation implications before use on any live site
* Regular cleanup of test data to maintain database performance

== Developer Information ==

**Modern Architecture:**

```php
namespace EasyCommerceFakerPress\Generators;

use EasyCommerce\Models\Product;
use EasyCommerceFakerPress\Abstracts\Generator;

class Product_Generator extends Generator {
    protected function generate_single_item() {
        $product = new Product();
        return $product->create([
            'title' => $this->faker->words(3, true),
            'attributes' => $this->get_or_create_product_attributes(),
            'variations' => $this->generate_product_variations(),
            'meta' => $this->generate_product_meta(),
        ]);
    }
}
```

**Available Hooks:**

```php
// Modify product generation
add_filter('ecfp_product_data', 'custom_product_data');

// Customize customer creation  
add_action('ecfp_after_customer_created', 'custom_customer_setup');

// Modify order generation
add_filter('ecfp_order_meta', 'custom_order_meta');

// Customize coupon rules
add_filter('ecfp_coupon_rules', 'custom_coupon_rules');
```

**REST API Endpoints:**

```
POST /wp-json/ecfp/v1/products/generate
POST /wp-json/ecfp/v1/customers/generate  
POST /wp-json/ecfp/v1/orders/generate
POST /wp-json/ecfp/v1/coupons/generate
```

**File Structure:**
```
easycommerce-fakerpress/
├── easycommerce-fakerpress.php           # Main plugin file
├── class-easycommerce-fakerpress.php     # Main plugin class  
├── includes/
│   ├── Abstracts/                        # Abstract base classes
│   ├── Generators/                       # Data generators with EasyCommerce integration
│   └── REST/Controllers/                 # REST API controllers
├── src/admin/                            # React components and modern UI
├── build/                                # Compiled assets
├── vendor/                               # Composer dependencies
└── composer.json                         # PSR-4 autoloading configuration
```

**Contributing:**
* GitHub: https://github.com/mralaminahamed/easycommerce-fakerpress
* Issues: Report bugs and request features via GitHub Issues
* Pull Requests: Code contributions welcome with proper testing
* Standards: WordPress Coding Standards + PSR-4 + EasyCommerce best practices

== Support ==

**Resources:**
* 📚 **Documentation**: Comprehensive developer and user guides
* 🐛 **Bug Reports**: GitHub Issues or WordPress.org support forum
* 💡 **Feature Requests**: Submit via GitHub Issues with detailed requirements
* 👨‍💻 **Development**: Extensive hooks, filters, and developer documentation
* 📧 **Professional Support**: Available for custom development and integration projects

**Community:**
* WordPress.org support forum for general questions
* GitHub Discussions for technical conversations
* Code contributions via Pull Requests
* Documentation improvements and translations welcome

== About the Author ==

Developed by **Al Amin Ahamed** - Senior Full-Stack Developer specializing in WordPress, EasyCommerce, React, and modern web technologies with 10+ years of experience in ecommerce solutions.

**Expertise:**
* 🌐 **Website**: https://alaminahamed.com
* 💼 **GitHub**: https://github.com/mralaminahamed  
* 📧 **Contact**: me@alaminahamed.com
* 💼 **Services**: Enterprise WordPress development, EasyCommerce solutions, React applications, performance optimization

**Professional Focus:**
* EasyCommerce plugin development and customization
* Modern WordPress architecture and best practices
* React-based admin interfaces and SPAs
* Performance optimization and scalability solutions
* Enterprise ecommerce platform development