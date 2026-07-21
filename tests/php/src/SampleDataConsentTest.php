<?php
/**
 * Tests for sample-data consent option and REST surface.
 *
 * @package EasyCommerceFakerPress\Tests
 */

namespace EasyCommerceFakerPress\Tests;

use EasyCommerce_FakerPress;
use WP_REST_Request;

/**
 * @covers \EasyCommerce_FakerPress
 */
class SampleDataConsentTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var EasyCommerce_FakerPress
	 */
	private EasyCommerce_FakerPress $plugin;

	public function setUp(): void {
		parent::setUp();
		$this->plugin = easycommerce_fakerpress();

		// register_rest_routes() is gated on check_dependencies(), which checks
		// is_plugin_active(). The test bootstrap loads EasyCommerce directly via
		// muplugins_loaded rather than through the `active_plugins` option, so
		// mock it active here — mirrors EasyCommerceFakerPressTest's pattern —
		// so route registration below reflects real (activated-plugin) behavior.
		add_filter(
			'option_active_plugins',
			function ( $plugins ) {
				$plugins[] = 'easycommerce/easycommerce.php';
				return $plugins;
			}
		);

		do_action( 'rest_api_init' );
		delete_option( 'easycommerce_fakerpress_sample_data_consent' );
	}

	public function tearDown(): void {
		delete_option( 'easycommerce_fakerpress_sample_data_consent' );
		unset( $this->plugin );
		parent::tearDown();
	}

	public function test_consent_defaults_to_empty(): void {
		$this->assertSame( '', $this->plugin->get_sample_data_consent() );
	}

	public function test_set_consent_persists_granted(): void {
		$this->plugin->set_sample_data_consent( 'granted' );
		$this->assertSame( 'granted', $this->plugin->get_sample_data_consent() );
	}

	public function test_set_consent_persists_declined(): void {
		$this->plugin->set_sample_data_consent( 'declined' );
		$this->assertSame( 'declined', $this->plugin->get_sample_data_consent() );
	}

	public function test_set_consent_ignores_invalid_value(): void {
		$this->plugin->set_sample_data_consent( 'maybe' );
		$this->assertSame( '', $this->plugin->get_sample_data_consent() );
	}

	public function test_status_reports_null_consent_when_undecided(): void {
		$data = $this->plugin->rest_sample_data_status()->get_data();
		$this->assertArrayHasKey( 'consent', $data );
		$this->assertNull( $data['consent'] );
	}

	public function test_status_reports_consent_value(): void {
		$this->plugin->set_sample_data_consent( 'declined' );
		$data = $this->plugin->rest_sample_data_status()->get_data();
		$this->assertSame( 'declined', $data['consent'] );
	}

	public function test_consent_route_records_declined(): void {
		$request = new WP_REST_Request( 'POST', '/easycommerce-fakerpress/v1/download-sample/consent' );
		$request->set_param( 'granted', false );

		$response = $this->plugin->rest_set_sample_data_consent( $request );

		$this->assertSame( 'declined', $this->plugin->get_sample_data_consent() );
		$this->assertSame( 'declined', $response->get_data()['consent'] );
	}

	public function test_consent_route_records_granted(): void {
		$request = new WP_REST_Request( 'POST', '/easycommerce-fakerpress/v1/download-sample/consent' );
		$request->set_param( 'granted', true );

		$response = $this->plugin->rest_set_sample_data_consent( $request );

		$this->assertSame( 'granted', $this->plugin->get_sample_data_consent() );
		$this->assertSame( 'granted', $response->get_data()['consent'] );
	}

	public function test_permission_check_denies_subscriber(): void {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$this->assertFalse( $this->plugin->rest_permission_check() );
	}

	public function test_permission_check_allows_admin(): void {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$this->assertTrue( $this->plugin->rest_permission_check() );
	}

	public function test_consent_route_is_registered(): void {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/easycommerce-fakerpress/v1/download-sample/consent', $routes );
	}
}
