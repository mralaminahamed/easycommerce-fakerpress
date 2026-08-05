<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Controllers\Plugins;
use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use WP_Error;

/**
 * Test class for the Our Plugins REST controller.
 *
 * The list comes from the WordPress.org directory, so `plugins_api` is filtered
 * rather than called: what matters is that the answer is cached, that install
 * state is read from this site rather than from the cache, that the action
 * links carry core's own nonces, and that an unreachable directory degrades to
 * a message instead of an error.
 *
 * @covers \EasyCommerceFakerPress\Controllers\Plugins
 */
class PluginsRESTControllerTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Plugins
	 */
	private $controller;

	private const CACHE_KEY = 'ecfp_our_plugins';

	public function setUp(): void {
		parent::setUp();

		$this->controller = new Plugins();

		delete_transient( self::CACHE_KEY );

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	}

	public function tearDown(): void {
		remove_all_filters( 'plugins_api' );
		delete_transient( self::CACHE_KEY );
		wp_cache_delete( 'plugins', 'plugins' );

		parent::tearDown();
	}

	/**
	 * Answer the directory with a fixture rather than a network call.
	 *
	 * @param array|null $plugins Rows, or null to fail.
	 *
	 * @return void
	 */
	private function fake_directory( $plugins ): void {
		add_filter(
			'plugins_api',
			static function ( $result, $action ) use ( $plugins ) {
				if ( 'query_plugins' !== $action ) {
					return $result;
				}

				if ( null === $plugins ) {
					return new WP_Error( 'plugins_api_failed', 'The directory is unreachable.' );
				}

				return (object) array(
					'plugins' => array_map(
						static function ( $plugin ) {
							return (object) $plugin;
						},
						$plugins
					),
				);
			},
			10,
			2
		);
	}

	/**
	 * A row as the directory returns one.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return array
	 */
	private function row( string $slug ): array {
		return array(
			'slug'              => $slug,
			'name'              => ucfirst( $slug ),
			'short_description' => 'Does a useful thing.',
			'version'           => '2.0.0',
			'active_installs'   => 1000,
			'rating'            => 90,
			'num_ratings'       => 12,
			'icons'             => array( '2x' => 'https://ps.w.org/' . $slug . '/assets/icon-256.png' ),
		);
	}

	/**
	 * The endpoint's payload.
	 *
	 * @return array
	 */
	private function listing(): array {
		return (array) $this->controller->get_plugins()->get_data();
	}

	public function test_it_lists_the_authors_plugins(): void {
		$this->fake_directory( array( $this->row( 'fixture-one' ), $this->row( 'fixture-two' ) ) );

		$data = $this->listing();

		$this->assertCount( 2, $data['plugins'] );
		$this->assertSame( 'fixture-one', $data['plugins'][0]['slug'] );
		$this->assertSame( '', $data['error'] );

		// The directory scores out of 100; the screen shows five stars.
		$this->assertSame( 4.5, $data['plugins'][0]['rating'] );
	}

	public function test_it_leaves_itself_out(): void {
		$this->fake_directory(
			array( $this->row( 'easycommerce-fakerpress' ), $this->row( 'fixture-two' ) )
		);

		$slugs = wp_list_pluck( $this->listing()['plugins'], 'slug' );

		$this->assertNotContains( 'easycommerce-fakerpress', $slugs );
		$this->assertContains( 'fixture-two', $slugs );
	}

	/**
	 * The directory is asked once and then cached.
	 *
	 * A screen that makes a third-party HTTP request on every load hangs for as
	 * long as WordPress.org is having a bad day.
	 */
	public function test_the_directory_is_only_asked_once(): void {
		$calls = 0;

		add_filter(
			'plugins_api',
			function ( $result, $action ) use ( &$calls ) {
				if ( 'query_plugins' !== $action ) {
					return $result;
				}

				++$calls;

				return (object) array( 'plugins' => array( (object) $this->row( 'fixture-two' ) ) );
			},
			10,
			2
		);

		$this->listing();
		$this->listing();
		$this->listing();

		$this->assertSame( 1, $calls );
	}

	/**
	 * Install state is read from this site, not from the cached listing.
	 *
	 * Caching the two together would leave a plugin reading "not installed" for
	 * hours after somebody installed it.
	 */
	public function test_install_state_is_not_cached_with_the_listing(): void {
		/*
		 * A slug that cannot be on disk. `get_plugins()` scans the real plugins
		 * directory, which on a development machine is the one the site uses — a
		 * fixture named after something the developer happens to have installed
		 * would measure their machine rather than this code.
		 */
		$this->fake_directory( array( $this->row( 'ecfp-fixture-absent' ) ) );

		$this->assertSame( 'missing', $this->listing()['plugins'][0]['state'] );

		/*
		 * The site gains the plugin while the listing stays cached. Written into
		 * the object cache because `get_plugins()` memoises its filesystem scan
		 * under the `plugins` group and has no filter of its own.
		 */
		$cache = wp_cache_get( 'plugins', 'plugins' );
		$cache = is_array( $cache ) ? $cache : array();

		$cache['']['ecfp-fixture-absent/ecfp-fixture-absent.php'] = array(
			'Name'    => 'Fixture',
			'Version' => '1.0.0',
		);

		wp_cache_set( 'plugins', $cache, 'plugins' );

		$this->assertSame( 'inactive', $this->listing()['plugins'][0]['state'] );
	}

	/**
	 * Action links point at core's own screens, carrying core's own nonces.
	 */
	public function test_action_links_use_cores_screens_and_nonces(): void {
		$this->fake_directory( array( $this->row( 'ecfp-fixture-absent' ) ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$plugin = $this->listing()['plugins'][0];

		$this->assertStringContainsString( 'update.php', $plugin['action_url'] );
		$this->assertStringContainsString( 'action=install-plugin', $plugin['action_url'] );
		$this->assertStringContainsString( '_wpnonce=', $plugin['action_url'] );
	}

	/**
	 * Somebody who cannot install plugins is offered no link to do it with.
	 */
	public function test_a_user_without_the_capability_gets_no_action_link(): void {
		$this->fake_directory( array( $this->row( 'ecfp-fixture-absent' ) ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->assertSame( '', $this->listing()['plugins'][0]['action_url'] );
	}

	/**
	 * An unreachable directory is a message, not a failed request.
	 */
	public function test_an_unreachable_directory_degrades(): void {
		$this->fake_directory( null );

		$data = $this->listing();

		$this->assertSame( array(), $data['plugins'] );
		$this->assertNotSame( '', $data['error'] );
	}
}
