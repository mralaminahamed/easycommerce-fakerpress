# Development Guide

## Build Commands

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

## Code Quality & Standards

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

## Contributing

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

## Technology Stack

- **Backend**: PHP 7.4+, WordPress REST API, EasyCommerce Models
- **Frontend**: React 18, React Router v7, Tailwind CSS, Headless UI
- **Build Tools**: Webpack 5, Babel, PostCSS, Sass
- **Data Generation**: Faker PHP library with realistic patterns
- **Code Quality**: ESLint, Stylelint, PHPCS, PHPStan level 8
- **Architecture**: PSR-4 autoloading, dependency injection, abstract patterns
- **Validation**: Real-time data availability and dependency validation