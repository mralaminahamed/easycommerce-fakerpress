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
- Use Tailwind CSS for styling
- Proper i18n with `@wordpress/i18n`
- Async/await for API calls with try/catch error handling
- TypeScript for type safety in all files

### General

- Conventional commits: `type(scope): description`
- No console.log in production code (warn level in ESLint)
- Prefer const over let/var, arrow functions
- Single quotes for strings (PHP), template literals for JS
- 4 spaces indentation (PHP), tabs (JS per WordPress standards)

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
- Refactor Copilot-generated code to match project conventions if needed</content>
  <parameter name="filePath">/Users/alamin/Sites/woocommerce/wp-content/plugins/easycommerce-fakerpress/AGENTS.md
