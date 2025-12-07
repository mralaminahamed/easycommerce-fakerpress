<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Product_Variation;

/**
 * Test class for Product Variation REST Controller
 *
 * @covers \EasyCommerceFakerPress\Controllers\Product_Variation
 */
class ProductVariationRESTControllerTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Product_Variation
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

		$this->controller = new Product_Variation();
		$this->controller->register_routes();

		$this->admin_user_id = $this->create_admin_user();
	}

	/**
	 * Test controller instantiation
	 */
	public function test_controller_instantiation(): void {
		$this->assertInstanceOf( Product_Variation::class, $this->controller );
	}

	/**
	 * Test route registration
	 */
	public function test_route_registration(): void {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/easycommerce-fakerpress/v1/product-variations/generate', $routes );
	}

	/**
	 * Test successful product variation generation
	 */
	public function test_successful_product_variation_generation(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/product-variations/generate' );
		$request->set_param( 'count', 2 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'product_variations', $data );
		$this->assertEquals( 2, $data['generated'] );
	}
}