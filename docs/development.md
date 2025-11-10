# 🛠️ Development Guide

Welcome to the EasyCommerce FakerPress v2.0.0 development guide! This comprehensive resource will help you contribute effectively to the project, now featuring complete TypeScript support and parameter schema alignment.

## 🚀 Quick Development Setup

### Prerequisites

- **PHP**: 7.4+ (8.0+ recommended)
- **Node.js**: 16+ (18+ recommended for TypeScript)
- **Composer**: 2.0+
- **WordPress**: 5.0+ with EasyCommerce plugin
- **Git**: For version control
- **TypeScript**: 4.5+ (included with project dependencies)

### One-Command Setup

```bash
# Clone and setup in one go
git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git
cd easycommerce-fakerpress
composer install && npm install && npm run build
```

### v2.0.0: TypeScript Migration

**All React components have been migrated to TypeScript (.tsx) for better type safety and developer experience.**

- **Type Definitions**: Comprehensive interfaces for all generator parameters
- **Parameter Validation**: Type-safe parameter schemas with proper validation
- **API Integration**: Strongly typed API responses and error handling
- **Build System**: Enhanced webpack configuration for TypeScript compilation

## 🏗️ Build System & Commands

### Development Workflow

```bash
# Start development server with hot reload
npm run start

# Production build (optimized for deployment)
npm run build

# Update packages
npm run packages-update
```

### Code Quality Assurance

```bash
# Full quality check suite
composer run lint         # PHP CodeSniffer (WordPress standards)
composer run analyse      # PHP static analysis (level 8)

# Auto-fix issues where possible
composer run format       # Auto-fix PHP code style

# Build and package management
npm run packages-update   # Update WordPress packages
```

### Testing Commands

```bash
# Run PHP unit tests
composer test

# Run with code coverage
composer test:coverage

# WordPress integration tests
phpunit
```

## 📋 Coding Standards & Quality

### PHP Standards (WordPress Coding Standards)

- **PSR-4 Autoloading**: Strict namespace and file structure compliance
- **WordPress Functions**: Use WordPress core functions over native PHP where possible
- **Security**: Nonce verification, input sanitization, and prepared statements
- **Documentation**: PHPDoc blocks for all classes, methods, and properties
- **Error Handling**: Proper exception handling with user-friendly messages

### JavaScript Standards (WordPress JavaScript Standards)

- **ES6+ Features**: Modern JavaScript with Babel transpilation
- **React Best Practices**: Functional components with hooks
- **Accessibility**: WCAG compliance with proper ARIA attributes
- **Performance**: Code splitting and lazy loading for optimal performance
- **WordPress Integration**: wp.i18n for internationalization

### CSS Standards (WordPress CSS Standards)

- **Tailwind CSS**: Utility-first approach with WordPress admin integration
- **BEM Methodology**: Block Element Modifier naming convention
- **CSS Variables**: WordPress admin color scheme integration
- **Responsive Design**: Mobile-first approach with WordPress breakpoints

### Code Quality Tools

- **PHPStan**: Static analysis at level 8 for type safety
- **PHPCS**: WordPress Coding Standards enforcement
- **ESLint**: JavaScript linting with React and WordPress rules
- **Stylelint**: CSS linting with WordPress standards
- **Prettier**: Consistent code formatting across all file types

## 🤝 Contributing Guidelines

### Contribution Types

- **🐛 Bug Fixes**: Fix issues and improve stability
- **✨ New Features**: Add new generators or functionality
- **📚 Documentation**: Improve docs, guides, and examples
- **🧪 Tests**: Add or improve test coverage
- **🔧 Tooling**: Build tools, CI/CD, and development experience

### Development Workflow

#### 1. Choose an Issue

- Check [GitHub Issues](https://github.com/mralaminahamed/easycommerce-fakerpress/issues) for open tasks
- Comment on the issue to indicate you're working on it
- For new features, create an issue first to discuss the approach

#### 2. Setup Development Environment

```bash
# Fork and clone
git clone https://github.com/your-username/easycommerce-fakerpress.git
cd easycommerce-fakerpress

# Setup dependencies
composer install
npm install

# Create feature branch
git checkout -b feature/your-feature-name
```

#### 3. Development Process

```bash
# Start development server
npm run start

# Make your changes following coding standards
# Run quality checks frequently
composer run lint && composer run analyse

# Write tests for new functionality
composer test

# Build and test in WordPress environment
npm run build
```

#### 4. Commit and Push

```bash
# Stage your changes
git add .

# Commit with descriptive message
git commit -m "feat: add new generator for shipping zones

- Add ShippingZoneGenerator class with full parameter support
- Implement REST API endpoint for shipping zone generation
- Add frontend component with advanced configuration options
- Include comprehensive validation and error handling
- Add unit tests and integration tests"

# Push to your fork
git push origin feature/your-feature-name
```

#### 5. Create Pull Request

- Go to the original repository
- Click "New Pull Request"
- Select your feature branch
- Fill out the PR template with:
  - Clear description of changes
  - Screenshots for UI changes
  - Test results
  - Breaking changes (if any)

### Commit Message Convention

We follow conventional commit format:

```
type(scope): description

[optional body]

[optional footer]
```

**Types:**

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Testing
- `chore`: Maintenance

**Examples:**

```
feat(products): add support for digital product generation
fix(orders): resolve tax calculation bug in multi-state orders
docs(readme): update installation instructions for Node.js 16+
```

## 🧪 Testing Strategy

### Unit Testing (PHP)

```php
class Product_Generator_Test extends WP_UnitTestCase {
    public function test_generate_single_product() {
        $generator = new Product_Generator();
        $product = $generator->generate_single_item([]);

        $this->assertArrayHasKey('name', $product);
        $this->assertArrayHasKey('price', $product);
        // ... more assertions
    }
}
```

### Integration Testing

- **WordPress Integration**: Test with actual WordPress environment
- **EasyCommerce Integration**: Verify data works with EasyCommerce models
- **Database Testing**: Ensure proper data relationships and constraints

### End-to-End Testing

- **User Workflows**: Test complete generation workflows
- **UI Interactions**: Verify frontend functionality
- **API Endpoints**: Test REST API responses and error handling

### Test Coverage Goals

- **PHP Code**: Minimum 80% coverage
- **Critical Paths**: 100% coverage for core generation logic
- **API Endpoints**: Full coverage for REST controllers
- **Error Conditions**: Test edge cases and error scenarios

### Coding Standards

#### PHP Code Standards

```php
// ✅ Good: PSR-4 namespace and WordPress standards
namespace EasyCommerceFakerPress\Generators;

class Product_Generator extends Abstract_Generator {
    /**
     * Generate a single product with full e-commerce data.
     *
     * @param array $params Generation parameters.
     * @return array Product data array.
     */
    protected function generate_single_item(array $params): array {
        // Implementation following WordPress coding standards
    }
}

// ❌ Bad: Mixed conventions and poor documentation
class product_generator { // Wrong naming
    function make_product($params) { // Wrong style
        // Missing PHPDoc and type hints
    }
}
```

#### JavaScript/React Standards

```javascript
// ✅ Good: Modern React with hooks and WordPress integration
import { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";

function ProductGenerator({ onGenerate }) {
  const [count, setCount] = useState(10);

  return (
    <div className="generator-form">
      <label htmlFor="product-count">
        {__("Number of products:", "easycommerce-fakerpress")}
      </label>
      <input
        id="product-count"
        type="number"
        value={count}
        onChange={(e) => setCount(e.target.value)}
        min="1"
        max="1000"
      />
    </div>
  );
}

// ❌ Bad: Legacy patterns and poor accessibility
function productGenerator() {
  // Wrong naming convention
  return (
    <div>
      {" "}
      {/* Missing semantic HTML */}
      <input type="text" /> {/* Missing label and accessibility */}
    </div>
  );
}
```

#### CSS Standards

```css
/* ✅ Good: Tailwind with WordPress integration */
.generator-card {
  @apply bg-white border rounded-lg shadow-sm p-6;
  border-color: var(--wp-admin-theme-color);
}

.generator-card__title {
  @apply text-lg font-semibold mb-4;
  color: var(--wp-admin-theme-color);
}

/* ❌ Bad: Inconsistent styling */
.generatorCard {
  /* Wrong naming */
  background: white; /* Not using design system */
  border: 1px solid blue; /* Hard-coded colors */
}
```

#### Documentation Standards

```php
/**
 * Product Generator Class
 *
 * Generates realistic product data for EasyCommerce stores.
 *
 * @package EasyCommerceFakerPress\Generators
 * @since 1.0.0
 */
class Product_Generator extends Abstract_Generator {

    /**
     * Generate a single product item.
     *
     * Creates a complete product with attributes, pricing, inventory,
     * and all necessary metadata for EasyCommerce integration.
     *
     * @since 1.0.0
     * @param array $params {
     *     Generation parameters.
     *
     *     @type int    $category_id     Parent category ID.
     *     @type bool   $include_images  Whether to generate images.
     *     @type string $price_range     Price range ('low'|'medium'|'high').
     * }
     * @return array Product data array.
     * @throws InvalidArgumentException When parameters are invalid.
     */
    protected function generate_single_item(array $params): array {
        // Implementation
    }
}
```

## 🏗️ Technology Stack

EasyCommerce FakerPress leverages modern web technologies and development practices for optimal performance and maintainability.

### Backend Architecture

#### PHP Ecosystem

- **PHP 7.4+**: Modern PHP with type declarations and improved performance
- **WordPress REST API**: Native WordPress API integration with custom endpoints
- **EasyCommerce Models**: Direct integration with EasyCommerce data models
- **PSR-4 Autoloading**: Standard PHP namespace and class loading
- **Composer Dependencies**: Modern PHP dependency management

#### Core Libraries

```json
{
  "php": ">=7.4",
  "fakerphp/faker": "^1.23",
  "bluemmb/faker-picsum-photos-provider": "^2.0"
}
```

### Frontend Architecture

#### React Ecosystem

- **React 18**: Latest React with concurrent features and automatic batching
- **React Router v7**: Modern data router with hash-based routing for WordPress
- **Tailwind CSS**: Utility-first CSS framework with WordPress admin integration
- **Headless UI**: Accessible, unstyled UI components for consistent design

#### Build Tools

- **Webpack 5**: Advanced module bundling with code splitting and optimization
- **Babel**: JavaScript transpilation for browser compatibility
- **PostCSS**: CSS processing with Autoprefixer and CSS variables
- **ESLint + Prettier**: Code linting and consistent formatting

#### Development Tools

```json
{
  "react": "^18.2.0",
  "react-router-dom": "^7.7.1",
  "@wordpress/scripts": "^30.20.0",
  "tailwindcss": "^3.3.5",
  "webpack": "^5.89.0"
}
```

### Quality Assurance

#### Code Quality Tools

- **PHPStan Level 8**: Advanced static analysis for type safety and bug detection
- **PHPCS**: WordPress Coding Standards enforcement with automatic fixing
- **ESLint**: JavaScript linting with React and WordPress-specific rules
- **Stylelint**: CSS linting with Tailwind CSS and WordPress standards

#### Testing Framework

- **PHPUnit**: PHP unit testing with WordPress integration
- **WordPress Test Suite**: wp-phpunit for WordPress-specific testing
- **React Testing Library**: Component testing for React components
- **Playwright**: End-to-end testing (future implementation)

### Architecture Patterns

#### Design Patterns Implemented

- **Abstract Factory**: Generator instantiation based on type
- **Template Method**: Consistent generation workflow across all generators
- **Strategy Pattern**: Configurable generation strategies
- **Observer Pattern**: Event-driven architecture with WordPress hooks
- **Dependency Injection**: Clean architecture with testable components

#### WordPress Integration Patterns

- **Plugin Architecture**: Proper WordPress plugin structure and hooks
- **REST API Design**: WordPress REST API best practices
- **Admin Interface**: Native WordPress admin page integration
- **Internationalization**: wp-i18n for multi-language support
- **Security**: WordPress nonce and capability systems

### Development Workflow

#### Version Control

- **Git Flow**: Feature branches, releases, and hotfixes
- **Conventional Commits**: Standardized commit message format
- **Pull Requests**: Code review and automated testing
- **GitHub Actions**: CI/CD pipeline with automated checks

#### Development Environment

- **Local by Flywheel**: Recommended WordPress development environment
- **Docker**: Containerized development (alternative)
- **VS Code**: Recommended IDE with WordPress and React extensions
- **Pre-commit Hooks**: Automated code quality checks

### Performance Optimization

#### Frontend Optimization

- **Code Splitting**: Route-based and component-based code splitting
- **Lazy Loading**: Dynamic imports for improved initial load time
- **Asset Optimization**: Webpack optimization for production builds
- **Caching**: Browser caching and service worker implementation

#### Backend Optimization

- **Database Optimization**: Efficient queries with proper indexing
- **Memory Management**: Chunked processing for large datasets
- **Caching**: WordPress object cache and transient caching
- **Background Processing**: WordPress cron for long-running tasks

### Deployment & Distribution

#### Build Process

- **Development Build**: Hot reload and source maps for development
- **Production Build**: Optimized assets with minification and compression
- **Asset Management**: WordPress enqueue system for proper loading
- **Version Management**: Automated versioning and changelog generation

#### Distribution

- **WordPress.org**: Official plugin repository distribution
- **GitHub Releases**: Versioned releases with build artifacts
- **Composer**: PHP package distribution for advanced users
- **NPM**: JavaScript package for frontend-only usage
