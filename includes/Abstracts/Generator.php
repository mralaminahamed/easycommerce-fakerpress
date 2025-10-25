<?php
/**
 * Abstract Generator Class.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Abstracts
 */

namespace EasyCommerceFakerPress\Abstracts;

use Bluemmb\Faker\PicsumPhotosProvider;
use Exception;
use Faker\Factory;
use Faker\Generator as Faker_Generator;
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
	 * @since 1.0.0
	 * @var Faker_Generator
	 */
	protected Faker_Generator $faker;

	/**
	 * WordPress database instance
	 *
	 * @since 1.0.0
	 * @var \wpdb
	 */
	protected wpdb $wpdb;

	/**
	 * Maximum items to generate per batch
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected int $max_batch_size = 100;

	/**
	 * Locale for Faker
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $locale;

	/**
	 * Generation parameters from REST API
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	protected array $generation_params = array();

	/**
	 * Constructor
	 *
	 * Initializes the database reference.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;
	}

	/**
	 * Set Faker instance
	 *
	 * Configures the Faker generator with providers.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function set_faker(): void {
		$this->faker = Factory::create( $this->get_faker_locale() );

		// Add additional providers directly to avoid recursive calls.
		$this->faker->addProvider( new DateTime( $this->faker ) );
		$this->faker->addProvider( new PicsumPhotosProvider( $this->faker ) );
	}

	/**
	 * Get Faker instance
	 *
	 * @since 1.0.0
	 *
	 * @return Faker_Generator Faker instance.
	 */
	public function get_faker(): Faker_Generator {
		return $this->faker;
	}

	/**
	 * Set locale for Faker
	 *
	 * @since 1.0.0
	 *
	 * @param string $locale Locale code (e.g., 'en_US').
	 *
	 * @return void
	 */
	public function set_locale( string $locale = 'en_US' ): void {
		$this->locale = $locale;
	}

	/**
	 * Get locale for Faker
	 *
	 * @since 1.0.0
	 *
	 * @return string Locale code.
	 */
	public function get_faker_locale(): string {
		return $this->locale;
	}

	/**
	 * Generate fake data
	 *
	 * Orchestrates batch generation of items.
	 *
	 * @since 1.0.0
	 *
	 * @param int $count Number of items to generate.
	 *
	 * @return array<string, mixed>|WP_Error Generation results or error.
	 */
	public function generate( int $count ) {
		$resource_type = $this->get_resource_type();

		// Validate count.
		$validation_result = $this->validate_count( $count );
		if ( is_wp_error( $validation_result ) ) {
			return $validation_result;
		}

		/**
		 * Apply filtered generation params for the current resource type.
		 * Allows modification of generation parameters through WordPress filters.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, mixed> $generation_params Generation parameters to be filtered
		 */
		$this->generation_params = apply_filters( "easycommerce_fakerpress_generation_params_{$resource_type}", $this->generation_params );

		// Apply seed for reproducibility if provided.
		$seed = $this->generation_params['seed'] ?? null;
		if ( $seed ) {
			$this->faker->seed( (int) $seed );
			$this->log( "Seeded Faker with value: {$seed}", 'info' );
		}

		$results = array();

		try {
			for ( $i = 0; $i < $count; $i++ ) {
				/**
				 * Fires before generating a single item of the specified resource type.
				 *
				 * @since 1.0.0
				 * @see   Generator::generate()
				 *
				 * @param string $resource_type The type of resource being generated (e.g. 'product', 'customer')
				 */
				do_action( "easycommerce_fakerpress_before_generate_single_item_{$resource_type}" );

				try {
					$item_result = $this->generate_single_item();

					if ( is_wp_error( $item_result ) ) {
						$this->log( 'Single item generation failed: ' . $item_result->get_error_message(), 'warning' );
						continue;
					}

					/**
					 * Filters the generated item of the specified resource type.
					 *
					 * @since 1.0.0
					 *
					 * @param array<string,mixed>|WP_Error $item_result The generated item result
					 * @param int                          $i           The current item index in the generation loop
					 */
					$item_result = apply_filters( "easycommerce_fakerpress_generated_item_{$resource_type}", $item_result, $i );

					if ( $item_result ) {
						$results[] = $item_result;
					}

					/**
					 * Fires after generating a single item of the specified resource type.
					 *
					 * @since 1.0.0
					 * @see   Generator::generate()
					 *
					 * @param array<string,mixed>|WP_Error $item_result The generated item result
					 * @param int                          $i           The current item index in the generation loop
					 */
					do_action( "easycommerce_fakerpress_after_generate_single_item_{$resource_type}", $item_result, $i );
				} catch ( Exception $e ) {
					$this->log( "Per-item exception: {$e->getMessage()}", 'error' );
					continue;
				}
			}

			/**
			 * Fires after completing a batch generation of the specified resource type.
			 *
			 * @since 1.0.0
			 * @see   Generator::generate()
			 *
			 * @param array<int,mixed> $results All generated items in the batch
			 * @param int              $count   Total number of items attempted to generate
			 */
			do_action( "easycommerce_fakerpress_after_batch_generate_{$resource_type}", $results, $count );

			return $results;
		} catch ( Exception $e ) {
			$this->log( 'Batch generation exception: ' . $e->getMessage(), 'error' );
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
	 * Must be implemented by child classes to define item-specific generation logic.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|WP_Error Single item data or error.
	 */
	abstract protected function generate_single_item();

	/**
	 * Get the resource type name (e.g., 'product', 'customer')
	 *
	 * Must be implemented by child classes.
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type name.
	 */
	abstract protected function get_resource_type(): string;

	/**
	 * Set generation parameters
	 *
	 * Stores parameters for use during generation.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $params Generation parameters from request.
	 *
	 * @return void
	 */
	public function set_generation_params( array $params ): void {
		$this->generation_params = $params;
	}

	/**
	 * Validate generation count
	 *
	 * Ensures the count adheres to batch limits.
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
	 * Log generation activity
	 *
	 * Records activity for debugging, conditional on WP_DEBUG_LOG.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $message Log message.
	 * @param string               $level   Log level (e.g., 'info', 'warning', 'error').
	 * @param array<string, mixed> $context Additional context data.
	 *
	 * @return void
	 */
	public function log( string $message, string $level = 'info', array $context = array() ): void {
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
