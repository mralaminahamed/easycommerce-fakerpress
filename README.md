# EasyCommerce FakerPress

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.txt)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-8892BF.svg)](https://php.net/)

A professional WordPress plugin that generates realistic fake ecommerce data (products, customers, orders, and coupons) for testing and development purposes with EasyCommerce plugin. Built with modern technologies including React admin interface, PHPFaker library, and Tailwind CSS styling.

## ✨ Features

- 🛍️ **Products**: Generate products with SKUs, prices, stock, attributes, categories, and images
- 👥 **Customers**: Create WordPress users with billing/shipping addresses and ecommerce metadata
- 📦 **Orders**: Generate realistic orders linking existing customers and products
- 🎫 **Coupons**: Create discount coupons with various types and expiration dates
- ⚡ **Modern Interface**: React-based admin interface with Tailwind CSS
- 🎨 **Tab Navigation**: Intuitive tabbed interface for different data types
- 🔧 **WordPress Standards**: Follows WordPress Coding Standards (WPCS)
- 🛡️ **Security**: Proper sanitization, validation, and nonce verification
- 🌐 **Internationalization**: Ready for translation with proper text domains

## 📋 Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **EasyCommerce**: Required for ecommerce functionality
- **Node.js**: 14+ (for development)
- **Composer**: For PHP dependency management

## 🚀 Installation

### Manual Installation

1. **Download**: Clone or download this plugin to your WordPress plugins directory
   ```bash
   cd /path/to/wp-content/plugins/
   git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git
   ```

2. **Install Dependencies**: 
   ```bash
   cd easycommerce-fakerpress
   composer install          # Install PHP dependencies
   yarn install             # Install Node.js dependencies
   ```

3. **Build Assets**:
   ```bash
   yarn build               # Build production assets
   ```

4. **Activate**: Go to WordPress Admin → Plugins and activate "EasyCommerce FakerPress"

### Development Setup

For development work on this plugin:

```bash
# Clone the repository
git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git
cd easycommerce-fakerpress

# Install dependencies
composer install
yarn install

# Start development server with hot reload
yarn dev
```

## 🛠️ Development

### Build Commands

```bash
# Development build with watch mode
yarn dev
yarn start
yarn watch

# Production build
yarn build

# Update packages
yarn packages-update
```

### Code Quality & Linting

```bash
# Lint everything
yarn lint

# Individual linting
yarn lint:js             # Lint JavaScript
yarn lint:css            # Lint CSS/SCSS
yarn lint:php            # Lint PHP (requires composer)

# Fix linting issues
yarn fix                 # Fix JS/CSS issues
composer run fix         # Fix PHP issues

# Static analysis
composer run phpstan     # Run PHPStan analysis
```

## 📖 Usage

### Admin Interface

1. Navigate to **WordPress Admin → EC FakerPress**
2. Choose from the tabbed interface:
   - **Products**: Generate WooCommerce products
   - **Customers**: Create customer accounts
   - **Orders**: Generate purchase orders
   - **Coupons**: Create discount coupons
3. Set the number of items to generate
4. Click **Generate** button

### Data Generation Features

- **Smart Dependencies**: Orders automatically use existing products and customers
- **Realistic Data**: Uses Faker library for authentic-looking information
- **WordPress Integration**: Proper user roles, capabilities, and metadata
- **EasyCommerce Compatible**: Full integration with EasyCommerce data structures

## 🏗️ Architecture

### File Structure

```
easycommerce-fakerpress/
├── easycommerce-fakerpress.php    # Main plugin file
├── class-easycommerce-fakerpress.php  # Main plugin class
├── src/
│   └── admin/
│       ├── components/             # React components
│       ├── styles/                # SCSS stylesheets
│       └── index.js               # Main entry point
├── includes/
│   ├── class-product-generator.php
│   ├── class-customer-generator.php
│   ├── class-order-generator.php
│   └── class-coupon-generator.php
├── build/                         # Compiled assets
├── vendor/                        # Composer dependencies  
├── composer.json                  # PHP dependencies
├── package.json                   # Node.js dependencies
├── webpack.config.js              # Build configuration
└── CLAUDE.md                      # Development instructions
```

### Technology Stack

- **Backend**: PHP 7.4+, WordPress APIs, EasyCommerce
- **Frontend**: React 18, Tailwind CSS, Headless UI
- **Build Tools**: Webpack, Babel, Sass
- **Data Generation**: Faker PHP library
- **Code Quality**: ESLint, Stylelint, PHPCS, PHPStan

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

### Development Workflow

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Run tests and ensure code quality (`yarn lint`, `composer run lint`)
4. Commit your changes (`git commit -m 'Add amazing feature'`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request

## 📝 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Al Amin Ahamed**
- Website: [alaminahamed.com](https://alaminahamed.com)
- GitHub: [@mralaminahamed](https://github.com/mralaminahamed)
- Email: me@alaminahamed.com

## 🐛 Support

For support and bug reports, please use the [GitHub Issues](https://github.com/mralaminahamed/easycommerce-fakerpress/issues) page.
