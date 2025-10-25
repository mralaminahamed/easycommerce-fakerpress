# Installation Guide

## Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **EasyCommerce Plugin**: Required for ecommerce functionality
- **Node.js**: 14+ (for development)
- **Composer**: For PHP dependency management

## Manual Installation

1. **Download**: Clone or download this plugin to your WordPress plugins directory
   ```bash
   cd /path/to/wp-content/plugins/
   git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git
   ```

2. **Install Dependencies**:
   ```bash
   cd easycommerce-fakerpress
   composer install          # Install PHP dependencies
   npm install              # Install Node.js dependencies
   ```

3. **Build Assets**:
   ```bash
   npm run build            # Build production assets
   ```

4. **Activate**: Go to WordPress Admin → Plugins and activate "EasyCommerce FakerPress"

## Development Setup

For development work on this plugin:

```bash
# Clone the repository
git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git
cd easycommerce-fakerpress

# Install dependencies
composer install
npm install

# Start development server with hot reload
npm run dev
```

## Troubleshooting

### Common Issues

- **EasyCommerce Plugin Missing**: Ensure the EasyCommerce plugin is installed and active
- **Build Errors**: Make sure Node.js 14+ is installed and run `npm install` again
- **PHP Errors**: Verify PHP 7.4+ is running and composer dependencies are installed
- **Permission Issues**: Check file permissions on the plugins directory

### Getting Help

- Check the [GitHub Issues](https://github.com/mralaminahamed/easycommerce-fakerpress/issues) page
- Review the [Development Guide](development.md) for advanced setup
- Contact support at the repository