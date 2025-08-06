<?php
/**
 * Abstract Generator Class.
 *
 * @package EasyCommerceFakerPress\Abstracts
 * @since   1.0.0
 */

namespace EasyCommerceFakerPress\Abstracts;

use Bluemmb\Faker\PicsumPhotosProvider;
use Exception;
use Faker\Factory;
use Faker\Generator as FakerGenerator;
use Faker\Provider\DateTime;
use WP_Error;
use wpdb;

/**
 * Abstract Generator Class
 *
 * Base class for all data generators with common functionality
 *
 * @since 1.0.0
 */
abstract class Generator {
	/**
	 * Faker instance
	 *
	 * @var FakerGenerator
	 */
	protected FakerGenerator $faker;

	/**
	 * WordPress database instance
	 *
	 * @var \wpdb
	 */
	protected wpdb $wpdb;

	/**
	 * Maximum items to generate per batch
	 *
	 * @var int
	 */
	protected int $max_batch_size = 100;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb  = $wpdb;
		$this->faker = Factory::create( get_locale() );

		// Add addtional providers.
		$this->faker->addProvider( new DateTime( $this->faker ) );
		$this->faker->addProvider( new PicsumPhotosProvider( $this->faker ) );
	}

	/**
	 * Generate fake data
	 *
	 * @since 1.0.0
	 *
	 * @param int $count Number of items to generate.
	 *
	 * @return array<string, mixed>|WP_Error Generation results or error.
	 */
	public function generate( int $count ) {
		// Validate count.
		$validation_result = $this->validate_count( $count );
		if ( is_wp_error( $validation_result ) ) {
			return $validation_result;
		}

		$results = array();

		try {
			for ( $i = 0; $i < $count; $i++ ) {
				$item_result = $this->generate_single_item();

				if ( is_wp_error( $item_result ) ) {
					// Continue with other items but log the error.
					continue;
				}

				if ( $item_result ) {
					$results[] = $item_result;
				}
			}

			return $this->format_results( $results );
		} catch ( Exception $e ) {
			return new WP_Error(
				'generation_failed',
				sprintf(
				/* translators: %s: Error message */
					__( 'Generation failed: %s', 'easycommerce-fakerpress' ),
					$e->getMessage()
				)
			);
		}
	}

	/**
	 * Generate a single item
	 *
	 * Must be implemented by child classes
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|WP_Error|false Single item data, error, or false on failure.
	 */
	abstract protected function generate_single_item();

	/**
	 * Get the resource type name (e.g., 'product', 'customer')
	 *
	 * Must be implemented by child classes
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type name.
	 */
	abstract protected function get_resource_type(): string;

	/**
	 * Get the plural resource type name (e.g., 'products', 'customers')
	 *
	 * @since 1.0.0
	 *
	 * @return string Plural resource type name.
	 */
	protected function get_resource_type_plural(): string {
		return $this->get_resource_type() . 's';
	}

	/**
	 * Validate generation count
	 *
	 * @since 1.0.0
	 *
	 * @param int $count Number of items to generate.
	 *
	 * @return true|WP_Error True if valid, WP_Error otherwise.
	 */
	protected function validate_count( int $count ) {
		if ( $count <= 0 ) {
			return new WP_Error(
				'invalid_count',
				__( 'Count must be a positive number.', 'easycommerce-fakerpress' )
			);
		}

		if ( $count > $this->max_batch_size ) {
			return new WP_Error(
				'count_too_large',
				sprintf(
				/* translators: %d: Maximum batch size */
					__( 'Count cannot exceed %d items per batch.', 'easycommerce-fakerpress' ),
					$this->max_batch_size
				)
			);
		}

		return true;
	}

	/**
	 * Format generation results
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, mixed> $results Generated items data.
	 *
	 * @return array<string, mixed> Formatted results.
	 */
	protected function format_results( array $results ): array {
		return array(
			'generated'                       => count( $results ),
			$this->get_resource_type_plural() => $results,
		);
	}

	/**
	 * Log generation activity
	 *
	 * @since 1.0.0
	 *
	 * @param string               $message Log message.
	 * @param string               $level   Log level (info, warning, error).
	 * @param array<string, mixed> $context Additional context data.
	 *
	 * @return void
	 */
	protected function log( string $message, string $level = 'info', array $context = array() ): void {
		if ( function_exists( 'error_log' ) && WP_DEBUG_LOG ) {
			$context['resource_type'] = $this->get_resource_type();
			$log_message              = sprintf(
				'[EasyCommerce FakerPress] [%s] [%s] %s %s',
				strtoupper( $level ),
				$this->get_resource_type(),
				$message,
				! empty( $context ) ? '- Context: ' . wp_json_encode( $context ) : ''
			);
			error_log( $log_message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
