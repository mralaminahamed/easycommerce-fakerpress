# Agent Instructions for easycommerce-fakerpress

## Build/Lint/Test Commands

### JavaScript/React

- Build: `yarn build` or `npm run build`
- Dev server: `yarn start` or `npm run start`
- Update packages: `yarn packages-update`

### PHP

- Test all: `composer test` or `phpunit`
- Test single file: `phpunit tests/php/src/SpecificTest.php`
- Test single method: `phpunit --filter TestClassName::testMethodName`
- Coverage: `composer test:coverage`
- Lint: `composer lint` or `composer phpcs`
- Static analysis: `composer analyse` or `composer phpstan`
- Format: `composer format` or `composer phpcbf`

## Code Style Guidelines

### PHP

- Follow WordPress coding standards (WPCS)
- Use PSR-4 autoloading (`EasyCommerceFakerPress\` namespace)
- PHP 7.4+ minimum, support up to current WordPress requirements
- Class names: PascalCase (e.g., `ProductGenerator`)
- Method/variable names: camelCase
- File names: snake_case with hyphens (e.g., `product-generator.php`)
- Use `wc_get_template*` functions for loading templates, never create custom class or functions
- PHPDoc comments for all classes, methods, and properties
- Dependency injection over global state
- Proper error handling with try/catch and WP_Error

### JavaScript/React

- ES6+ syntax with WordPress ESLint rules
- Functional components with hooks (no class components)
- Import order: WordPress core, external libraries, local components
- camelCase for variables/functions, PascalCase for components
- Use Tailwind CSS v4 for styling with @theme and @utility directives
- Proper i18n with `@wordpress/i18n`
- Async/await for API calls with try/catch error handling
- TypeScript for type safety in all files
- Node.js 20+ required for Tailwind v4 and build process

### General

- Conventional commits: `type(scope): description`
- No console.log in production code (warn level in ESLint)
- Prefer const over let/var, arrow functions
- Single quotes for strings (PHP), template literals for JS
- 4 spaces indentation (PHP), tabs (JS per WordPress standards)

## Project Structure & Architecture

### Generator System

- **PHP Generators**: Located in `includes/Generators/` - Handle data creation logic
- **Controllers**: Located in `includes/Controllers/` - REST API endpoints for generators
- **React Components**: Located in `src/admin/components/Generators/` - Frontend UI for generators
- **Data Flow**: React components → REST API → Controllers → Generators → EasyCommerce models

### Key Patterns Established

- **Generator Data Structures**: All generators now provide complete data structures matching EasyCommerce model expectations
- **Model Integration**: Generators properly use EasyCommerce models (Product, Customer, Order, etc.) with correct data formats
- **Meta Data Management**: Related data properly stored using meta models (Order_Meta, Product_Meta, etc.)
- **Dependency Injection**: Controllers use generator instances via `get_generator_instance()`
- **Parameter Validation**: REST endpoints validate parameters using JSON Schema configurations
- **Error Handling**: Consistent WP_Error usage with proper error codes and user-friendly messages

### Recent Improvements (v2.0.3 - December 15, 2025)

- **Product Review Generator**: Added new generator for creating realistic product reviews with ratings
- **Review Rating System**: Implemented weighted rating distribution favoring higher ratings (realistic patterns)
- **WordPress Comments Integration**: Leverages WordPress comment system for review storage
- **Verified Purchase Tracking**: Reviews can be marked as verified purchases for enhanced credibility
- **Order Generator Data Structure Fix**: Corrected Order generator to match EasyCommerce Order model expectations
- **Order Notes Integration**: Added proper order notes creation using Order_Notes model
- **Comprehensive Model Review**: Validated all 11 EasyCommerce models for proper generator integration
- **Controller Pattern Alignment**: Updated Product_Review controller to match existing controller patterns
- **Complete Model Validation**: Validated all generators against EasyCommerce models for data consistency
- **API Schema Consistency**: Added proper resource-specific properties and parameter validation

## Copilot Instructions

- Follow WordPress PHP coding standards for all PHP files
- Use ES6+ syntax for JavaScript/React code in `src/` directory
- Use Tailwind CSS for styling in frontend components
- Ensure code is linted and formatted before committing
- Write clear, self-documenting code and add comments where necessary
- Prefer functional components and hooks in React
- Use dependency injection and avoid global state in PHP classes
- Keep functions and components small and focused
- Avoid duplicating code; use shared utilities/components
- Add or update tests for new features and bug fixes
- Use PHPUnit for PHP tests and Jest/React Testing Library for JS/React tests
- Update README.md for major changes or new features
- Document public APIs and important functions/classes
- Use Copilot to suggest code, but always review and test before committing
- Do not accept Copilot suggestions that violate project standards or introduce security risks
- Refactor Copilot-generated code to match project conventions if needed

### Generator Development Guidelines

- **Model Integration**: Always use appropriate EasyCommerce model classes for data creation
- **Data Structure Alignment**: Ensure generator `create()` calls match EasyCommerce model expectations exactly
- **Meta Data Handling**: Use meta arrays for additional data not handled by base model properties
- **Related Model Usage**: Implement proper relationships using dedicated models (e.g., Order_Notes for order comments)
- **Parameter Dependencies**: Use `dependsOn` in React parameter configs for conditional fields
- **API Endpoint Naming**: REST bases should be plural (e.g., `orders`, `products`, `customers`)
- **Result Formatting**: Return consistent result arrays with `id`, `message`, and relevant metadata
- **Error Handling**: Use WP_Error with descriptive error codes and user-friendly messages
- **Model Validation**: Regularly validate generators against updated EasyCommerce models</content>
  <parameter name="filePath">/Users/alamin/Sites/woocommerce/wp-content/plugins/easycommerce-fakerpress/AGENTS.md
