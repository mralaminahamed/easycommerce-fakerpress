<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Tax_Class;

/**
 * Test class for Tax Class REST Controller
 *
 * @covers \EasyCommerceFakerPress\Controllers\Tax_Class
 */
class TaxClassRESTControllerTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Tax_Class
	 */
	private $controller;

	/**
	 * @var int
	 */
	private $admin_user_id;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active
		if ( ! class_exists( 'EasyCommerce\Models\Product' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->controller = new Tax_Class();
		$this->controller->register_routes();

		$this->admin_user_id = $this->create_admin_user();
	}

	/**
	 * Test controller instantiation
	 */
	public function test_controller_instantiation(): void {
		$this->assertInstanceOf( Tax_Class::class, $this->controller );
	}

	/**
	 * Test route registration
	 */
	public function test_route_registration(): void {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/easycommerce-fakerpress/v1/tax-classes/generate', $routes );
	}

	/**
	 * Test successful tax class generation
	 */
	public function test_successful_tax_class_generation(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/tax-classes/generate' );
		$request->set_param( 'count', 2 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'tax_classes', $data );
		$this->assertEquals( 2, $data['generated'] );
	}
}