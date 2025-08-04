# EasyCommerce FakerPress

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.txt)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-8892BF.svg)](https://php.net/)

A professional WordPress plugin that generates realistic fake ecommerce data (products, customers, orders, and coupons) for testing and development purposes with the EasyCommerce plugin. Built with modern technologies including React admin interface, PHPFaker library, and seamless EasyCommerce model integration.

## ✨ Features

### Core Data Generation
- 🛍️ **Products**: Generate products with attributes, variations, categories, pricing, and gallery images
- 👥 **Customers**: Create customers with realistic profiles, purchase history, and loyalty tiers
- 📦 **Orders**: Generate complete orders with payment processing, shipping, and tax calculations
- 🎫 **Coupons**: Create sophisticated discount coupons with comprehensive rule systems

### Technical Excellence
- ⚡ **EasyCommerce Integration**: Full integration with EasyCommerce data models and business logic
- 🎨 **Modern Interface**: React-based admin interface with Tailwind CSS and tabbed navigation
- 🏗️ **Architecture**: PSR-4 namespacing, abstract base classes, and REST API controllers
- 🔧 **WordPress Standards**: Follows WordPress Coding Standards (WPCS) and best practices
- 🛡️ **Security**: Proper sanitization, validation, nonce verification, and capability checks
- 🌐 **Internationalization**: Ready for translation with proper text domains

### Advanced Features
- 📊 **Realistic Data Patterns**: Customer journey modeling, purchase behavior, and loyalty systems
- 🔗 **Smart Dependencies**: Orders link existing customers and products with proper relationships
- 🎯 **Business Logic**: Inventory management, tax calculations, shipping costs, and coupon validation
- 🌍 **International Support**: Multi-country addresses, currencies, and localization

## 📋 Requirements

- **WordPress**: 5.0 or higher  
- **PHP**: 7.4 or higher
- **EasyCommerce Plugin**: Required for ecommerce functionality
- **Node.js**: 14+ (for development)
- **Composer**: For PHP dependency management

## 🚀 Installation

### Manual Installation

1. **Download**: Clone or download this plugin to your WordPress plugins directory
   ```bash
   cd /path/to/wp-content/plugins/
   git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git
   ```

2. **Install Dependencies**: 
   ```bash
   cd easycommerce-fakerpress
   composer install          # Install PHP dependencies
   npm install              # Install Node.js dependencies
   ```

3. **Build Assets**:
   ```bash
   npm run build            # Build production assets
   ```

4. **Activate**: Go to WordPress Admin → Plugins and activate "EasyCommerce FakerPress"

### Development Setup

For development work on this plugin:

```bash
# Clone the repository
git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git
cd easycommerce-fakerpress

# Install dependencies
composer install
npm install

# Start development server with hot reload
npm run dev
```

## 🛠️ Development

### Build Commands

```bash
# Development build with watch mode
npm run dev
npm run start

# Production build
npm run build

# Linting and code quality
npm run lint             # Lint JS and CSS
npm run fix              # Auto-fix linting issues
composer run lint        # PHP CodeSniffer
composer run fix         # PHP Code Beautifier
```

### Code Quality & Standards

```bash
# PHP Quality Tools
composer run lint        # PHPCS linting
composer run fix         # PHPCBF auto-fix
composer run phpstan     # Static analysis

# JavaScript/CSS Tools  
npm run lint:js          # ESLint
npm run lint:css         # Stylelint
npm run fix              # Auto-fix JS/CSS issues
```

## 📖 Usage

### Admin Interface

1. Navigate to **WordPress Admin → EC FakerPress**
2. Choose from the tabbed interface:
   - **Products**: Generate products with attributes, variations, and categories
   - **Customers**: Create customer accounts with realistic profiles and history
   - **Orders**: Generate purchase orders with complete payment and shipping data
   - **Coupons**: Create discount coupons with sophisticated rule systems
3. Set the number of items to generate (1-100)
4. Click **Generate** button and monitor progress

### Generated Data Quality

#### Products
- **Realistic Product Data**: Names, descriptions, pricing, and SKUs
- **Product Variations**: Size, color, material attributes with proper inventory
- **Categories & Brands**: WordPress taxonomy integration
- **Gallery Images**: Placeholder images with proper metadata
- **Inventory Management**: Stock quantities, low stock limits, and status tracking

#### Customers  
- **Complete Profiles**: Names, emails, addresses, and phone numbers
- **International Support**: Multi-country addresses with proper formatting
- **Purchase History**: Realistic order counts and spending patterns based on customer age
- **Loyalty Systems**: Bronze/Silver/Gold/Platinum tiers with points
- **Behavioral Data**: Marketing preferences, communication settings, and segmentation tags

#### Orders
- **Complete Transactions**: Customer selection, product variations, and pricing
- **Payment Processing**: Multiple payment methods with realistic transaction data
- **Shipping Calculations**: Carrier selection, costs, and delivery estimates
- **Tax Management**: Multi-rate tax calculations with proper breakdowns
- **Order Fulfillment**: Status tracking, shipping labels, and delivery updates

#### Coupons
- **Discount Types**: Percentage and fixed amount discounts with realistic values
- **Rule Systems**: Spending limits, date ranges, usage restrictions, and customer targeting
- **Product Restrictions**: Include/exclude specific products or categories
- **Advanced Features**: Free shipping, stackable coupons, and first-time customer offers
- **Business Logic**: Comprehensive validation and application rules

## 🏗️ Architecture

### Modern Plugin Structure

```
easycommerce-fakerpress/
├── easycommerce-fakerpress.php           # Main plugin file
├── class-easycommerce-fakerpress.php     # Main plugin class with REST API
├── includes/
│   ├── Abstracts/                        # Abstract base classes
│   │   ├── Generator.php                 # Base generator class
│   │   └── REST_Controller.php           # Base REST controller
│   ├── Generators/                       # Data generators
│   │   ├── Product_Generator.php         # Product generation with EasyCommerce models
│   │   ├── Customer_Generator.php        # Customer generation with profiles
│   │   ├── Order_Generator.php           # Order generation with business logic
│   │   └── Coupon_Generator.php          # Coupon generation with rule systems
│   └── REST/
│       └── Controllers/                  # REST API controllers
│           ├── Product_Controller.php
│           ├── Customer_Controller.php
│           ├── Order_Controller.php
│           └── Coupon_Controller.php
├── src/
│   └── admin/
│       ├── components/                   # React components
│       │   ├── App.js                   # Main application
│       │   ├── GeneratorTabs.js         # Tab navigation
│       │   └── DataGenerator.js         # Generation interface
│       ├── styles.css                   # Tailwind CSS styles
│       └── index.js                     # Entry point
├── build/                               # Compiled assets
├── vendor/                              # Composer dependencies
├── node_modules/                        # NPM dependencies
├── composer.json                        # PHP dependencies and autoloading
├── package.json                         # Node.js dependencies and scripts
├── webpack.config.js                    # Build configuration
├── phpcs.xml.dist                       # PHP CodeSniffer rules
├── CLAUDE.md                            # Development instructions
└── README.md                            # This file
```

### Technology Stack

- **Backend**: PHP 7.4+, WordPress REST API, EasyCommerce Models
- **Frontend**: React 18, Tailwind CSS, Headless UI
- **Build Tools**: Webpack 5, Babel, PostCSS, Sass
- **Data Generation**: Faker PHP library with realistic patterns
- **Code Quality**: ESLint, Stylelint, PHPCS, PHPStan
- **Architecture**: PSR-4 autoloading, dependency injection, abstract patterns

### EasyCommerce Integration

The plugin seamlessly integrates with EasyCommerce through:

- **Model Integration**: Uses EasyCommerce Product, Customer, Order, and Coupon models
- **Database Abstraction**: Leverages EasyCommerce Database class for consistent data access
- **Business Logic**: Implements EasyCommerce business rules and validation
- **Relationship Management**: Maintains proper data relationships and integrity
- **Attribute System**: Creates proper product attributes and variations
- **Meta Data**: Uses EasyCommerce meta systems for extended data storage

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

### Development Workflow

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Install dependencies (`composer install && npm install`)
4. Make your changes following the coding standards
5. Run tests and linting (`npm run lint && composer run lint`)
6. Build assets (`npm run build`)
7. Commit your changes (`git commit -m 'Add amazing feature'`)
8. Push to the branch (`git push origin feature/amazing-feature`)
9. Open a Pull Request

### Coding Standards

- **PHP**: WordPress Coding Standards (WPCS) with PSR-4 autoloading
- **JavaScript**: ESLint with WordPress standards
- **CSS**: Stylelint with Tailwind CSS best practices
- **Documentation**: PHPDoc blocks and inline comments
- **Testing**: Unit tests for critical functionality

## 📝 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Al Amin Ahamed**
- Website: [alaminahamed.com](https://alaminahamed.com)
- GitHub: [@mralaminahamed](https://github.com/mralaminahamed)
- Email: me@alaminahamed.com

## 🆘 Support

For support and bug reports, please use the [GitHub Issues](https://github.com/mralaminahamed/easycommerce-fakerpress/issues) page.

## 🔄 Changelog

### v2.0.0 - Major Architecture Update
- ✨ Complete EasyCommerce model integration
- 🏗️ PSR-4 namespace restructuring with autoloading
- 🚀 REST API controllers replacing AJAX endpoints
- 📊 Enhanced data generation with realistic business logic
- 🎯 Comprehensive product attribute system
- 💼 Advanced customer profiling and loyalty systems
- 📦 Complete order lifecycle with payment and shipping
- 🎫 Sophisticated coupon rule engine
- 🔧 Abstract base classes for consistent patterns
- 📱 Modern React interface with improved UX

### v1.0.0 - Initial Release
- 🎉 Basic product, customer, order, and coupon generation
- 🖥️ React-based admin interface
- 🎨 Tailwind CSS styling
- 📚 WordPress standards compliance