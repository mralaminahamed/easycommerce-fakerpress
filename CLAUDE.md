# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with the EasyCommerce FakerPress plugin.

## Project Overview

EasyCommerce FakerPress is a modernized WordPress plugin that generates realistic fake ecommerce data (products, customers, orders, coupons) for testing and development. It integrates with the EasyCommerce plugin's data models and features a modern React-based admin interface with REST API architecture.

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

### Backend (PHP)
```
includes/
├── Abstracts/
│   ├── Abstract_Generator.php     # Base generator with common functionality
│   └── Abstract_Rest_Controller.php # Base REST controller
├── Controllers/
│   ├── Product_Controller.php     # REST endpoints for product generation
│   ├── Customer_Controller.php    # REST endpoints for customer generation
│   ├── Order_Controller.php       # REST endpoints for order generation
│   └── Coupon_Controller.php      # REST endpoints for coupon generation
└── Generators/
    ├── Product_Generator.php      # Product generation with EasyCommerce integration
    ├── Customer_Generator.php     # Customer generation with realistic profiles
    ├── Order_Generator.php        # Order generation with business logic
    └── Coupon_Generator.php       # Coupon generation with rules engine
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
npm run lint         # Lint JS and CSS
npm run fix          # Auto-fix linting issues
composer run lint    # PHP CodeSniffer (WPCS)
composer run fix     # PHP Code Beautifier
```

## EasyCommerce Integration

### Data Models Used
The plugin integrates with EasyCommerce's data models for proper data validation and relationships:

- **Product Model**: Creates products with attributes, variations, pricing, and inventory
- **Customer Model**: Creates customer profiles with billing/shipping addresses and loyalty tiers
- **Order Model**: Creates orders with items, payment methods, shipping, and taxes
- **Coupon Model**: Creates discount coupons with rules and restrictions
- **Attribute/Attribute_Value Models**: Manages product attributes and their values

### Key Integration Points
- **REST API Base**: `/wp-json/easycommerce-fakerpress/v1/`
- **Endpoints**:
  - `POST /products/generate` - Generate products
  - `POST /customers/generate` - Generate customers
  - `POST /orders/generate` - Generate orders
  - `POST /coupons/generate` - Generate coupons
- **Admin Interface**: WordPress admin page at `admin.php?page=easycommerce-fakerpress`
- **React Mount Point**: `#easycommerce-fakerpress-root`
- **Nonce**: `ecfp_nonce` for REST API security

## Data Generation Features

### Advanced Product Generation
- **Product Types**: Physical products, digital products, variations
- **Attribute System**: Creates proper EasyCommerce attributes and values
- **Categories & Brands**: WordPress taxonomy integration
- **Pricing Strategy**: Cost-based pricing with realistic margins
- **Inventory Management**: Stock tracking and low stock alerts
- **SEO Optimization**: Meta descriptions, keywords, and slugs

### Realistic Customer Profiles
- **Demographics**: Age-appropriate purchase patterns and preferences
- **Geographic Diversity**: International addresses with proper formatting
- **Loyalty Progression**: Customer lifetime value and tier advancement
- **Purchase History**: Historical order patterns based on customer age
- **Address Validation**: Proper billing and shipping address structures

### Business Logic Orders
- **Customer Matching**: Links orders to existing customers with fallback user creation
- **Product Selection**: Intelligent product selection with stock validation
- **Payment Methods**: Realistic payment method distribution
- **Order Status**: Proper order lifecycle status progression
- **Tax Calculation**: Location-based tax computation
- **Shipping Integration**: Multiple shipping methods and rates

### Advanced Coupon System
- **Discount Types**: Percentage, fixed amount, BOGO, free shipping
- **Rule Engine**: Customer restrictions, product limitations, usage limits
- **Expiration Logic**: Time-based and usage-based expiration
- **Code Generation**: Unique coupon codes with validation
- **A/B Testing**: Multiple coupon variants for testing

## WordPress Standards Compliance

- **Coding Standards**: WordPress Coding Standards (WPCS) compliance
- **Security**: Proper sanitization, validation, and nonce verification
- **Internationalization**: Text domain `easycommerce-fakerpress` for translations
- **Hooks System**: Uses WordPress actions and filters appropriately
- **User Capabilities**: Respects `manage_options` capability
- **Database**: Uses WordPress database abstraction layer

## Technical Architecture

### Abstract Base Classes
- **Abstract_Generator**: Common functionality for all data generators
- **Abstract_Rest_Controller**: Standardized REST API response handling
- **Template Method Pattern**: Consistent generation workflow across all generators

### Error Handling
- **Validation**: Input validation before data generation
- **Logging**: WordPress debug logging for troubleshooting
- **Graceful Degradation**: Fallback mechanisms for missing dependencies
- **User Feedback**: Clear error messages in admin interface

### Performance Optimization
- **Batch Processing**: Efficient bulk data generation
- **Memory Management**: Proper cleanup for large data sets
- **Database Optimization**: Optimized queries and transactions
- **Caching**: Strategic caching for repeated operations

## Development Workflow

1. **Setup**: Install dependencies with `composer install` and `npm install`
2. **Development**: Use `npm run dev` for hot reloading during development
3. **Testing**: Generate test data using the admin interface
4. **Code Quality**: Run `npm run lint` and `composer run lint` before commits
5. **Build**: Use `npm run build` for production deployment

## Dependencies

### PHP Dependencies (Composer)
- `fakerphp/faker`: Realistic fake data generation
- `EasyCommerce Plugin`: Data models and business logic (required)

### JavaScript Dependencies (NPM)
- `react`: UI component library
- `@headlessui/react`: Accessible UI components
- `tailwindcss`: Utility-first CSS framework
- `webpack`: Module bundler
- `babel`: JavaScript compiler

## Plugin Activation Requirements

- **EasyCommerce Plugin**: Must be active (dependency)
- **WordPress Version**: 5.0 or higher
- **PHP Version**: 7.4 or higher
- **Database**: MySQL 5.6 or higher

## Troubleshooting

### Common Issues
- **Model Not Found**: Ensure EasyCommerce plugin is active
- **Permission Denied**: Check user has `manage_options` capability
- **Generation Fails**: Verify database connectivity and plugin dependencies
- **Frontend Not Loading**: Run `npm run build` and check console for errors

### Debug Mode
Enable WordPress debug mode for detailed error logging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Development Memories

### EasyCommerce Integration Notes
- Always use EasyCommerce data models instead of direct database queries
- Validate product attributes exist before creating attribute relationships
- Ensure customer roles are properly assigned for order generation
- Use EasyCommerce Database class for code uniqueness validation
- Follow EasyCommerce's business logic patterns for realistic data

### Code Patterns
- All generators extend `Abstract_Generator` for consistency
- All REST controllers extend `Abstract_Rest_Controller`
- Use dependency injection for model instantiation
- Implement proper error handling and user feedback
- Follow PSR-4 autoloading standards

### Testing Strategy
- Generate small batches first to validate data structure
- Test with EasyCommerce plugin's admin interface for data verification
- Verify database relationships are properly established
- Check frontend rendering of generated data
- Validate REST API endpoints with proper authentication
