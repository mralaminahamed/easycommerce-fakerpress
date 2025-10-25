# Contributing to EasyCommerce FakerPress

Thank you for your interest in contributing to EasyCommerce FakerPress! We welcome contributions from the community and appreciate your efforts to make this plugin better.

## Development Setup

### Prerequisites

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher (8.0+ recommended)
- **EasyCommerce**: Latest stable version
- **Node.js**: 14 or higher
- **Composer**: Latest version
- **Git**: For version control

### Local Development Environment

1. **Clone the repository**
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/mralaminahamed/easycommerce-fakerpress.git
   cd easycommerce-fakerpress
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   yarn install
   # or
   npm install
   ```

4. **Build assets for development**
   ```bash
   yarn dev
   # or
   yarn dev
   ```

5. **Activate the plugin** in your WordPress admin

### Development Commands

```bash
# Development build with watch mode
yarn dev
yarn start
yarn watch

# Production build
yarn build

# Code quality checks
yarn lint              # Lint all code
yarn lint:js           # Lint JavaScript
yarn lint:css          # Lint CSS/SCSS
yarn lint:php          # Lint PHP
composer run phpcs     # PHP CodeSniffer

# Fix auto-fixable issues
yarn fix               # Fix JS/CSS issues
composer run phpcbf    # Fix PHP issues

# Static analysis
composer run phpstan   # PHP static analysis

# Update dependencies
yarn packages-update   # Update WordPress packages
```

## Code Standards

This project follows strict coding standards to ensure consistency and quality:

### WordPress Coding Standards

- **PHP**: [WordPress PHP Coding Standards (WPCS)](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- **JavaScript**: [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- **CSS**: [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)

### Modern Development Standards

- **React**: [React Best Practices](https://reactjs.org/docs/thinking-in-react.html)
- **ES6+**: Modern JavaScript with WordPress compatibility
- **SCSS**: Structured CSS with BEM methodology
- **TypeScript**: Type annotations where applicable

### Quality Requirements

All contributions must pass these quality checks:

```bash
# Required before submitting PR
composer run phpcs     # PHP CodeSniffer (WordPress standards)
composer run phpstan   # PHPStan level 8 static analysis
yarn lint:js           # ESLint (WordPress JavaScript standards)
yarn lint:css          # Stylelint (SCSS standards)
```

**Quality Levels:**
- **PHPCS**: Must pass WordPress Coding Standards
- **PHPStan**: Must pass level 8 static analysis
- **ESLint**: Must pass WordPress JavaScript standards
- **Stylelint**: Must pass SCSS/CSS standards
- **No console.log**: Remove debugging statements

## Development Workflow

### Branch Naming Convention

Use descriptive branch names following this pattern:

- `feature/feature-name` - New features
- `fix/issue-description` - Bug fixes
- `enhancement/improvement-name` - Enhancements to existing features
- `refactor/component-name` - Code refactoring
- `docs/documentation-update` - Documentation improvements
- `chore/maintenance-task` - Maintenance tasks

Examples:
- `feature/order-generation-improvements`
- `fix/ajax-nonce-validation`
- `enhancement/react-error-boundaries`

### Commit Message Guidelines

Follow [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
type(scope): description

[optional body]

[optional footer]
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(generators): add advanced product attribute generation
fix(ajax): resolve nonce verification issue in customer generator
docs(readme): update installation instructions
style(css): improve mobile responsiveness for admin interface
refactor(hooks): optimize database queries in order generator
```

### Pull Request Process

1. **Fork the repository** and create your feature branch
   ```bash
   git checkout -b feature/amazing-new-feature
   ```

2. **Make your changes** following our coding standards
   - Write clean, documented code
   - Follow existing patterns and conventions
   - Add tests for new functionality
   - Update documentation as needed

3. **Test thoroughly**
   ```bash
   # Run all quality checks
   composer run phpcs
   composer run phpstan
   yarn lint
   
   # Build assets
   yarn build
   
   # Test functionality manually
   ```

4. **Commit your changes**
   ```bash
   git add .
   git commit -m "feat(scope): descriptive commit message"
   ```

5. **Push to your fork**
   ```bash
   git push origin feature/amazing-new-feature
   ```

6. **Create a Pull Request**
   - Use the PR template
   - Provide clear description of changes
   - Reference related issues (`Closes #123`)
   - Include screenshots for UI changes
   - List any breaking changes

### Pull Request Requirements

**Before submitting:**
- [ ] All tests pass
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] Screenshots included (for UI changes)
- [ ] Breaking changes documented

**PR Checklist:**
- [ ] Clear title and description
- [ ] Related issues referenced
- [ ] Code is well-documented
- [ ] No debugging code left behind
- [ ] Responsive design verified
- [ ] Cross-browser compatibility checked

## Code Architecture

### Plugin Structure

```
easycommerce-fakerpress/
├── easycommerce-fakerpress.php      # Main plugin file
├── class-easycommerce-fakerpress.php # Main plugin class
├── src/
│   └── admin/
│       ├── components/              # React components
│       ├── styles/                  # SCSS stylesheets
│       └── index.js                 # Entry point
├── includes/
│   ├── class-product-generator.php  # Product generation logic
│   ├── class-customer-generator.php # Customer generation logic
│   ├── class-order-generator.php    # Order generation logic
│   └── class-coupon-generator.php   # Coupon generation logic
├── build/                           # Compiled assets
├── vendor/                          # Composer dependencies
├── node_modules/                    # Node dependencies
└── docs/                            # Documentation
```

### Key Components

#### PHP Classes
- **Main Class**: Singleton pattern with dependency management
- **Generator Classes**: Individual data generators with WordPress integration
- **Security**: Nonce verification, capability checks, data sanitization

#### React Components
- **Admin Interface**: Tabbed navigation with form controls
- **Data Generation**: AJAX communication with progress feedback
- **Error Handling**: User-friendly error messages and validation

#### Asset Management
- **Webpack**: Modern build system with hot module replacement
- **SCSS**: Organized stylesheets with Tailwind CSS integration
- **JavaScript**: ES6+ with WordPress compatibility

### Coding Patterns

#### PHP Patterns
```php
// Singleton pattern
class Example_Class {
    private static ?self $instance = null;
    
    public static function get_instance(): self {
        return self::$instance ??= new self();
    }
}

// WordPress hooks
add_action( 'wp_ajax_action_name', array( $this, 'handle_ajax' ) );

// Input sanitization
$value = sanitize_text_field( $_POST['value'] ?? '' );

// Output escaping
echo esc_html( $value );

// Capability checks
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'Insufficient permissions.', 'easycommerce-fakerpress' ) );
}
```

#### React Patterns
```javascript
// Functional components with hooks
const DataGenerator = ({ type, onGenerate }) => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    
    const handleSubmit = async (count) => {
        setLoading(true);
        setError(null);
        
        try {
            await onGenerate(type, count);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };
    
    return (
        <div className="generator-component">
            {/* Component JSX */}
        </div>
    );
};
```

## Testing Guidelines

### Manual Testing

**Test Environments:**
- Latest WordPress version
- Multiple themes (WordPress default themes, popular EasyCommerce themes)
- Different browsers (Chrome, Firefox, Safari, Edge)
- Various screen sizes (desktop, tablet, mobile)
- Multiple PHP versions (7.4, 8.0, 8.1, 8.2)

**Functionality Testing:**
- [ ] Plugin activation/deactivation
- [ ] Admin interface loads correctly
- [ ] All data generators function properly
- [ ] AJAX requests work as expected
- [ ] Error handling displays appropriate messages
- [ ] Generated data appears correctly in EasyCommerce
- [ ] Performance with large datasets
- [ ] Security measures function correctly

### Automated Testing

We encourage adding tests for new functionality:

```php
// Example PHPUnit test
class Test_Product_Generator extends WP_UnitTestCase {
    public function test_generate_products() {
        $generator = new \EasyCommerceFakerPress\Generators\Product();
        $result = $generator->generate(5);
        
        $this->assertCount(5, $result);
        $this->assertArrayHasKey('products_created', $result);
    }
}
```

### Performance Testing

- Test with large datasets (100+ items)
- Monitor memory usage during generation
- Check database query efficiency
- Verify no memory leaks in JavaScript

## Documentation

### Code Documentation

**PHP Documentation:**
```php
/**
 * Generate fake products with realistic data
 *
 * @since 1.0.0
 * @param int $count Number of products to generate
 * @return array Array of generated product IDs and meta data
 * @throws InvalidArgumentException If count is invalid
 */
public function generate_products( int $count ): array {
    // Implementation
}
```

**JavaScript Documentation:**
```javascript
/**
 * Handles product generation form submission
 * 
 * @param {string} type - The type of data to generate
 * @param {number} count - Number of items to generate
 * @returns {Promise} Promise that resolves with generation results
 */
const handleGeneration = async (type, count) => {
    // Implementation
};
```

### User Documentation

- Update README.md for new features
- Update readme.txt for WordPress.org compatibility
- Add changelog entries for all changes
- Include usage examples and screenshots
- Document any breaking changes

## Issue Reporting

### Bug Reports

When reporting bugs, please include:

**Environment Information:**
- WordPress version
- PHP version
- Plugin version
- EasyCommerce version
- Active theme
- Other active plugins

**Bug Details:**
- Clear, descriptive title
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- Screenshots or screen recordings
- Console errors (if applicable)
- Network tab information (for AJAX issues)

**Example Bug Report:**
```markdown
## Bug Description
Product generation fails when generating more than 50 products

## Environment
- WordPress: 6.4.1
- PHP: 8.1.0
- Plugin Version: 2.1.0
- EasyCommerce: 1.0.0

## Steps to Reproduce
1. Go to EC FakerPress admin page
2. Select Products tab
3. Enter 60 in the count field
4. Click Generate

## Expected Behavior
60 products should be generated successfully

## Actual Behavior
Process stops at 50 products with "Memory limit exceeded" error

## Additional Information
- No console errors
- PHP memory limit: 256M
- Error appears in PHP error log
```

### Feature Requests

When requesting features, please include:

- **Use Case**: Why this feature is needed
- **Expected Behavior**: How the feature should work
- **Proposed Implementation**: Ideas for how to implement (optional)
- **Impact Assessment**: Effect on existing functionality
- **Priority Level**: How important this feature is

## Security Guidelines

### Reporting Security Issues

**DO NOT** report security vulnerabilities through public GitHub issues.

Instead, email security issues to: **security@alaminahamed.com**

Include:
- Detailed description of the vulnerability
- Steps to reproduce
- Proof of concept (if safe to share)
- Suggested fix (if any)

### Security Best Practices

When contributing code:

```php
// Input sanitization
$input = sanitize_text_field( $_POST['input'] ?? '' );

// Output escaping
echo esc_html( $output );

// Nonce verification
check_ajax_referer( 'easycommerce_fakerpress_nonce', 'nonce' );

// Capability checks
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Insufficient permissions' );
}

// Prepared statements
$wpdb->prepare( "SELECT * FROM table WHERE column = %s", $value );
```

## Community Guidelines

### Code of Conduct

- Be respectful and constructive
- Welcome newcomers and help them learn
- Focus on what's best for the community
- Show empathy towards other contributors

### Communication

- **GitHub Issues**: Bug reports and feature requests
- **Pull Requests**: Code contributions
- **Discussions**: General questions and ideas
- **Email**: Security issues and private matters

## Release Process

### Version Numbering

We follow [Semantic Versioning](https://semver.org/):
- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Checklist

- [ ] All tests pass
- [ ] Documentation updated
- [ ] Changelog updated
- [ ] Version numbers bumped
- [ ] Security review completed
- [ ] Performance testing done
- [ ] WordPress.org assets updated

## Recognition

Contributors are recognized in:
- **CHANGELOG.md**: All contributions listed
- **README.md**: Major contributors highlighted
- **GitHub**: Automatic contributor recognition
- **Plugin Credits**: In-plugin acknowledgments

## Support

### Getting Help

- **Documentation**: Comprehensive guides in README.md
- **Code Examples**: Available in `/examples` directory
- **GitHub Discussions**: Community Q&A
- **Issues**: Bug reports and feature requests

### Providing Help

Ways to help the community:
- Answer questions in GitHub Discussions
- Review pull requests
- Report bugs and suggest improvements
- Improve documentation
- Share usage examples

## License

By contributing to EasyCommerce FakerPress, you agree that your contributions will be licensed under the GPL v3 or later license that covers the project.

---

**Thank you for contributing to EasyCommerce FakerPress!** 

Your contributions help make WordPress development easier for developers and agencies worldwide. Every improvement, no matter how small, makes a difference in the community.

*This contributing guide is regularly updated to reflect current best practices and community feedback.*
