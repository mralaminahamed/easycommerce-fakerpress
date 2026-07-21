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
}
