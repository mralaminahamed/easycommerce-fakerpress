# Copilot Instructions for easycommerce-fakerpress Plugin

## Purpose

This file provides comprehensive guidelines for using GitHub Copilot in the easycommerce-fakerpress WordPress plugin project. The plugin generates realistic test data for EasyCommerce stores using a modern React/TypeScript frontend and PHP backend.

## Project Overview

- **Technology Stack**: WordPress Plugin, PHP 7.4+, React 18, TypeScript, Tailwind CSS v4
- **Architecture**: PSR-4 PHP backend with REST API, React frontend with modern tooling
- **Purpose**: Generate test data for e-commerce development, testing, and demonstrations

## Coding Standards

### PHP Standards

- Follow WordPress Coding Standards (WPCS) - PSR-4 autoloading with `EasyCommerceFakerPress\` namespace
- PHP 7.4+ minimum, support up to current WordPress requirements
- Class names: PascalCase (e.g., `ProductGenerator`)
- Method/variable names: camelCase
- File names: snake_case with hyphens (e.g., `product-generator.php`)
- PHPDoc comments for all classes, methods, and properties
- Dependency injection over global state
- Proper error handling with try/catch and WP_Error

### JavaScript/React Standards

- ES6+ syntax with WordPress ESLint rules
- Functional components with hooks (no class components)
- Import order: WordPress core → external libraries → local components
- camelCase for variables/functions, PascalCase for components
- Use Tailwind CSS v4 for styling with @theme and @utility directives
- Node.js 20+ required for Tailwind v4 and build process
- Proper i18n with `@wordpress/i18n`
- Async/await for API calls with try/catch error handling
- TypeScript for type safety in all files

## File Structure & Architecture

### Backend Structure

- `includes/Generators/`: PHP generator classes (Product, Customer, Order, etc.)
- `includes/Controllers/`: REST API controllers for each generator
- `includes/Abstracts/`: Base classes for generators and controllers

### Frontend Structure

- `src/admin/components/Generators/`: React components for each generator UI
- `src/admin/components/ui/`: Reusable UI components (Button, Card, Input, etc.)
- `src/admin/styles.css`: Tailwind CSS v4 styles with @theme and @utility directives

### Build System

- `build/`: Compiled assets (do not edit directly)
- PostCSS with Tailwind v4 and Autoprefixer
- Webpack 5 with TypeScript compilation

## Best Practices

### Code Quality

- Write clear, self-documenting code with meaningful variable names
- Add PHPDoc/JSDoc comments for complex logic
- Prefer functional components and hooks in React
- Use dependency injection and avoid global state in PHP
- Keep functions and components small and focused (< 50 lines)
- Avoid code duplication; use shared utilities/components

### Security & Performance

- Never expose sensitive data in frontend code
- Use WordPress nonce verification for all API calls
- Sanitize and validate all user inputs
- Follow WordPress security best practices
- Optimize database queries and avoid N+1 problems

## Development Workflow

### Setup Requirements

- Node.js 20+ for frontend development
- Composer for PHP dependencies
- WordPress 5.0+ with EasyCommerce plugin
- Yarn or npm for package management

### Build Commands

- `yarn build` - Production build
- `yarn start` - Development server with hot reload
- `composer lint` - PHP code linting
- `composer test` - PHP unit tests

## Generator Development Guidelines

### Data Structure Alignment

- Ensure generator `create()` calls match EasyCommerce model expectations exactly
- Use `dependsOn` in React parameter configs for conditional fields
- REST API endpoints should be plural (e.g., `orders`, `products`, `customers`)
- Return consistent result arrays with `id`, `message`, and relevant metadata
- Use WP_Error with descriptive error codes and user-friendly messages

### Frontend-Backend Integration

- React components must call correct REST endpoints matching PHP controllers
- Parameter schemas must align between frontend forms and backend validation
- Use TypeScript interfaces for API request/response types
- Handle loading states and error conditions appropriately

## Testing Guidelines

### PHP Testing

- Use PHPUnit for unit and integration tests
- Test data generators with realistic sample data
- Verify database operations and cleanup
- Test error conditions and edge cases

### JavaScript Testing

- Use Jest and React Testing Library for component tests
- Test user interactions and form submissions
- Mock API calls and WordPress functions
- Verify accessibility and responsive behavior

## Documentation Standards

### Code Documentation

- PHPDoc for all PHP classes, methods, and properties
- JSDoc for complex React components and utilities
- Inline comments for business logic explanations
- Update README.md for major features or breaking changes

### API Documentation

- Document REST API endpoints with parameter schemas
- Include examples for generator configurations
- Maintain changelog with version-specific changes

## Copilot Usage Guidelines

### When to Use Copilot

- Generating boilerplate code (hooks, components, classes)
- Implementing common patterns (API calls, form handling)
- Suggesting utility functions and helpers
- Creating test cases and mock data

### When NOT to Use Copilot

- Security-sensitive code (authentication, data validation)
- Complex business logic requiring domain knowledge
- Database queries and schema definitions
- WordPress-specific integrations

### Review Checklist

- Verify WordPress coding standards compliance
- Check for proper error handling and validation
- Ensure TypeScript types are correct and complete
- Test functionality in WordPress environment
- Verify accessibility and responsive design
- Check for security vulnerabilities

### Refactoring Suggestions

- Refactor Copilot-generated code to match project conventions
- Simplify overly complex suggestions
- Add proper error handling and logging
- Ensure code follows established patterns
- Add tests for new functionality

## Common Patterns

### PHP Generator Class

```php
class ProductGenerator extends AbstractGenerator {
    protected function generate_single_item(array $params): array {
        // Implementation following WPCS
        return $productData;
    }
}
```

### React Generator Component

```tsx
function ProductGenerator() {
  const [params, setParams] = useState({});
  const handleGenerate = async () => {
    // API call with error handling
  };
  return <GeneratorBase {...config} />;
}
```

### Tailwind CSS v4 Usage

```css
@theme {
  --color-wp-admin-primary: #2271b1;
}

@utility animate-fade-in {
  animation: fade-in 0.3s ease-in;
}
```

---

For questions, contact a maintainer or refer to AGENTS.md for complete project guidelines.
