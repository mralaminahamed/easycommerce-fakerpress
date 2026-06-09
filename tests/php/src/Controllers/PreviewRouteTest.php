<?php
/**
 * Test class for the read-only preview REST routes.
 *
 * Verifies that GET /easycommerce-fakerpress/v1/{resource}/preview returns
 * the correct shape and does NOT persist any data to the database.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Tests\Controllers
 */

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Product  as ProductController;
use EasyCommerceFakerPress\Controllers\Customer as CustomerController;
use EasyCommerceFakerPress\Controllers\Order    as OrderController;
use EasyCommerceFakerPress\Controllers\Coupon   as CouponController;

/**
 * Preview Route integration test.
 *
 * @covers \EasyCommerceFakerPress\Abstracts\Controller::preview_items
 * @covers \EasyCommerceFakerPress\Abstracts\Generator::preview
 */
class PreviewRouteTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * Admin user ID used for authenticated requests.
	 *
	 * @var int
	 */
	private int $admin_user_id;

	/**
	 * Set up REST server and register all four preview routes.
	 */
	public function setUp(): void {
		parent::setUp();

		// Register the four Core controllers so their routes are available.
		( new ProductController() )->register_routes();
		( new CustomerController() )->register_routes();
		( new OrderController() )->register_routes();
		( new CouponController() )->register_routes();

		$this->admin_user_id = $this->create_admin_user();
	}

	// -----------------------------------------------------------------------
	// Helper
	// -----------------------------------------------------------------------

	/**
	 * Assert that a single row cell array is well-formed.
	 *
	 * @param mixed  $cell Cell value from a preview row.
	 * @param string $key  Cell key (for the failure message).
	 */
	private function assertCellShape( $cell, string $key ): void {
		$this->assertIsArray( $cell, "Cell '{$key}' must be an array" );
		$this->assertArrayHasKey( 'v', $cell, "Cell '{$key}' must have key 'v'" );
		$this->assertArrayHasKey( 'kind', $cell, "Cell '{$key}' must have key 'kind'" );
	}

	// -----------------------------------------------------------------------
	// Products preview
	// -----------------------------------------------------------------------

	/**
	 * Verify the products/preview route exists.
	 */
	public function test_products_preview_route_registered(): void {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey(
			'/' . $this->namespace . '/products/preview',
			$routes,
			'products/preview route is not registered'
		);
	}

	/**
	 * Core assertion: status 200 + correct shape + no persistence.
	 */
	public function test_preview_returns_columns_and_rows_without_persisting(): void {
		wp_set_current_user( $this->admin_user_id );

		// Count EC products before (using wpdb if the EC post type is not
		// a standard WP post type; fall back to counting wp_posts rows).
		global $wpdb;
		$before_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}ec_products WHERE 1 = %d",
				1
			)
		);

		$req = $this->get_wp_rest_request( 'POST', '/products/preview' );
		$req->set_param( 'count', 5 );

		$res = $this->server->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );

		$data = $res->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'columns', $data );
		$this->assertArrayHasKey( 'rows', $data );
		$this->assertCount( 5, $data['rows'] );

		// No new EC products must have been created.
		$after_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}ec_products WHERE 1 = %d",
				1
			)
		);
		$this->assertSame(
			$before_count,
			$after_count,
			'preview must not persist products to the database'
		);
	}

	/**
	 * Verify columns definition and row cell shape for products.
	 */
	public function test_products_preview_shape(): void {
		wp_set_current_user( $this->admin_user_id );

		$req = $this->get_wp_rest_request( 'POST', '/products/preview' );
		$req->set_param( 'count', 3 );

		$res  = $this->server->dispatch( $req );
		$data = $res->get_data();

		$this->assertSame( 200, $res->get_status() );

		// Columns must be a non-empty list of arrays with 'key' and 'label'.
		$this->assertNotEmpty( $data['columns'] );
		foreach ( $data['columns'] as $col ) {
			$this->assertArrayHasKey( 'key', $col );
			$this->assertArrayHasKey( 'label', $col );
		}

		// Every row must have a cell for each declared column.
		$column_keys = array_column( $data['columns'], 'key' );
		foreach ( $data['rows'] as $row ) {
			foreach ( $column_keys as $key ) {
				$this->assertArrayHasKey( $key, $row, "Row missing cell '{$key}'" );
				$this->assertCellShape( $row[ $key ], $key );
			}
		}
	}

	// -----------------------------------------------------------------------
	// Customers preview
	// -----------------------------------------------------------------------

	/**
	 * Verify the customers/preview route exists.
	 */
	public function test_customers_preview_route_registered(): void {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey(
			'/' . $this->namespace . '/customers/preview',
			$routes,
			'customers/preview route is not registered'
		);
	}

	/**
	 * Core assertion: status 200 + correct shape + no persistence for customers.
	 */
	public function test_customers_preview_returns_columns_and_rows_without_persisting(): void {
		wp_set_current_user( $this->admin_user_id );

		// Count WP users before (customers are WP users).
		$before_count = (int) ( new \WP_User_Query( array( 'count_total' => true ) ) )->get_total();

		$req = $this->get_wp_rest_request( 'POST', '/customers/preview' );
		$req->set_param( 'count', 5 );

		$res = $this->server->dispatch( $req );

		$this->assertSame( 200, $res->get_status() );

		$data = $res->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'columns', $data );
		$this->assertArrayHasKey( 'rows', $data );
		$this->assertCount( 5, $data['rows'] );

		// No new users must have been created.
		$after_count = (int) ( new \WP_User_Query( array( 'count_total' => true ) ) )->get_total();
		$this->assertSame(
			$before_count,
			$after_count,
			'preview must not persist customers (WP users) to the database'
		);
	}

	/**
	 * Verify columns definition and row cell shape for customers.
	 */
	public function test_customers_preview_shape(): void {
		wp_set_current_user( $this->admin_user_id );

		$req = $this->get_wp_rest_request( 'POST', '/customers/preview' );
		$req->set_param( 'count', 3 );

		$res  = $this->server->dispatch( $req );
		$data = $res->get_data();

		$this->assertSame( 200, $res->get_status() );

		$this->assertNotEmpty( $data['columns'] );
		foreach ( $data['columns'] as $col ) {
			$this->assertArrayHasKey( 'key', $col );
			$this->assertArrayHasKey( 'label', $col );
		}

		$column_keys = array_column( $data['columns'], 'key' );
		foreach ( $data['rows'] as $row ) {
			foreach ( $column_keys as $key ) {
				$this->assertArrayHasKey( $key, $row, "Row missing cell '{$key}'" );
				$this->assertCellShape( $row[ $key ], $key );
			}
		}
	}

	// -----------------------------------------------------------------------
	// Orders preview
	// -----------------------------------------------------------------------

	/**
	 * Verify the orders/preview route exists.
	 */
	public function test_orders_preview_route_registered(): void {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey(
			'/' . $this->namespace . '/orders/preview',
			$routes,
			'orders/preview route is not registered'
		);
	}

	/**
	 * Core assertion: status 200 + correct shape for orders.
	 */
	public function test_orders_preview_shape(): void {
		wp_set_current_user( $this->admin_user_id );

		$req = $this->get_wp_rest_request( 'POST', '/orders/preview' );
		$req->set_param( 'count', 3 );

		$res  = $this->server->dispatch( $req );
		$data = $res->get_data();

		$this->assertSame( 200, $res->get_status() );
		$this->assertArrayHasKey( 'columns', $data );
		$this->assertArrayHasKey( 'rows', $data );
		$this->assertCount( 3, $data['rows'] );

		$column_keys = array_column( $data['columns'], 'key' );
		foreach ( $data['rows'] as $row ) {
			foreach ( $column_keys as $key ) {
				$this->assertArrayHasKey( $key, $row );
				$this->assertCellShape( $row[ $key ], $key );
			}
		}
	}

	// -----------------------------------------------------------------------
	// Coupons preview
	// -----------------------------------------------------------------------

	/**
	 * Verify the coupons/preview route exists.
	 */
	public function test_coupons_preview_route_registered(): void {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey(
			'/' . $this->namespace . '/coupons/preview',
			$routes,
			'coupons/preview route is not registered'
		);
	}

	/**
	 * Core assertion: status 200 + correct shape for coupons.
	 */
	public function test_coupons_preview_shape(): void {
		wp_set_current_user( $this->admin_user_id );

		$req = $this->get_wp_rest_request( 'POST', '/coupons/preview' );
		$req->set_param( 'count', 3 );

		$res  = $this->server->dispatch( $req );
		$data = $res->get_data();

		$this->assertSame( 200, $res->get_status() );
		$this->assertArrayHasKey( 'columns', $data );
		$this->assertArrayHasKey( 'rows', $data );
		$this->assertCount( 3, $data['rows'] );

		$column_keys = array_column( $data['columns'], 'key' );
		foreach ( $data['rows'] as $row ) {
			foreach ( $column_keys as $key ) {
				$this->assertArrayHasKey( $key, $row );
				$this->assertCellShape( $row[ $key ], $key );
			}
		}
	}

	// -----------------------------------------------------------------------
	// Permission checks
	// -----------------------------------------------------------------------

	/**
	 * Unauthenticated requests must receive 401.
	 */
	public function test_preview_requires_authentication(): void {
		wp_set_current_user( 0 );

		$req = $this->get_wp_rest_request( 'POST', '/products/preview' );
		$req->set_param( 'count', 3 );

		$res = $this->server->dispatch( $req );
		$this->assertSame( 401, $res->get_status() );
	}

	/**
	 * Count parameter enforcement: preview clamps to 1–25.
	 */
	public function test_preview_count_clamped_to_25(): void {
		wp_set_current_user( $this->admin_user_id );

		$req = $this->get_wp_rest_request( 'POST', '/products/preview' );
		$req->set_param( 'count', 99 );

		$res  = $this->server->dispatch( $req );
		$data = $res->get_data();

		$this->assertSame( 200, $res->get_status() );
		// Generator::preview() clamps max to 25.
		$this->assertCount( 25, $data['rows'] );
	}
}
