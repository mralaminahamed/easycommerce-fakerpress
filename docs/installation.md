# 📦 Installation Guide

Welcome to EasyCommerce FakerPress! This comprehensive installation guide will help you get up and running quickly and safely.

## ✅ System Requirements

Before installing, ensure your system meets these requirements:

### Core Requirements

- **WordPress**: 5.0 or higher (6.0+ recommended)
- **PHP**: 7.4 or higher (8.0+ recommended for optimal performance)
- **MySQL/MariaDB**: 5.6 or higher
- **Memory**: 256MB minimum (512MB recommended for large datasets)

### EasyCommerce Integration

- **EasyCommerce Plugin**: Latest version required (must be active)
- **Database Tables**: EasyCommerce tables must be properly installed
- **User Permissions**: Administrator access for installation

### Development Requirements (Optional)

- **Node.js**: 16+ (18+ recommended for TypeScript support)
- **Composer**: 2.0+ (for PHP dependency management)
- **Git**: For cloning the repository (optional)
- **TypeScript**: 4.5+ (included with project dependencies for v2.0.0)

### Server Recommendations

- **Web Server**: Apache/Nginx with mod_rewrite
- **PHP Extensions**: curl, json, mbstring, openssl, pdo, pdo_mysql
- **File Permissions**: Write access to wp-content/plugins/ and wp-content/uploads/
- **SSL Certificate**: HTTPS recommended for security

## 🔧 Manual Installation

For developers and advanced users who prefer manual installation.

### Step 1: Download the Plugin

#### Option A: Git Clone (Recommended for Development)

```bash
# Navigate to your WordPress plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Clone the repository
git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git

# Navigate to the plugin directory
cd easycommerce-fakerpress
```

#### Option B: Direct Download

1. Download the latest release ZIP from GitHub
2. Extract to `/wp-content/plugins/easycommerce-fakerpress/`
3. Ensure proper file permissions (755 for directories, 644 for files)

### Step 2: Install PHP Dependencies

EasyCommerce FakerPress uses Composer for PHP dependency management:

```bash
# Install PHP dependencies (including Faker library)
composer install --no-dev --optimize-autoloader

# For development (includes dev dependencies)
composer install
```

**What gets installed:**

- `fakerphp/faker`: Realistic fake data generation
- `bluemmb/faker-picsum-photos-provider`: Placeholder image generation
- Development tools (PHPCS, PHPStan, PHPUnit) if using dev dependencies

### Step 3: Install Frontend Dependencies (Development Only)

If you plan to modify the frontend or build custom assets:

```bash
# Install Node.js dependencies
npm install

# Alternative with Yarn (if available)
yarn install
```

### Step 4: Build Production Assets

For production deployment, build the optimized frontend assets:

```bash
# Build production assets
npm run build

# Alternative with Yarn
yarn build
```

**Build Output:**

- Compiled JavaScript and CSS files in `/build/`
- Optimized bundles with code splitting
- Source maps for debugging (development builds only)

### Step 5: Activate the Plugin

1. **Access WordPress Admin**: Log in to your WordPress dashboard
2. **Navigate to Plugins**: Go to **Plugins → Installed Plugins**
3. **Locate the Plugin**: Find "EasyCommerce FakerPress" in the list
4. **Activate**: Click the **Activate** button
5. **Verify Installation**: Look for "EC FakerPress" in the admin menu

### Step 6: Post-Installation Verification

After activation, verify everything is working:

1. **Check Admin Menu**: "EC FakerPress" should appear in WordPress admin menu
2. **Visit Admin Page**: Navigate to the EC FakerPress admin page (sample data will be downloaded automatically on first visit)
3. **Test Generator Access**: Click on any generator to ensure the interface loads
4. **Validate Dependencies**: The interface should show green checkmarks for data dependencies
5. **Run Test Generation**: Generate a small batch (5-10 items) to verify functionality

## 🛠️ Development Setup

Set up a complete development environment for contributing to EasyCommerce FakerPress.

### Local Development Environment

#### 1. Clone and Setup

```bash
# Clone the repository
git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git
cd easycommerce-fakerpress

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

#### 2. WordPress Development Setup

**Recommended: Local by Flywheel or similar**

- Create a new WordPress site
- Install and activate EasyCommerce plugin
- Symlink or copy the plugin to wp-content/plugins/
- Activate EasyCommerce FakerPress

**Alternative: Manual WordPress Setup**

```bash
# Download WordPress
wget https://wordpress.org/latest.zip
unzip latest.zip
cd wordpress

# Configure wp-config.php
cp wp-config-sample.php wp-config.php
# Edit database settings...

# Install WordPress via browser or WP-CLI
```

#### 3. Development Workflow

```bash
# Start frontend development server (hot reload)
npm run start

# Build production assets
npm run build

# Run code quality checks
composer run lint     # PHP CodeSniffer
composer run analyse  # Static analysis
```

#### 4. Testing Setup

```bash
# Install WordPress test framework
bash tests/php/bin/install-wp-tests.sh wordpress_test root '' localhost latest

# Run PHP tests
composer test

# Run with coverage
composer test:coverage
```

### Development Tools Configuration

#### VS Code Recommendations

- **PHP Extension**: For PHP development
- **ESLint Extension**: For JavaScript linting
- **Prettier Extension**: For code formatting
- **WordPress Extension**: For WordPress-specific snippets

#### Git Hooks (Recommended)

Set up pre-commit hooks for code quality:

```bash
# Install pre-commit hook
cp .git-hooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

### Advanced Development

#### Custom Build Configuration

Modify `webpack.config.js` for custom build requirements:

- Add new entry points
- Configure code splitting
- Set up custom loaders

#### Extending Generators

Create custom generators by extending the base Generator class:

```php
class Custom_Generator extends \EasyCommerceFakerPress\Abstracts\Generator {
    protected function generate_single_item(array $params): array {
        // Your custom generation logic
    }
}
```

#### API Development

Add new REST endpoints by extending the REST_Controller class:

```php
class Custom_REST_Controller extends \EasyCommerceFakerPress\Abstracts\REST_Controller {
    // Implement required abstract methods
}
```

## 🔍 Troubleshooting

Common installation and setup issues with solutions.

### 🚨 Critical Issues

#### Plugin Won't Activate

**Symptoms:** Plugin appears in installed plugins but won't activate

**Solutions:**

1. **Check PHP Version**: Ensure PHP 7.4+ is running
   ```bash
   php -v
   ```
2. **Verify EasyCommerce**: Ensure EasyCommerce plugin is active
3. **Check Dependencies**: Run `composer install` to ensure all PHP dependencies are installed
4. **Review Error Logs**: Check WordPress debug logs for specific error messages
5. **File Permissions**: Ensure plugin files have correct permissions (755 directories, 644 files)

#### White Screen or Fatal Errors

**Symptoms:** WordPress admin shows white screen after activation

**Solutions:**

1. **Enable Debugging**: Add to wp-config.php:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
2. **Check PHP Memory**: Increase memory limit:
   ```php
   define('WP_MEMORY_LIMIT', '256M');
   ```
3. **Deactivate and Reactivate**: Temporarily deactivate, check errors, then reactivate

### ⚠️ Common Issues

#### Build/Asset Issues

**Symptoms:** Admin interface not loading or showing errors

**Solutions:**

1. **Node.js Version**: Ensure Node.js 16+ is installed
   ```bash
   node -v
   npm -v
   ```
2. **Rebuild Assets**: Clear and rebuild
   ```bash
   rm -rf node_modules build
   npm install
   npm run build
   ```
3. **Check Build Output**: Verify files exist in `/build/` directory

#### Dependency Validation Errors

**Symptoms:** Generators show red warnings about missing dependencies

**Solutions:**

1. **Generate Prerequisites**: Generate required data in correct order:
   - Locations → Customers → Products → Orders
2. **Check EasyCommerce Setup**: Ensure EasyCommerce is properly configured
3. **Database Issues**: Verify EasyCommerce tables exist and are populated

#### Performance Issues

**Symptoms:** Generation is slow or times out

**Solutions:**

1. **Increase Limits**: Update php.ini or wp-config.php:
   ```php
   ini_set('max_execution_time', 300);
   ini_set('memory_limit', '512M');
   ```
2. **Batch Size**: Reduce batch size in generator settings
3. **Server Resources**: Check server CPU/memory usage during generation

#### Permission Issues

**Symptoms:** Cannot write files or access certain features

**Solutions:**

1. **File Permissions**: Set correct permissions:
   ```bash
   find /path/to/wordpress -type d -exec chmod 755 {} \;
   find /path/to/wordpress -type f -exec chmod 644 {} \;
   ```
2. **WordPress File System**: Use WordPress file system methods for uploads
3. **User Capabilities**: Ensure user has `manage_options` capability

### 🧪 Testing Installation

#### Automated Tests

```bash
# Run PHP unit tests
composer test

# Run WordPress integration tests
phpunit
```

#### Manual Testing Checklist

- [ ] Plugin activates without errors
- [ ] Admin menu shows "EC FakerPress"
- [ ] Interface loads and displays generators
- [ ] Dependency validation works
- [ ] Small data generation succeeds
- [ ] Generated data appears in EasyCommerce

### 📞 Getting Help

#### Community Support

- **GitHub Issues**: [Report bugs and request features](https://github.com/mralaminahamed/easycommerce-fakerpress/issues)
- **WordPress Forums**: Search for EasyCommerce FakerPress discussions
- **Documentation**: Check all docs in the `/docs/` directory

#### Debug Information

When reporting issues, include:

- WordPress version and PHP version
- EasyCommerce plugin version
- Plugin version and installation method
- Error messages and debug logs
- Steps to reproduce the issue

#### System Information Script

Run this to gather debug information:

```bash
php -r "
echo 'PHP Version: ' . PHP_VERSION . PHP_EOL;
echo 'WordPress Version: ' . get_bloginfo('version') . PHP_EOL;
echo 'Memory Limit: ' . ini_get('memory_limit') . PHP_EOL;
echo 'Max Execution Time: ' . ini_get('max_execution_time') . PHP_EOL;
"
```
