# EasyCommerce FakerPress

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.txt)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-8892BF.svg)](https://php.net/)
[![Version](https://img.shields.io/badge/Version-2.1.0-green.svg)]()
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.1.18-blue.svg)](https://tailwindcss.com/)

🚀 **Generate realistic test data for your EasyCommerce store in seconds!** 14 specialized generators, a modern SaaS admin UI with run history and stats, configurable settings, sample data sync from GitHub, and a full Playwright e2e test suite. Perfect for development, testing, and creating stunning demos.

## ❓ What is EasyCommerce FakerPress?

**EasyCommerce FakerPress** helps you populate your EasyCommerce store with realistic test data quickly and easily. Whether you're:

- 🧑‍💻 **Developing** new features and need test data
- 🧪 **Testing** plugins, themes, or integrations
- 🎬 **Creating demos** for clients or presentations
- 📈 **Analyzing performance** with realistic data sets

Our plugin generates high-quality, realistic data that behaves just like real customer data, making your testing and development process much more effective.

## 🚀 Get Started in Minutes

### For Users

1. **Install & Activate**: Install the plugin from WordPress.org and activate it
2. **Open Interface**: Go to WordPress Admin → **EC FakerPress**
3. **Start Generating**: Choose a generator and click "Generate" - that's it!

### For Developers

```bash
# Install dependencies
composer install && npm install

# Build assets
npm run build

# Activate plugin
# WordPress Admin → Plugins → Activate "EasyCommerce FakerPress"
```

## ✨ Key Features

- 🛍️ **14 Specialized Generators** — Products, customers, orders, coupons, variations, shipping plans, tax classes, transactions, cart sessions, locations, product reviews, attributes, refunds, and logs
- 🎨 **Modern SaaS Admin UI** — Stats bar homepage, two-panel generator page with sticky action panel, sidebar with run history, global sticky nav
- 📊 **Run History & Stats** — Per-generator run log persisted to localStorage; all-time stats on homepage
- ⚙️ **Settings Page** — Default count, locale, seed, metadata toggle; configurable max history; sample data sync; About section
- 🔄 **Sample Data Sync** — Download locale-specific reference data (75+ locales) from the companion GitHub repository in one click
- 🔌 **Our Plugins Page** — Browse the author's other WordPress.org plugins with live data
- 🧪 **Playwright E2E Suite** — 131 automated tests covering all generators, field types, and UI interactions
- 🔧 **Developer Friendly** — Extensive hooks, TypeScript strict, REST API, PHPStan level 8

## 🚀 What's New in v2.1.0

- **Complete admin UI redesign** — Modern SaaS style (clean white, blue/indigo accents, Linear/Notion aesthetic); homepage stats bar + generator grid; two-panel generator page with sticky action panel; sidebar with category nav and per-generator run history
- **New generator architecture** — Replaced 757-line GeneratorBase monolith and 14 per-generator files with focused `ParamsPanel`, `ActionPanel`, and `GeneratorSidebar` components; `parameterConfig` centralised in `generators.ts`
- **3 new generators** — Attribute, Refund, Log (total now 14)
- **Settings page** — Default count, locale, seed, include-metadata toggle; configurable max run history; sample data sync from GitHub; About card; Reset Settings
- **Sample data sync** — Download / force-re-sync locale-specific reference data (75+ locales) from the companion [easycommerce-fakerpress-sample-data](https://github.com/mralaminahamed/easycommerce-fakerpress-sample-data) repository
- **Our Plugins page** — Live WordPress.org plugin cards for the author's other plugins
- **Global sticky nav** — Generators / Settings / Our Plugins with correct active-state matching
- **Playwright e2e suite** — 131 automated tests covering home page, all generator layouts, ActionPanel interactions, all 6 field types, and all 14 generators
- **Bug fixes** — Category matching in all locales; single ActionPanel DOM instance; accessible focus styles; RangeField error colour; nested route active-state

## 📸 Screenshots

### Homepage — StatsBar + GeneratorGrid

![Homepage](.wordpress-org/screenshot-1.png)
_Live generation stats and all 14 generators grouped by category with Popular badges_

### Products Generator

![Products Generator](.wordpress-org/screenshot-2.png)
_Two-panel layout: sidebar with category nav, params panel, sticky action panel with count/locale/seed/generate_

### Customers Generator

![Customers Generator](.wordpress-org/screenshot-3.png)
_ChipField pill-selects for customer types and age groups, address and purchase history toggles_

### Orders Generator

![Orders Generator](.wordpress-org/screenshot-4.png)
_Order status selector, customer distribution controls, items-per-order range_

### Coupons Generator

![Coupons Generator](.wordpress-org/screenshot-5.png)
_Discount type chip-selects (Percentage, Fixed, Free Shipping), discount range, usage limit controls_

### Settings Page

![Settings Page](.wordpress-org/screenshot-6.png)
_Default count, locale, seed, metadata toggle; configurable run history limit; sample data sync_

### Our Plugins Page

![Our Plugins](.wordpress-org/screenshot-7.png)
_Live WordPress.org plugin cards with ratings and active install counts_

### Product Variations Generator

![Product Variations Generator](.wordpress-org/screenshot-8.png)
_Variable product type selection, price variance range, stock management settings_

### Product Reviews Generator

![Product Reviews Generator](.wordpress-org/screenshot-9.png)
_Target specific products, realistic rating distribution, verified purchase support_

### Shipping Plans Generator

![Shipping Plans Generator](.wordpress-org/screenshot-10.png)
_Shipping type chips, cost and coverage area range, calculation method and delivery timeframe_

### Attributes Generator

![Attributes Generator](.wordpress-org/screenshot-11.png)
_Attribute type chip-select (Text, Color, Image) for product variations and filtering_

## 📚 Documentation

| Document                                      | Description                                |
| --------------------------------------------- | ------------------------------------------ |
| [📦 Installation Guide](docs/installation.md) | Setup instructions and requirements        |
| [🚀 Usage Guide](docs/usage.md)               | How to use the generators and interface    |
| [✨ Features Overview](docs/features.md)      | Complete feature list and capabilities     |
| [🏗️ Architecture](docs/architecture.md)       | Technical architecture and design patterns |
| [🛠️ Development Guide](docs/development.md)   | Contributing and development workflow      |
| [📋 Changelog](docs/changelog.md)             | Version history and release notes          |

## 📋 Requirements

- **WordPress**: 5.0+
- **PHP**: 7.4+
- **EasyCommerce Plugin**: Required
- **Node.js**: 20+ (development, required for Tailwind CSS v4)
- **Composer**: For PHP dependencies

## 🛠️ Quick Commands

```bash
# Install dependencies
composer install && yarn install

# Development
yarn start               # Watch mode with hot reload
yarn build               # Production build with Tailwind v4

# E2E tests (Playwright)
yarn test:e2e:setup      # Configure WP test user via WP-CLI
yarn test:e2e            # Run all 131 Playwright tests
yarn test:e2e:ui         # Playwright interactive UI mode
yarn test:e2e:report     # Open HTML test report

# Code quality
composer lint            # PHP CodeSniffer (WordPress standards)
composer analyse         # PHP Static Analysis (PHPStan level 8)
composer test            # PHPUnit tests
```

## 🏗️ Built for Reliability

- **Modern & Fast** - Built with React 18 and WordPress REST API for lightning-fast performance
- **WordPress Native** - Seamlessly integrates with your WordPress admin and EasyCommerce
- **Quality Assured** - Rigorous testing and code quality standards ensure reliability
- **Developer Ready** - Extensive customization options and comprehensive documentation

## 🤝 Contributing

Contributions welcome! See [Development Guide](docs/development.md) for complete contributing guidelines, development setup, coding standards, and workflow.

## 📝 License

GPL v2 or later - see [LICENSE](LICENSE) file.

## 👨‍💻 Author

**Al Amin Ahamed**

- Website: [alaminahamed.com](https://alaminahamed.com)
- GitHub: [@mralaminahamed](https://github.com/mralaminahamed)
- Email: me@alaminahamed.com

## 🆘 Support

[GitHub Issues](https://github.com/mralaminahamed/easycommerce-fakerpress/issues) | [Changelog](docs/changelog.md)

---

**EasyCommerce FakerPress v2.1.0** | April 26, 2026

_Modern test data generation for EasyCommerce stores_
