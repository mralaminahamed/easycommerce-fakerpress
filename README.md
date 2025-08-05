# EasyCommerce FakerPress

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.txt)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-8892BF.svg)](https://php.net/)
[![Version](https://img.shields.io/badge/Version-1.0.0-green.svg)]()

A comprehensive WordPress plugin that generates realistic fake ecommerce data for EasyCommerce stores. Features 10 specialized generators, advanced parameter configuration, WordPress admin color integration, and modern React interface with comprehensive business logic modeling.

## ✨ Features

### 🗂️ 10 Specialized Generators
- 🛍️ **Products**: Advanced products with attributes, variations, categories, and pricing strategies
- 👥 **Customers**: Comprehensive customer profiles with demographics, purchase history, and loyalty tiers
- 📦 **Orders**: Complete orders with payment processing, shipping, tax calculations, and item metadata
- 🎫 **Coupons**: Sophisticated discount coupons with usage limits, restrictions, and validity periods
- 🔄 **Product Variations**: Detailed product variations with attribute systems and inventory tracking
- 🚚 **Shipping Plans**: Shipping methods with regional coverage, carrier selection, and cost calculations
- 💰 **Tax Management**: Multi-jurisdiction tax classes with location-based rates and rule systems
- 💳 **Transactions**: Payment transaction history with multiple gateways and status distributions
- 🛒 **Cart Sessions**: Shopping cart sessions with abandonment scenarios and recovery simulation
- 🌍 **Location Data**: Comprehensive location hierarchy (countries, states, cities) with coordinates

### 🎛️ Advanced Parameter System
- **Dynamic Parameter Configuration**: Each generator has extensive customization options
- **Nested Object Parameters**: Complex parameter structures with proper state management
- **Smart Defaults**: Intelligent default values based on EasyCommerce best practices
- **Parameter Validation**: Client-side and server-side validation with proper error handling
- **Conditional Logic**: Parameters that adapt based on other selections

### 🎨 Modern User Interface
- **WordPress Admin Color Integration**: Automatically adapts to user's chosen admin color scheme
- **React 18 + Tailwind CSS**: Modern, responsive interface with excellent performance
- **Tabbed Navigation**: Organized generator access with progress tracking
- **Enhanced Form Controls**: Smart form fields with proper labeling and validation
- **Real-time Feedback**: Live generation progress with detailed status updates

### 🏗️ Technical Excellence
- **PSR-4 Architecture**: Modern PHP with namespacing, autoloading, and abstract base classes
- **REST API Controllers**: Clean API design replacing legacy AJAX endpoints
- **EasyCommerce Integration**: Native model usage with proper business logic enforcement
- **WordPress Standards**: Full WPCS compliance with security best practices
- **Extensible Design**: Hook system and abstract patterns for easy customization

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
2. Choose from the comprehensive tabbed interface:

#### Core Generators
- **Products**: Generate products with attributes, variations, categories, pricing, and inventory
- **Customers**: Create customer accounts with demographics, purchase history, and loyalty tiers
- **Orders**: Generate complete orders with payment processing, shipping, and tax calculations
- **Coupons**: Create discount coupons with usage limits, restrictions, and validity periods

#### Enhanced Generators
- **Product Variations**: Create detailed product variations with attribute systems
- **Shipping Plans**: Generate shipping methods with regional coverage and cost calculations
- **Tax Management**: Create tax classes with multi-jurisdiction rates and rules
- **Transactions**: Generate payment transaction history with multiple gateways
- **Cart Sessions**: Create shopping cart sessions with abandonment scenarios
- **Location Data**: Populate location hierarchy for geographic functionality

3. **Configure Parameters**: Use advanced parameter controls for customized data generation
4. **Monitor Progress**: Watch real-time generation progress with detailed feedback
5. **Adaptive UI**: Interface automatically matches your WordPress admin color scheme

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
├── class-easycommerce-fakerpress.php     # Main plugin class with color integration
├── includes/
│   ├── Abstracts/                        # Abstract base classes
│   │   ├── Generator.php                 # Base generator with parameter handling
│   │   └── REST_Controller.php           # Base REST controller with validation
│   ├── Generators/                       # 10 Specialized generators
│   │   ├── Product_Generator.php         # Products with attributes & variations
│   │   ├── Customer_Generator.php        # Comprehensive customer profiles
│   │   ├── Order_Generator.php           # Orders with item metadata & locations
│   │   ├── Coupon_Generator.php          # Coupons with advanced rule systems
│   │   ├── Product_Variation_Generator.php # Product variations & attributes
│   │   ├── Shipping_Plan_Generator.php   # Shipping methods & regional coverage
│   │   ├── Tax_Generator.php             # Tax classes & location-based rates
│   │   ├── Transaction_Generator.php     # Payment transaction history
│   │   ├── Cart_Session_Generator.php    # Cart sessions & abandonment
│   │   └── Location_Generator.php        # Geographic location hierarchy
│   └── REST/
│       └── Controllers/                  # REST API controllers with parameters
│           ├── Product_REST_Controller.php
│           ├── Customer_REST_Controller.php
│           ├── Order_REST_Controller.php
│           ├── Coupon_REST_Controller.php
│           ├── Product_Variation_REST_Controller.php
│           ├── Shipping_Plan_REST_Controller.php
│           ├── Tax_REST_Controller.php
│           ├── Transaction_REST_Controller.php
│           ├── Cart_Session_REST_Controller.php
│           └── Location_REST_Controller.php
├── src/
│   └── admin/
│       ├── components/                   # React components with advanced UX
│       │   ├── App.jsx                   # Main app with 10-tab navigation
│       │   ├── GeneratorBase.jsx         # Enhanced form controls & validation
│       │   └── Generators/               # Individual generator components
│       │       ├── ProductGenerator.jsx  # Core generators
│       │       ├── CustomerGenerator.jsx
│       │       ├── OrderGenerator.jsx
│       │       ├── CouponGenerator.jsx
│       │       ├── ProductVariationGenerator.jsx # Enhanced generators
│       │       ├── ShippingPlanGenerator.jsx
│       │       ├── TaxGenerator.jsx
│       │       ├── TransactionGenerator.jsx
│       │       ├── CartSessionGenerator.jsx
│       │       └── LocationGenerator.jsx
│       ├── styles.css                   # Tailwind with WordPress color integration
│       └── index.js                     # Entry point with color scheme support
├── build/                               # Compiled assets with CSS variables
├── vendor/                              # Composer dependencies
├── node_modules/                        # NPM dependencies
├── composer.json                        # PSR-4 autoloading & dependencies
├── package.json                         # Modern build tools & dependencies
├── webpack.config.js                    # Advanced build configuration
├── tailwind.config.js                   # WordPress admin color integration
├── phpcs.xml.dist                       # PHP CodeSniffer rules
├── CLAUDE.md                            # Comprehensive development guide
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

### v1.0.0 - Initial Release
**Release Date: August 5, 2025**

#### 🎉 Complete EasyCommerce Data Generation Solution

**🗂️ 10 Specialized Generators:**
- **Products**: Advanced generation with attributes, variations, categories, pricing, and inventory management
- **Customers**: Comprehensive profiles with demographics, purchase history, and loyalty tier progression
- **Orders**: Complete orders with payment processing, shipping, tax calculations, and item metadata
- **Coupons**: Sophisticated discount system with usage limits, restrictions, and validity periods
- **Product Variations**: Detailed variations with attribute systems and inventory tracking
- **Shipping Plans**: Methods with regional coverage, carrier selection, and cost calculations
- **Tax Management**: Multi-jurisdiction tax classes with location-based rates and rule systems
- **Transactions**: Payment transaction history with multiple gateways and status distributions
- **Cart Sessions**: Shopping cart abandonment scenarios and recovery simulation
- **Location Data**: Geographic hierarchy (countries, states, cities) with coordinates and timezone support

#### 🎨 Modern User Experience:
- **WordPress Admin Color Integration**: Automatic adaptation to user's chosen admin color scheme
- **Advanced Parameter System**: Dynamic, nested parameters with intelligent validation and smart defaults
- **Enhanced Form Controls**: Modern React interface with smart form fields and proper labeling
- **Tabbed Navigation**: Organized 10-generator interface with progress tracking
- **Real-time Feedback**: Live generation progress with detailed status updates and error handling
- **Responsive Design**: Mobile-optimized interface with improved accessibility

#### 🏗️ Enterprise Architecture:
- **PSR-4 Architecture**: Modern PHP with namespacing, autoloading, and abstract base classes
- **REST API Controllers**: 10 clean API controllers with comprehensive parameter schemas
- **EasyCommerce Integration**: Native model usage with Order_Item_Meta and location system integration
- **WordPress Standards**: Full WPCS compliance with security best practices and proper internationalization
- **Extensible Design**: Hook system and abstract patterns for easy customization and extension

#### 🔧 Technical Excellence:
- **Modern Build System**: React 18, Tailwind CSS, Webpack 5 with CSS variable integration
- **Advanced Validation**: Client-side and server-side parameter validation with proper error handling
- **State Management**: Complex form handling with nested object parameter support
- **Performance Optimization**: Efficient database operations and memory management
- **Developer Experience**: Comprehensive documentation, modern tooling, and extensive customization hooks