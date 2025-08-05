# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with the EasyCommerce FakerPress plugin.

## Project Overview

EasyCommerce FakerPress is a modernized WordPress plugin that generates realistic fake ecommerce data for testing and development. It integrates deeply with the EasyCommerce plugin's data models and features a modern React-based admin interface with comprehensive REST API architecture.

## Development Environment

- WordPress installation path: `/home/alamin/Local Sites/easy-commerce-development/app/public/`
- Plugin path: `wp-content/plugins/easycommerce-fakerpress/`
- Local by Flywheel development setup
- PHP 7.4+ required
- Node.js 16+ for frontend development

## Architecture Overview

### Version 2.0.0 Architecture
The plugin has been completely modernized with:
- **PSR-4 Namespace Structure**: All classes use `EasyCommerceFakerPress` namespace
- **Composer Autoloading**: Automatic class loading via Composer PSR-4 autoloader
- **REST API Controllers**: WordPress REST API endpoints replacing legacy AJAX
- **Abstract Base Classes**: Uniform patterns reducing code duplication by ~70%
- **EasyCommerce Model Integration**: Uses EasyCommerce plugin's data models instead of direct database queries

### Backend (PHP) - Current Structure
```
includes/
├── Abstracts/
│   ├── Generator.php              # Base generator with common functionality
│   └── REST_Controller.php        # Base REST controller
├── REST/Controllers/
│   ├── Product_REST_Controller.php           # REST endpoints for product generation
│   ├── Customer_REST_Controller.php          # REST endpoints for customer generation
│   ├── Order_REST_Controller.php             # REST endpoints for order generation
│   ├── Coupon_REST_Controller.php            # REST endpoints for coupon generation
│   ├── Product_Variation_REST_Controller.php # REST endpoints for product variations
│   ├── Shipping_Plan_REST_Controller.php     # REST endpoints for shipping plans
│   ├── Tax_REST_Controller.php               # REST endpoints for tax classes
│   ├── Transaction_REST_Controller.php       # REST endpoints for transactions
│   └── Cart_Session_REST_Controller.php      # REST endpoints for cart sessions
└── Generators/
    ├── Product_Generator.php           # Product generation with EasyCommerce integration
    ├── Customer_Generator.php          # Customer generation with realistic profiles
    ├── Order_Generator.php             # Order generation with business logic
    ├── Coupon_Generator.php            # Coupon generation with rules engine
    ├── Product_Variation_Generator.php # Product variations with attributes
    ├── Shipping_Plan_Generator.php     # Shipping methods and regional coverage
    ├── Tax_Generator.php               # Location-based tax classes and rates
    ├── Transaction_Generator.php       # Payment transaction history
    └── Cart_Session_Generator.php      # Shopping cart sessions and abandoned carts
```

### Frontend (React)
- React 18 components in `src/admin/components/`
- Modern tab-based interface using Headless UI
- Tailwind CSS for styling with WordPress-compatible design
- Webpack 5 build system with Babel for JSX/ES6+ support

## Development Commands

```bash
# Install dependencies
composer install
npm install

# Development
npm run dev          # Watch mode for development
npm run build        # Production build

# Code quality
npm run lint         # Lint JS and CSS with ESLint and Stylelint
npm run fix          # Auto-fix linting issues
composer run lint    # PHP CodeSniffer (WPCS) with PHPStan
composer run fix     # PHP Code Beautifier
```

## Coding Standards & Quality

### PHP Standards
- **WordPress Coding Standards (WPCS)**: Strict adherence to WordPress PHP standards
- **PHPStan**: Static analysis for type safety and code quality
- **PHPCS**: Code style enforcement with WordPress ruleset
- **PSR-4**: Namespace and autoloading standards
- **PHPDoc**: Comprehensive documentation for all methods and properties

### JavaScript Standards
- **ESLint**: JavaScript linting with WordPress and React rulesets
- **Prettier**: Code formatting consistency
- **React Best Practices**: Hooks, component patterns, and performance optimization

### CSS/SCSS Standards
- **Stylelint**: CSS/SCSS linting with WordPress standards
- **BEM Methodology**: Block Element Modifier naming convention
- **Tailwind CSS**: Utility-first approach with custom configurations

## EasyCommerce Integration

### Enhanced Data Models Used
The plugin integrates with EasyCommerce's comprehensive data model ecosystem:

#### Core Models
- **Product Model**: Products with attributes, variations, pricing, and inventory
- **Customer Model**: Customer profiles with billing/shipping addresses and loyalty tiers
- **Order Model**: Orders with items, payment methods, shipping, and taxes
- **Coupon Model**: Discount coupons with advanced rules and restrictions

#### Advanced Models (New in v2.0.0)
- **Product_Variation Model**: Product variations with attributes and pricing
- **Attribute/Attribute_Value Models**: Product attribute system management
- **Shipping_Plan Model**: Shipping methods with regional coverage and pricing tiers
- **Tax Model**: Location-based tax classes with multi-jurisdiction support
- **Transaction Model**: Payment transaction history with multiple gateways
- **Cart Model**: Shopping cart sessions with abandonment tracking

#### Utility Models
- **Location Model**: Geographic hierarchy (countries, states, cities) with currency data
- **Database Model**: Enhanced database operations with query optimization

### REST API Endpoints
- **Base URL**: `/wp-json/easycommerce-fakerpress/v1/`
- **Core Endpoints**:
  - `POST /products/generate` - Generate products with variations
  - `POST /customers/generate` - Generate customer profiles
  - `POST /orders/generate` - Generate orders with comprehensive data
  - `POST /coupons/generate` - Generate discount coupons
- **Advanced Endpoints** (New in v2.0.0):
  - `POST /product-variations/generate` - Generate product variations
  - `POST /shipping-plans/generate` - Generate shipping configurations
  - `POST /taxes/generate` - Generate tax classes and rates
  - `POST /transactions/generate` - Generate payment transactions
  - `POST /cart-sessions/generate` - Generate cart sessions and abandoned carts

### Admin Interface
- **Page**: `admin.php?page=easycommerce-fakerpress`
- **React Mount Point**: `#easycommerce-fakerpress-root`
- **Security**: `ecfp_nonce` for REST API authentication
- **Permissions**: `manage_options` capability required

## Code Patterns and Best Practices

### React Component Best Practices
- **Localization Best Practices**:
  - Use WordPress native component for localize text in React component

[Rest of the file remains unchanged...]