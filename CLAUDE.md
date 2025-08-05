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

## Data Generation Features

### Advanced Product Generation
- **Product Types**: Physical products, digital products, variations
- **Attribute System**: Creates proper EasyCommerce attributes and values with validation
- **Variation System**: Complex product variations with attribute combinations
- **Categories & Brands**: WordPress taxonomy integration with realistic hierarchies
- **Pricing Strategy**: Cost-based pricing with realistic margins and sale prices
- **Inventory Management**: Stock tracking, low stock alerts, and stock limits
- **SEO Optimization**: Meta descriptions, keywords, slugs, and structured data

### Comprehensive Customer Profiles
- **Demographics**: Age-appropriate purchase patterns and preferences
- **Geographic Diversity**: International addresses with proper formatting and validation
- **Loyalty Progression**: Customer lifetime value and tier advancement algorithms
- **Purchase History**: Historical order patterns based on customer demographics
- **Address Validation**: Proper billing and shipping address structures
- **Customer Journey**: Realistic progression from registration to loyal customer

### Advanced Order Management
- **Customer Matching**: Links orders to existing customers with intelligent fallback
- **Product Selection**: Stock validation and realistic product combinations
- **Payment Methods**: Distribution based on regional preferences
- **Order Status**: Proper order lifecycle with realistic timing
- **Tax Calculation**: Multi-jurisdiction tax computation with compound rates
- **Shipping Integration**: Multiple methods with regional pricing and restrictions
- **Order Items**: Detailed line items with meta data and variations

### Sophisticated Coupon System
- **Discount Types**: Percentage, fixed amount, BOGO, free shipping, tiered discounts
- **Rule Engine**: Customer restrictions, product limitations, usage limits, date ranges
- **Expiration Logic**: Time-based and usage-based expiration with grace periods
- **Code Generation**: Unique coupon codes with customizable patterns and validation
- **A/B Testing**: Multiple coupon variants for conversion optimization

### Enhanced Transaction Tracking
- **Payment Gateways**: Stripe, PayPal, Square, Authorize.Net, and 6+ others
- **Transaction Types**: Payments, refunds, adjustments, fees, commissions
- **Realistic IDs**: Gateway-specific transaction ID patterns
- **Status Distribution**: Realistic success/failure rates by transaction type
- **Multi-Currency**: Support for 7+ major currencies with proper formatting

### Advanced Cart Analytics
- **Cart Sessions**: Realistic shopping cart behavior simulation
- **Abandonment Patterns**: Time-based abandonment with customer segmentation
- **Recovery Tracking**: Email reminder campaigns with effectiveness metrics
- **Value Analysis**: Cart value distribution and trending analysis
- **Customer Behavior**: Registered vs guest abandonment patterns

## WordPress Standards Compliance

- **Coding Standards**: Strict WordPress Coding Standards (WPCS) compliance
- **Security**: Comprehensive sanitization, validation, and nonce verification
- **Internationalization**: Full i18n support with `easycommerce-fakerpress` text domain
- **Hooks System**: Proper use of WordPress actions and filters with custom hooks
- **User Capabilities**: Granular permission system respecting WordPress roles
- **Database**: WordPress database abstraction layer with prepared statements
- **Performance**: Query optimization, caching strategies, and memory management

## Technical Architecture

### Abstract Base Classes
- **Generator**: Common functionality for all data generators with template method pattern
- **REST_Controller**: Standardized REST API response handling with error management
- **Validation**: Input validation and sanitization with WordPress standards
- **Logging**: Integrated WordPress debug logging with context information

### Design Patterns
- **Template Method**: Consistent generation workflow across all generators
- **Factory Pattern**: Dynamic generator instantiation based on data type
- **Strategy Pattern**: Configurable generation strategies per data type
- **Observer Pattern**: Event-driven generation with progress tracking

### Error Handling & Validation
- **Input Validation**: Comprehensive validation before data generation
- **Type Safety**: PHPStan level 8 compliance with strict typing
- **Exception Handling**: Graceful error handling with user-friendly messages
- **Logging**: Detailed error logging with context and stack traces
- **Fallback Mechanisms**: Graceful degradation for missing dependencies

### Performance Optimization
- **Batch Processing**: Efficient bulk data generation with memory management
- **Query Optimization**: Optimized database queries with proper indexing
- **Caching**: Strategic caching for repeated operations and lookups
- **Memory Management**: Proper cleanup for large data sets with garbage collection
- **Database Transactions**: Atomic operations for data consistency

## Development Workflow

1. **Setup**: Install dependencies with `composer install` and `npm install`
2. **Development**: Use `npm run dev` for hot reloading during development
3. **Code Quality**: Run linting and static analysis before commits
4. **Testing**: Generate test data and validate with EasyCommerce interface
5. **Build**: Use `npm run build` for production deployment
6. **Documentation**: Update PHPDoc and inline documentation

### Pre-Commit Checklist
- [ ] Run `composer run lint` (PHPCS + PHPStan)
- [ ] Run `npm run lint` (ESLint + Stylelint)
- [ ] Test data generation functionality
- [ ] Verify REST API endpoints
- [ ] Check error handling and validation
- [ ] Update documentation if needed

## Dependencies

### PHP Dependencies (Composer)
- `fakerphp/faker`: Realistic fake data generation with localization
- `EasyCommerce Plugin`: Data models and business logic (required dependency)
- `WordPress`: 5.0+ with proper database abstraction

### JavaScript Dependencies (NPM)
- `react`: UI component library with hooks
- `@headlessui/react`: Accessible UI components
- `tailwindcss`: Utility-first CSS framework
- `webpack`: Module bundler with optimization
- `babel`: JavaScript compiler with modern syntax support

### Development Dependencies
- `phpstan/phpstan`: PHP static analysis
- `squizlabs/php_codesniffer`: PHP code style checking
- `wp-coding-standards/wpcs`: WordPress coding standards
- `eslint`: JavaScript linting
- `stylelint`: CSS/SCSS linting

## Plugin Requirements

- **EasyCommerce Plugin**: Must be active (critical dependency)
- **WordPress Version**: 5.0 or higher
- **PHP Version**: 7.4 or higher (8.0+ recommended)
- **Database**: MySQL 5.6 or higher
- **Memory Limit**: 256MB+ recommended for large data generation
- **User Permissions**: `manage_options` capability

## Troubleshooting

### Common Issues
- **Model Not Found**: Ensure EasyCommerce plugin is active and properly configured
- **Permission Denied**: Verify user has `manage_options` capability
- **Generation Fails**: Check database connectivity and plugin dependencies
- **Frontend Not Loading**: Run `npm run build` and check browser console
- **Memory Issues**: Increase PHP memory limit for large batch operations

### Debug Mode
Enable comprehensive WordPress debug mode:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

### Performance Debugging
```php
define('SAVEQUERIES', true);
define('WP_DEBUG_LOG', true);
```

## Development Best Practices

### Code Architecture
- **Follow existing structure**: Always align new classes with abstract base classes
- **Consistent naming**: Use WordPress and PSR-4 naming conventions
- **Type safety**: Implement strict typing with PHPStan compliance
- **Documentation**: Comprehensive PHPDoc for all public methods
- **Error handling**: Implement proper exception handling and user feedback

### Generator Development
- All generators MUST extend `EasyCommerceFakerPress\Abstracts\Generator`
- Implement required `generate_single_item()` method
- Include `get_resource_type()` method for identification
- Follow template method pattern for consistency
- Use EasyCommerce models instead of direct database queries
- Implement proper validation and error handling

### Controller Development
- All REST controllers MUST extend `EasyCommerceFakerPress\Abstracts\REST_Controller`
- Place in `includes/REST/Controllers/` directory
- Use `_REST_Controller` suffix in class names
- Implement required abstract methods:
  - `get_rest_base()`
  - `get_generator_instance()`
  - `get_resource_type()`
- Follow WordPress REST API standards

### Testing Strategy
- Generate small batches first to validate data structure
- Test with EasyCommerce plugin's admin interface for data verification
- Verify database relationships are properly established
- Check frontend rendering of generated data
- Validate REST API endpoints with proper authentication
- Test error conditions and edge cases
- Verify performance with large data sets

### Security Considerations
- Always sanitize and validate input data
- Use WordPress nonces for CSRF protection
- Respect user capabilities and permissions
- Implement proper SQL injection prevention
- Validate file uploads and media handling
- Use WordPress security functions and filters

### Performance Guidelines
- Implement batch processing for large data sets
- Use database transactions for data consistency
- Optimize queries and avoid N+1 problems
- Implement proper caching strategies
- Monitor memory usage during generation
- Use WordPress object cache where appropriate

## EasyCommerce Integration Notes

### Critical Integration Points
- Always use EasyCommerce data models instead of direct database queries
- Validate product attributes exist before creating relationships
- Ensure customer roles are properly assigned for order generation
- Use EasyCommerce Database class for code uniqueness validation
- Follow EasyCommerce's business logic patterns for realistic data
- Respect EasyCommerce's data validation and constraints
- Integrate with EasyCommerce's event system and hooks

### Data Relationship Management
- Maintain proper foreign key relationships
- Validate data integrity across related models
- Handle cascading operations appropriately
- Implement proper data cleanup and orphan handling
- Respect EasyCommerce's data lifecycle management

### Business Logic Compliance
- Follow EasyCommerce's pricing calculation logic
- Implement proper tax calculation workflows
- Respect inventory management rules and constraints
- Handle shipping calculation integration properly
- Maintain coupon usage tracking and validation
