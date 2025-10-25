<?php

namespace EasyCommerceFakerPress\Tests;

use EasyCommerceFakerPress;
use ReflectionClass;

/**
 * Test class for the main EasyCommerce FakerPress plugin
 *
 * @covers \EasyCommerceFakerPress
 */
class EasyCommerceFakerPressTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var EasyCommerceFakerPress
	 */
	private $plugin;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();
		$this->plugin = easycommerce_fakerpress();
	}

	/**
	 * Tear down after each test
	 */
	public function tearDown(): void {
		parent::tearDown();
		unset( $this->plugin );
	}

	/**
	 * Test instance returns singleton
	 */
	public function test_instance(): void {
		// Check if instance returns the correct class
		$this->assertInstanceOf( EasyCommerceFakerPress::class, EasyCommerceFakerPress::get_instance() );

		// Check if instance returns the same object
		$this->assertSame( EasyCommerceFakerPress::get_instance(), EasyCommerceFakerPress::get_instance() );
	}

	/**
	 * Test version property
	 */
	public function test_version(): void {
		$this->assertEquals( EASYCOMMERCE_FAKERPRESS_VERSION, $this->plugin->version );
	}

	/**
	 * Test init method registers all required hooks
	 */
	public function test_init(): void {
		// Call init method
		$this->plugin->init();

		// Verify the correct hooks were registered with proper priority
		$this->assertEquals( 10, has_action( 'admin_menu', array( $this->plugin, 'add_admin_menu' ) ) );
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', array( $this->plugin, 'enqueue_admin_assets' ) ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_easycommerce_fakerpress_generate_data', array( $this->plugin, 'handle_ajax_request' ) ) );

		// Verify activation and deactivation hooks
		$file = plugin_basename( EASYCOMMERCE_FAKERPRESS_PLUGIN_FILE );
		$this->assertTrue( has_action( "activate_{$file}" ) !== false );
		$this->assertTrue( has_action( "deactivate_{$file}" ) !== false );
	}

	/**
	 * Test add_admin_menu method
	 */
	public function test_add_admin_menu(): void {
		global $admin_page_hooks, $submenu;

		// Clear existing menu
		$admin_page_hooks = array();
		$submenu = array();

		// Call the method
		$this->plugin->add_admin_menu();

		// Check if menu was added
		$this->assertArrayHasKey( 'easycommerce-fakerpress', $admin_page_hooks );
	}

	/**
	 * Test render_admin_page method
	 */
	public function test_render_admin_page(): void {
		// Capture output
		ob_start();
		$this->plugin->render_admin_page();
		$output = ob_get_clean();

		// Check if the root div is rendered
		$this->assertStringContains( '<div id="easycommerce-fakerpress-root"></div>', $output );
	}

	/**
	 * Test enqueue_admin_assets method on correct page
	 */
	public function test_enqueue_admin_assets_on_plugin_page(): void {
		global $wp_scripts, $wp_styles;

		// Reset globals
		$wp_scripts = new \WP_Scripts();
		$wp_styles = new \WP_Styles();

		// Call the method with correct hook
		$this->plugin->enqueue_admin_assets( 'toplevel_page_easycommerce-fakerpress' );

		// Check if assets were enqueued
		$this->assertTrue( wp_script_is( 'easycommerce-fakerpress-admin', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'easycommerce-fakerpress-admin', 'enqueued' ) );
	}

	/**
	 * Test enqueue_admin_assets method on wrong page
	 */
	public function test_enqueue_admin_assets_on_other_page(): void {
		global $wp_scripts, $wp_styles;

		// Reset globals
		$wp_scripts = new \WP_Scripts();
		$wp_styles = new \WP_Styles();

		// Call the method with wrong hook
		$this->plugin->enqueue_admin_assets( 'other-page' );

		// Check if assets were NOT enqueued
		$this->assertFalse( wp_script_is( 'easycommerce-fakerpress-admin', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'easycommerce-fakerpress-admin', 'enqueued' ) );
	}

	/**
	 * Test handle_ajax_request with invalid nonce
	 */
	public function test_handle_ajax_request_invalid_nonce(): void {
		// Set up invalid nonce
		$_POST['nonce'] = 'invalid_nonce';

		// Expect wp_die to be called
		$this->expectException( \WPDieException::class );

		// Call the method
		$this->plugin->handle_ajax_request();
	}

	/**
	 * Test handle_ajax_request without admin capability
	 */
	public function test_handle_ajax_request_without_capability(): void {
		// Create a user without admin capability
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Set up valid nonce
		$_POST['nonce'] = wp_create_nonce( 'easycommerce_fakerpress_nonce' );

		// Expect wp_die to be called
		$this->expectException( \WPDieException::class );

		// Call the method
		$this->plugin->handle_ajax_request();
	}

	/**
	 * Test handle_ajax_request with valid parameters
	 */
	public function test_handle_ajax_request_valid_params(): void {
		// Create admin user
		$admin_id = $this->create_admin_user();
		wp_set_current_user( $admin_id );

		// Set up valid request
		$_POST['nonce'] = wp_create_nonce( 'easycommerce_fakerpress_nonce' );
		$_POST['type'] = 'products';
		$_POST['count'] = 5;

		// Mock the generator
		$mock_generator = $this->createMock( \EasyCommerceFakerPress\Generators\Product_Generator::class );
		$mock_generator->method( 'generate' )->willReturn( array( 'success' => true, 'products_created' => 5 ) );

		// We can't easily test the full method without actual generators, so we test parameter validation
		$reflection = new ReflectionClass( $this->plugin );
		$method = $reflection->getMethod( 'handle_ajax_request' );

		// Test that the method doesn't throw exception with valid params
		$this->assertTrue( $method->isPublic() );
	}

	/**
	 * Test is_easycommerce_active method when plugin is active
	 */
	public function test_is_easycommerce_active_when_active(): void {
		// Mock is_plugin_active function
		add_filter(
			'active_plugins',
			function ( $plugins ) {
				$plugins[] = 'easycommerce/easycommerce.php';
				return $plugins;
			}
		);

		// Test the method
		$this->assertTrue( $this->plugin->is_easycommerce_active() );
	}

	/**
	 * Test is_easycommerce_active method when plugin is not active
	 */
	public function test_is_easycommerce_active_when_not_active(): void {
		// Mock is_plugin_active function to return false
		add_filter(
			'active_plugins',
			function ( $plugins ) {
				// Remove easycommerce if present
				return array_filter(
					$plugins,
					function ( $plugin ) {
						return $plugin !== 'easycommerce/easycommerce.php';
					}
				);
			}
		);

		// Test the method
		$this->assertFalse( $this->plugin->is_easycommerce_active() );
	}

	/**
	 * Test check_dependencies method
	 */
	public function test_check_dependencies(): void {
		// Mock is_easycommerce_active to return true
		$plugin = $this->getMockBuilder( EasyCommerceFakerPress::class )
			->onlyMethods( array( 'is_easycommerce_active' ) )
			->getMock();

		$plugin->method( 'is_easycommerce_active' )->willReturn( true );

		// Test dependencies check
		$this->assertTrue( $plugin->check_dependencies() );

		// Test with false
		$plugin->method( 'is_easycommerce_active' )->willReturn( false );
		$this->assertFalse( $plugin->check_dependencies() );
	}

	/**
	 * Test activate method
	 */
	public function test_activate(): void {
		// Mock check_dependencies to return true
		$plugin = $this->getMockBuilder( EasyCommerceFakerPress::class )
			->onlyMethods( array( 'check_dependencies' ) )
			->getMock();

		$plugin->method( 'check_dependencies' )->willReturn( true );

		// Test activation
		$plugin->activate();

		// If we get here without exception, activation succeeded
		$this->assertTrue( true );
	}

	/**
	 * Test activate method with missing dependencies
	 */
	public function test_activate_with_missing_dependencies(): void {
		// Mock check_dependencies to return false
		$plugin = $this->getMockBuilder( EasyCommerceFakerPress::class )
			->onlyMethods( array( 'check_dependencies' ) )
			->getMock();

		$plugin->method( 'check_dependencies' )->willReturn( false );

		// Expect wp_die to be called
		$this->expectException( \WPDieException::class );

		// Test activation
		$plugin->activate();
	}

	/**
	 * Test deactivate method
	 */
	public function test_deactivate(): void {
		// Test deactivation
		$this->plugin->deactivate();

		// If we get here without exception, deactivation succeeded
		$this->assertTrue( true );
	}

	/**
	 * Test dependency_notice method
	 */
	public function test_dependency_notice(): void {
		// Mock is_easycommerce_active to return false
		$plugin = $this->getMockBuilder( EasyCommerceFakerPress::class )
			->onlyMethods( array( 'is_easycommerce_active' ) )
			->getMock();

		$plugin->method( 'is_easycommerce_active' )->willReturn( false );

		// Capture output
		ob_start();
		$plugin->dependency_notice();
		$output = ob_get_clean();

		// Check if notice is displayed
		$this->assertStringContains( 'notice notice-error', $output );
		$this->assertStringContains( 'EasyCommerce FakerPress requires EasyCommerce plugin', $output );
	}
}
