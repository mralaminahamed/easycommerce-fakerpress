<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Location;

/**
 * Test class for Location REST Controller
 *
 * @covers \EasyCommerceFakerPress\Controllers\Location
 */
class LocationRESTControllerTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Location
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

		$this->controller = new Location();
		$this->controller->register_routes();

		$this->admin_user_id = $this->create_admin_user();
	}

	/**
	 * Tear down after each test
	 */
	public function tearDown(): void {
		parent::tearDown();
		$this->cleanup_test_data();
	}

	/**
	 * Test controller instantiation
	 */
	public function test_controller_instantiation(): void {
		$this->assertInstanceOf( Location::class, $this->controller );
	}

	/**
	 * Test route registration
	 */
	public function test_route_registration(): void {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/easycommerce-fakerpress/v1/locations/generate', $routes );
	}

	/**
	 * Test successful location generation
	 */
	public function test_successful_location_generation(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/locations/generate' );
		$request->set_param( 'count', 2 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'locations', $data );
		$this->assertEquals( 2, $data['generated'] );
	}
}