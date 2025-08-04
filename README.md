# EasyCommerce FakerPress

A WordPress plugin that generates fake ecommerce data (products, customers, orders, and coupons) for the EasyCommerce plugin using the PHPFaker library.

## Features

- Generate fake products with random names, descriptions, prices, and attributes
- Create fake customer accounts with addresses and contact information
- Generate fake orders with realistic data
- Create discount coupons with various settings
- Modern React-based admin interface with Tailwind CSS
- Tab-based navigation for each data type

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Node.js 14+ (for development)
- Composer (for PHP dependencies)

## Installation

1. Clone or download this plugin to your WordPress plugins directory
2. Install PHP dependencies: `composer install`
3. Install Node.js dependencies: `npm install`
4. Build the frontend assets: `npm run build`
5. Activate the plugin in WordPress admin

## Development

### Build Assets
```bash
# Development build with watch
npm run dev

# Production build
npm run build
```

### Code Quality
```bash
# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css

# Lint PHP
composer run lint

# Fix linting issues
npm run fix
composer run fix
```

## Usage

1. Go to WordPress Admin → EC FakerPress
2. Select the tab for the type of data you want to generate
3. Choose the number of items to generate
4. Click the generate button

## File Structure

```
easycommerce-fakerpress/
├── src/admin/           # React frontend source
├── includes/            # PHP generator classes
├── dist/               # Built assets
├── vendor/             # Composer dependencies
├── node_modules/       # npm dependencies
└── easycommerce-fakerpress.php  # Main plugin file
```

## License

GPL v2 or later