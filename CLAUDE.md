# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with the EasyCommerce FakerPress plugin.

## Project Overview

EasyCommerce FakerPress is a modernized WordPress plugin that generates realistic fake ecommerce data for testing and development. It integrates deeply with the EasyCommerce plugin's data models and features a modern React-based admin interface with comprehensive REST API architecture.

## Development Environment

- WordPress installation path: `/Users/alamin/Sites/easy-commerce-development/wp-content/plugins/easycommerce-fakerpress/`
- Plugin path: `wp-content/plugins/easycommerce-fakerpress/`
- Local by Flywheel development setup
- PHP 7.4+ required (8.0+ recommended)
- Node.js 16+ for frontend development
- **Package Manager Guidelines**:
  - Always use yarn as package manager instead of npm (version 4.9.2 specified in packageManager field)

## Architecture Overview

### Version 1.0.0 Architecture (Current)
The plugin has been completely modernized with:
- **PSR-4 Namespace Structure**: All classes use `EasyCommerceFakerPress` namespace
- **Composer Autoloading**: Automatic class loading via Composer PSR-4 autoloader
- **REST API Controllers**: WordPress REST API endpoints replacing legacy AJAX
- **Abstract Base Classes**: Uniform patterns reducing code duplication by ~70%
- **EasyCommerce Model Integration**: Uses EasyCommerce plugin's data models instead of direct database queries
- **WordPress Scripts Integration**: Uses @wordpress/scripts for modern build tooling

### Backend (PHP) - Current Structure
```
includes/
├── Abstracts/
│   ├── Generator.php              # Base generator with common functionality
│   └── REST_Controller.php        # Base REST controller
├── Controllers/
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

### Frontend (React) - Current Structure
```
src/admin/components/
├── App.jsx                    # Main router configuration with createHashRouter
├── GeneratorBase.jsx          # Shared base component for all generators
├── Pages/                     # Route-based page components
│   ├── RootLayout.jsx        # Main layout wrapper with header and Outlet
│   ├── HomePage.jsx          # Generator selection grid with categories
│   └── GeneratorPage.jsx     # Individual generator page with sidebar
└── Generators/               # Data generation components
    ├── ProductGenerator.jsx
    ├── CustomerGenerator.jsx
    ├── OrderGenerator.jsx
    ├── CouponGenerator.jsx
    ├── ProductVariationGenerator.jsx
    ├── ShippingPlanGenerator.jsx
    ├── TaxGenerator.jsx
    ├── TransactionGenerator.jsx
    ├── CartSessionGenerator.jsx
    └── LocationGenerator.jsx
```

#### Modern React Architecture Features
- **React 18** with modern hooks and concurrent features
- **React Router v7** with createHashRouter for optimal WordPress compatibility
- **Component-Based Architecture** with clear separation of concerns
- **Route-Based Code Splitting** for improved performance
- **Headless UI Components** for accessible, unstyled UI primitives
- **Tailwind CSS** for utility-first styling with WordPress design system
- **Webpack 5** build system with Babel for modern JavaScript features

## Development Commands

```bash
# Install dependencies
composer install
yarn install         # Always use yarn as package manager

# Development
yarn dev             # Watch mode for development (alias for yarn start)
yarn start           # Start development server with hot reloading
yarn build           # Production build
yarn watch           # Alias for yarn start

# Code quality
yarn lint            # Lint JS, CSS, and PHP (runs all linters)
yarn lint:js         # ESLint for JavaScript files
yarn lint:css        # Stylelint for CSS/SCSS files  
yarn lint:php        # PHPCS for PHP files
yarn fix             # Auto-fix JS formatting and PHP code style
yarn format          # Format JS files with wp-scripts
yarn phpstan         # Run PHPStan static analysis
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
- **React Router v7**: Modern data router patterns with createHashRouter
- **Component Architecture**: Clear separation between Pages, Generators, and Base components

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

#### Advanced Models
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
- **Advanced Endpoints**:
  - `POST /product-variations/generate` - Generate product variations
  - `POST /shipping-plans/generate` - Generate shipping configurations
  - `POST /taxes/generate` - Generate tax classes and rates
  - `POST /transactions/generate` - Generate payment transactions
  - `POST /cart-sessions/generate` - Generate cart sessions and abandoned carts

### Admin Interface
- **Page**: `admin.php?page=easycommerce-fakerpress`
- **React Mount Point**: `#easycommerce-fakerpress-root`
- **Security**: `easycommerce_fakerpress_nonce` for REST API authentication
- **Permissions**: `manage_options` capability required

## Code Patterns and Best Practices

### React Component Best Practices
- **Localization Best Practices**:
  - Use WordPress native component for localize text in React component
  - Always add translator comments for sprintf functions: `/* translators: %s: description */`
- **Component Organization**:
  - Pages: Route-based components that handle specific URLs
  - Generators: Data generation components that extend GeneratorBase
  - Base Components: Shared/reusable components across the application

### React Router v7 Architecture
- **createHashRouter**: Used for WordPress admin compatibility with hash-based routing
- **RouterProvider**: Top-level router provider wrapping the entire application
- **Nested Routes**: Layout routes with child pages using Outlet component
- **Route Object Pattern**: Declarative route configuration over component-based routing

#### Router Structure Pattern
```javascript
const router = createHashRouter([
  {
    path: '/',
    element: <RootLayout />,
    children: [
      {
        index: true,
        element: <HomePage />,
      },
      {
        path: 'generator/:route',
        element: <GeneratorPage />,
      },
    ],
  },
]);
```

#### Component Import Strategy
- **Pages Components**: Import from `./Pages/` directory for route components
- **Generator Components**: Import from `./Generators/` for data generation
- **Shared Logic**: Export/import generators array between HomePage and GeneratorPage


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

1. **Setup**: Install dependencies with `composer install` and `yarn install`
2. **Development**: Use `yarn dev` for hot reloading during development
3. **Code Quality**: Run linting and static analysis before commits
4. **Testing**: Generate test data and validate with EasyCommerce interface
5. **Build**: Use `yarn build` for production deployment
6. **Documentation**: Update PHPDoc and inline documentation

## Current Architectural Features (v1.0.0)

### Modern Frontend Architecture
- **Component Separation**: Clean App.jsx with focused Page components (29 lines)
- **React Router v7**: Uses createHashRouter with data router patterns for WordPress compatibility
- **Pages Directory**: Organized route-based components (RootLayout, HomePage, GeneratorPage)
- **Generators Directory**: Dedicated generator components with consistent naming
- **WordPress Scripts**: Integrated @wordpress/scripts for optimal WordPress development

### Performance Features
- **WordPress Scripts Optimization**: Built-in code splitting and optimization
- **Modern React 18**: Concurrent features and improved rendering
- **Tailwind CSS**: Utility-first CSS framework with purging for smaller bundles
- **Sass Support**: Advanced CSS preprocessing capabilities
- **PostCSS**: Advanced CSS processing with Autoprefixer

### Developer Experience
- **WordPress Standards**: Full compliance with WordPress coding standards
- **TypeScript Support**: Optional static typing for enhanced development
- **Playwright Testing**: Modern end-to-end testing capabilities
- **Multiple Linting**: ESLint, Stylelint, and PHPCS integration
- **PHPStan Analysis**: Advanced PHP static analysis with WordPress stubs

### Pre-Commit Checklist
- [ ] Run `composer run lint` (PHPCS + PHPStan)
- [ ] Run `yarn lint` (ESLint + Stylelint + PHPCS)
- [ ] Run `yarn build` to ensure production build works
- [ ] Test data generation functionality
- [ ] Verify REST API endpoints
- [ ] Check error handling and validation
- [ ] Update documentation if needed

## Dependencies

### PHP Dependencies (Production)
- `fakerphp/faker`: 1.23+ - Realistic fake data generation with localization
- `bluemmb/faker-picsum-photos-provider`: 2.0+ - Lorem Picsum image provider for Faker
- `EasyCommerce Plugin`: Data models and business logic (required dependency)
- `WordPress`: 5.0+ with proper database abstraction
- `PHP`: 7.4+ (8.0+ recommended)

### PHP Development Dependencies
- `phpstan/phpstan`: 2.1+ - PHP static analysis tool
- `squizlabs/php_codesniffer`: 3.7+ - PHP code style checking
- `wp-coding-standards/wpcs`: 3.1+ - WordPress coding standards
- `phpunit/phpunit`: 9.6+ - PHP unit testing framework
- `wp-phpunit/wp-phpunit`: 6.8+ - WordPress-specific PHPUnit utilities
- `php-stubs/wordpress-stubs`: 6.4+ - WordPress function stubs for static analysis

### JavaScript Dependencies (Production)
- `react`: 18.x - UI component library with hooks and concurrent features
- `react-router-dom`: 7.7.1 - Modern routing library with data router patterns
- `@headlessui/react`: 2.2.7 - Accessible, unstyled UI components
- `@heroicons/react`: 2.2.0 - Beautiful hand-crafted SVG icons
- `@wordpress/i18n`: 6.0.0 - WordPress internationalization utilities
- `@wordpress/api-fetch`: 7.27.0 - WordPress REST API fetch wrapper
- `@wordpress/element`: 6.27.0 - WordPress React element wrapper
- `@wordpress/dom-ready`: 4.27.0 - WordPress DOM ready utility

### JavaScript Development Dependencies
- `@wordpress/scripts`: 30.20.0 - WordPress build tooling and configuration
- `tailwindcss`: 3.3.5 - Utility-first CSS framework
- `@playwright/test`: 1.54.2 - End-to-end testing framework
- `sass`: 1.89.2 - CSS preprocessor
- `typescript`: 5.9.2 - Static type checking

### Integrated Development Tools
- `@wordpress/scripts`: Complete WordPress development toolchain
- `@wordpress/eslint-plugin`: WordPress-specific ESLint rules
- `@wordpress/stylelint-config`: WordPress CSS standards
- `phpstan/phpstan`: Advanced PHP static analysis
- `wp-coding-standards/wpcs`: WordPress PHP coding standards
- `@playwright/test`: Modern end-to-end testing

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
- **Frontend Not Loading**: Run `yarn build` and check browser console
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
- Place in `includes/Controllers/` directory
- Use `_REST_Controller` suffix in class names
- Implement required abstract methods:
  - `get_rest_base()`
  - `get_generator_instance()`
  - `get_resource_type()`
- Follow WordPress REST API standards

### Frontend Component Development
#### Page Components (src/admin/components/Pages/)
- **RootLayout.jsx**: Contains shared layout elements, uses Outlet for child routes
- **HomePage.jsx**: Generator selection grid, contains generators configuration array
- **GeneratorPage.jsx**: Individual generator pages with sidebar navigation
- All page components should be route-focused and handle specific URL patterns

#### Generator Components (src/admin/components/Generators/)
- All generator components MUST extend or use `GeneratorBase` component
- Place in `src/admin/components/Generators/` directory
- Use descriptive names ending with `Generator` (e.g., `ProductGenerator.jsx`)
- Implement data generation logic with proper error handling
- Follow WordPress i18n best practices for all user-facing strings

#### Component Architecture Guidelines
- **Single Responsibility**: Each component should have one clear purpose
- **Props Interface**: Use clear, descriptive prop names with proper TypeScript-style comments
- **State Management**: Use React hooks for local state, avoid prop drilling
- **Error Boundaries**: Implement proper error handling for data generation failures
- **Loading States**: Always provide loading indicators during API calls
- **Accessibility**: Ensure all components are keyboard navigable and screen reader friendly

#### Import/Export Patterns
```javascript
// Page components - default export
export default function HomePage() { ... }

// Shared data - named export
export { generators };

// Import patterns
import HomePage from './Pages/HomePage';
import { generators } from './Pages/HomePage';
```

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
- use composer commands to check code qualityand linting