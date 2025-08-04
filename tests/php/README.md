# EasyCommerce FakerPress Tests

This directory contains PHPUnit tests for the EasyCommerce FakerPress plugin.

## Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB
- WordPress test environment
- Composer dependencies installed

### Installation

1. **Install WordPress test suite:**
   ```bash
   cd tests/php/bin
   ./install-wp-tests.sh wp_test_db wp_test_user wp_test_pass localhost latest
   ```

   Where:
   - `wp_test_db` - Database name for tests (will be created)
   - `wp_test_user` - Database username
   - `wp_test_pass` - Database password
   - `localhost` - Database host
   - `latest` - WordPress version (or specific version like `6.4`)

2. **Configure test database:**
   
   Update `phpunit-wp-config.php` if needed:
   ```php
   define( 'DB_NAME', 'wp_test_db' );
   define( 'DB_USER', 'wp_test_user' );
   define( 'DB_PASSWORD', 'wp_test_pass' );
   define( 'DB_HOST', 'localhost' );
   ```

## Running Tests

### All Tests
```bash
# From plugin root directory
vendor/bin/phpunit

# Or with configuration file
vendor/bin/phpunit --configuration phpunit.xml.dist
```

### Specific Test Suite
```bash
# Run only unit tests
vendor/bin/phpunit --testsuite unit
```

### Specific Test Class
```bash
# Run specific test class
vendor/bin/phpunit tests/php/src/EasyCommerceFakerPressTest.php
```

### Specific Test Method
```bash
# Run specific test method
vendor/bin/phpunit --filter test_instance tests/php/src/EasyCommerceFakerPressTest.php
```

### With Coverage Report
```bash
# Generate HTML coverage report
vendor/bin/phpunit --coverage-html tests/coverage/html

# Generate Clover coverage report
vendor/bin/phpunit --coverage-clover tests/coverage/clover.xml
```

## Test Organization

### Directory Structure
```
tests/php/
├── bootstrap.php              # Test bootstrap file
├── phpunit-wp-config.php      # WordPress test configuration
├── bin/
│   └── install-wp-tests.sh    # WordPress test suite installer
└── src/
    ├── EasyCommerceFakerPressUnitTestCase.php    # Base test case
    ├── EasyCommerceFakerPressTest.php            # Main plugin tests
    ├── Generators/
    │   └── ProductGeneratorTest.php              # Product generator tests
    ├── Admin/
    │   └── AdminTest.php                         # Admin functionality tests
    └── TestHelpers/
        └── Traits/
            └── Wp_Rest_Request_Trait.php         # REST API test helpers
```

### Test Categories

1. **Unit Tests**: Test individual methods and classes in isolation
2. **Integration Tests**: Test component interactions
3. **Functional Tests**: Test complete features end-to-end

### Base Test Case

All tests extend `EasyCommerceFakerPressUnitTestCase` which provides:

- WordPress test environment setup
- REST API testing utilities
- Common assertion methods
- Test data cleanup
- Helper methods for creating test users and data

## Writing Tests

### Basic Test Structure
```php
<?php

namespace EasyCommerceFakerPress\Tests;

class MyComponentTest extends EasyCommerceFakerPressUnitTestCase {
    
    private $component;
    
    public function setUp(): void {
        parent::setUp();
        $this->component = new MyComponent();
    }
    
    public function tearDown(): void {
        parent::tearDown();
        $this->cleanup_test_data();
    }
    
    public function test_my_method(): void {
        $result = $this->component->my_method();
        $this->assertTrue($result);
    }
}
```

### Testing WordPress Hooks
```php
public function test_hooks_registered(): void {
    $this->component->register_hooks();
    
    $this->assertEquals(10, has_action('init', array($this->component, 'init')));
    $this->assertEquals(20, has_filter('the_content', array($this->component, 'filter_content')));
}
```

### Testing AJAX Requests
```php
public function test_ajax_handler(): void {
    $admin_user = $this->create_admin_user();
    wp_set_current_user($admin_user);
    
    $_POST['action'] = 'my_ajax_action';
    $_POST['nonce'] = wp_create_nonce('my_nonce');
    $_POST['data'] = 'test_data';
    
    $this->component->handle_ajax();
    
    $this->expectOutputString('{"success":true}');
}
```

### Testing REST API Endpoints
```php
public function test_rest_endpoint(): void {
    $request = $this->create_admin_request('POST', '/my-endpoint', array(
        'param1' => 'value1',
        'param2' => 'value2'
    ));
    
    $response = rest_do_request($request);
    
    $this->assertResponseStatus(200, $response);
    $this->assertResponseHasKeys(array('success', 'data'), $response);
}
```

## Test Data Management

### Creating Test Data
```php
// Create test users
$admin_id = $this->create_admin_user();
$customer_id = $this->create_customer_user();

// Create test posts
$post_id = $this->factory->post->create(array(
    'post_title' => 'Test Product',
    'post_type' => 'product'
));

// Create test terms
$category_id = $this->factory->term->create(array(
    'taxonomy' => 'product_category',
    'name' => 'Test Category'
));
```

### Cleanup
The base test case provides `cleanup_test_data()` method that:
- Removes test posts and their metadata
- Removes test users and their metadata
- Cleans up orphaned database entries

## Debugging Tests

### Enable Debug Mode
```php
// In phpunit-wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
```

### Output Debugging
```php
public function test_debug_example(): void {
    $data = $this->component->get_data();
    
    // Debug output (only in debug mode)
    error_log('Debug data: ' . print_r($data, true));
    
    $this->assertNotEmpty($data);
}
```

### Test Isolation
Each test method runs in isolation:
- Database transactions are rolled back
- Global state is reset
- Temporary data is cleaned up

## Continuous Integration

Tests are designed to run in CI environments:

### GitHub Actions Example
```yaml
- name: Setup test database
  run: |
    mysql -e 'CREATE DATABASE wp_test_db;' -uroot -proot
    
- name: Install WordPress test suite
  run: |
    cd tests/php/bin
    ./install-wp-tests.sh wp_test_db root root localhost latest
    
- name: Run tests
  run: vendor/bin/phpunit
```

## Performance Testing

### Memory Usage
```php
public function test_memory_usage(): void {
    $memory_before = memory_get_usage();
    
    $this->component->process_large_dataset();
    
    $memory_after = memory_get_usage();
    $memory_used = $memory_after - $memory_before;
    
    $this->assertLessThan(10 * 1024 * 1024, $memory_used); // Less than 10MB
}
```

### Execution Time
```php
public function test_execution_time(): void {
    $start_time = microtime(true);
    
    $this->component->complex_operation();
    
    $execution_time = microtime(true) - $start_time;
    
    $this->assertLessThan(5, $execution_time); // Less than 5 seconds
}
```

## Best Practices

1. **Test Names**: Use descriptive names that explain what is being tested
2. **One Assertion Per Test**: Focus each test on a single behavior
3. **Test Both Success and Failure**: Test both happy path and error conditions
4. **Use Mocks**: Mock external dependencies to isolate units under test
5. **Clean Up**: Always clean up test data to prevent test pollution
6. **Fast Tests**: Keep tests fast by avoiding unnecessary setup
7. **Readable Tests**: Write tests that serve as documentation

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Check database credentials in `phpunit-wp-config.php`
   - Ensure test database exists and is accessible

2. **Plugin Not Loading**
   - Verify plugin path in `bootstrap.php`
   - Check for PHP syntax errors in plugin files

3. **WordPress Not Found**
   - Run `install-wp-tests.sh` script
   - Check `WP_TESTS_DIR` environment variable

4. **Memory Limit Errors**
   - Increase PHP memory limit: `ini_set('memory_limit', '512M')`
   - Optimize test data creation

5. **Timeout Issues**
   - Increase PHPUnit timeout in configuration
   - Optimize slow database operations in tests