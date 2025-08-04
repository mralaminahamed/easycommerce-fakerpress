# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

EasyCommerce FakerPress is a WordPress plugin that generates fake ecommerce data (products, customers, orders, coupons) for testing and development purposes. It uses PHPFaker library for data generation and features a modern React-based admin interface with Tailwind CSS.

## Development Environment

- WordPress installation path: `/home/alamin/Local Sites/easy-commerce-development/app/public/`
- Plugin path: `wp-content/plugins/easycommerce-fakerpress/`
- Local by Flywheel development setup

## Architecture

### Backend (PHP)
- Main plugin file: `easycommerce-fakerpress.php`
- Generator classes in `includes/`:
  - `class-product-generator.php` - Products with attributes, categories, pricing
  - `class-customer-generator.php` - Users with billing/shipping addresses
  - `class-order-generator.php` - Orders linking customers and products
  - `class-coupon-generator.php` - Discount coupons with various settings
- Uses Faker\Factory for realistic fake data generation
- AJAX endpoints for React frontend communication

### Frontend (React)
- React components in `src/admin/components/`
- Tab-based interface using Headless UI
- Tailwind CSS for styling with WordPress-compatible colors
- Webpack build system with Babel for JSX/ES6+ support

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
composer run lint    # PHP CodeSniffer
composer run fix     # PHP Code Beautifier
```

## Key Integration Points

- WordPress AJAX action: `ecfp_generate_data`
- Admin menu slug: `easycommerce-fakerpress`
- React mount point: `#easycommerce-fakerpress-root`
- Nonce: `ecfp_nonce` for security
- Uses WordPress user roles and capabilities (`manage_options`)

## Data Generation Logic

Each generator creates realistic fake data:
- Products: SKUs, prices, stock, attributes, categories
- Customers: WordPress users with ecommerce meta fields
- Orders: Links existing customers/products with realistic order flow
- Coupons: Various discount types with expiration dates

## WordPress Standards

- Follows WordPress Coding Standards (WPCS)
- Proper sanitization and validation
- Uses WordPress hooks and filters
- Implements activation/deactivation hooks
- Proper text domain for internationalization

## Project Requirements and Technical Specifications

- Create a WordPress plugin to generate fake data for:
  * Orders
  * Products
  * Customers
  * Coupons
- Use PHPFaker library for data generation
- Build admin interface with:
  * Single page design
  * Tabbed navigation for different data types
- Frontend Technology Stack:
  * React for component-based UI
  * Tailwind CSS for styling
  * ESLint for JavaScript linting
  * Stylelint for CSS linting
- Build Tools:
  * Composer for PHP dependency management
  * Webpack for asset bundling and compilation

## Development Memories

- Carefully read the ../easycommerce plugin, and properly fill each data field from our fakker plugin