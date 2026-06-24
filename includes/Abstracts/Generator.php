<?php
/**
 * Abstract Generator Class for EasyCommerce FakerPress
 *
 * Base class providing common functionality for all data generators in the plugin.
 * Implements the Template Method pattern for consistent generation workflows across
 * all generator types. Handles FakerPHP integration, logging, validation, and
 * WordPress hooks for extensibility.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Abstracts
 */

namespace EasyCommerceFakerPress\Abstracts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
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
 * Provides the foundation for all data generators in EasyCommerce FakerPress.
 * Implements common functionality including FakerPHP integration, WordPress
 * database access, logging, validation, and the core generation workflow.
 * Uses the Template Method pattern to ensure consistent behavior across all generators.
 *
 * Key Features:
 * - FakerPHP integration with locale support
 * - WordPress database abstraction
 * - Comprehensive logging system
 * - Parameter validation and sanitization
 * - WordPress action/filter hooks for extensibility
 * - Batch processing with memory management
 *
 * @since 1.0.0
 */
abstract class Generator {
	/**
	 * FakerPHP generator instance
	 *
	 * Holds the FakerPHP generator instance configured with the appropriate locale
	 * and providers for generating realistic fake data. Initialized in set_faker().
	 *
	 * @since 1.0.0
	 * @var Faker_Generator
	 */
	protected Faker_Generator $faker;

	/**
	 * WordPress database instance
	 *
	 * Reference to the global WordPress database object for performing
	 * secure database operations using wpdb methods and prepared statements.
	 *
	 * @since 1.0.0
	 * @var \wpdb
	 */
	protected wpdb $wpdb;

	/**
	 * Maximum items to generate per batch
	 *
	 * Limits the number of items that can be generated in a single batch
	 * to prevent memory exhaustion and timeout issues. Can be overridden
	 * by child classes for specific requirements.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected int $max_batch_size = 100;

	/**
	 * Locale for FakerPHP generator
	 *
	 * Stores the locale code (e.g., 'en_US', 'fr_FR') used to configure
	 * the FakerPHP generator for locale-specific data generation.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $locale;

	/**
	 * Generation parameters from REST API
	 *
	 * Stores the parameters passed from the REST API request for customizing
	 * the data generation process. Includes options like count, locale, seed,
	 * and generator-specific parameters.
	 *
	 * @since 1.0.0
	 * @var array<string, mixed>
	 */
	protected array $generation_params = array();

	/**
	 * Constructor
	 *
	 * Initializes the generator with a reference to the WordPress database object.
	 * Sets up the foundation for database operations and ensures proper integration
	 * with WordPress database abstraction layer.
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
	 * Set FakerPHP instance
	 *
	 * Configures and initializes the FakerPHP generator with the specified locale
	 * and additional providers. Adds DateTime and PicsumPhotos providers for
	 * enhanced data generation capabilities including timestamps and placeholder images.
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
	 * Get FakerPHP instance
	 *
	 * Returns the configured FakerPHP generator instance for generating
	 * realistic fake data. Ensures the generator is properly initialized
	 * with locale and providers before use.
	 *
	 * @since 1.0.0
	 *
	 * @return Faker_Generator The configured FakerPHP generator instance.
	 */
	public function get_faker(): Faker_Generator {
		return $this->faker;
	}

	/**
	 * Set locale for FakerPHP generator
	 *
	 * Configures the locale for the FakerPHP generator to produce locale-specific
	 * fake data. The locale affects names, addresses, phone numbers, and other
	 * culturally-specific data patterns.
	 *
	 * @since 1.0.0
	 *
	 * @param string $locale Locale code (e.g., 'en_US', 'fr_FR', 'de_DE').
	 *
	 * @return void
	 */
	public function set_locale( string $locale = 'en_US' ): void {
		$this->locale = $locale;
	}

	/**
	 * Get locale for FakerPHP generator
	 *
	 * Returns the currently configured locale code for the FakerPHP generator.
	 * Used internally for generator configuration and can be overridden by
	 * child classes for specific locale handling requirements.
	 *
	 * @since 1.0.0
	 *
	 * @return string The configured locale code (e.g., 'en_US').
	 */
	public function get_faker_locale(): string {
		return $this->locale;
	}

	/**
	 * Generate fake data
	 *
	 * Orchestrates the complete data generation process using the Template Method pattern.
	 * Handles parameter validation, dependency checking, batch processing, and error handling.
	 * Fires WordPress actions at key points to allow for extensibility and monitoring.
	 *
	 * Process Flow:
	 * 1. Validate generation count and parameters
	 * 2. Apply WordPress filters to generation parameters
	 * 3. Set random seed if provided for reproducible results
	 * 4. Generate items in a loop with error handling
	 * 5. Apply filters to each generated item
	 * 6. Fire completion actions
	 *
	 * @since 1.0.0
	 *
	 * @param int $count Number of items to generate (1-100 per batch).
	 *
	 * @return array<int, array<string, mixed>>|WP_Error Array of generated items or error object.
	 */
	public function generate( int $count ) {
		$resource_type = $this->get_resource_type();

		// Validate count.
		$validation_result = $this->validate_count( $count );
		if ( is_wp_error( $validation_result ) ) {
			return $validation_result;
		}

		/**
		 * Filters the generation parameters for a specific resource type.
		 *
		 * Allows developers to modify generation parameters before they are used
		 * by the generator. Useful for customizing default values, adding validation,
		 * or implementing custom generation logic.
		 *
		 * @since 1.0.0
		 *
		 * @param array<string, mixed> $generation_params Current generation parameters.
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
				 * Allows developers to perform actions before individual item generation,
				 * such as logging, validation, or setup operations.
				 *
				 * @since 1.0.0
				 * @hook  easycommerce_fakerpress_before_generate_single_item_{$resource_type}
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
					 * Allows developers to modify or validate generated items before they are
					 * added to the results array. Useful for custom validation, data transformation,
					 * or additional processing.
					 *
					 * @since 1.0.0
					 * @hook  easycommerce_fakerpress_generated_item_{$resource_type}
					 *
					 * @param array<string, mixed>|WP_Error $item_result The generated item result.
					 * @param int                           $i           The current item index in the generation loop.
					 */
					$item_result = apply_filters( "easycommerce_fakerpress_generated_item_{$resource_type}", $item_result, $i );

					if ( $item_result && ! is_wp_error( $item_result ) ) {
						$results[] = $item_result;
					}

					/**
					 * Fires after generating a single item of the specified resource type.
					 *
					 * Allows developers to perform actions after individual item generation,
					 * such as cleanup, logging, or triggering related processes.
					 *
					 * @since 1.0.0
					 * @hook  easycommerce_fakerpress_after_generate_single_item_{$resource_type}
					 *
					 * @param array<string, mixed>|WP_Error $item_result The generated item result.
					 * @param int                           $i           The current item index in the generation loop.
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
			 * Allows developers to perform batch-level operations after all items have been
			 * generated, such as cache clearing, search index updates, or notification sending.
			 *
			 * @since 1.0.0
			 * @hook  easycommerce_fakerpress_after_batch_generate_{$resource_type}
			 *
			 * @param array<int, mixed> $results All successfully generated items in the batch.
			 * @param int               $count   Total number of items attempted to generate.
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
	 * Abstract method that must be implemented by all concrete generator classes.
	 * Contains the specific logic for generating one item of the resource type
	 * (product, customer, order, etc.). Should handle all business logic,
	 * validation, and database operations for creating a single item.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|WP_Error Generated item data array or error object.
	 */
	abstract protected function generate_single_item();

	/**
	 * Get the resource type name
	 *
	 * Abstract method that must be implemented by all concrete generator classes.
	 * Returns a string identifier for the type of resource being generated
	 * (e.g., 'product', 'customer', 'order'). Used for logging, filtering,
	 * and WordPress action/filter hook naming.
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type identifier (lowercase, no spaces).
	 */
	abstract protected function get_resource_type(): string;

	/**
	 * Set generation parameters
	 *
	 * Stores the parameters received from the REST API request for use during
	 * the generation process. These parameters control various aspects of
	 * data generation including count, locale, seed, and generator-specific options.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string, mixed> $params Generation parameters from REST API request.
	 *
	 * @return void
	 */
	public function set_generation_params( array $params ): void {
		$this->generation_params = $params;
	}

	/**
	 * Validate generation count
	 *
	 * Ensures the requested generation count is within acceptable limits
	 * to prevent memory exhaustion, timeout issues, and performance problems.
	 * Validates that count is positive and doesn't exceed the maximum batch size.
	 *
	 * @since 1.0.0
	 *
	 * @param int $count Number of items to generate.
	 *
	 * @return true|WP_Error True if count is valid, WP_Error with details if invalid.
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
	 * Build a preview dataset without persisting anything to the database.
	 *
	 * Returns a structure containing a columns definition array and an array of
	 * preview rows. Each row is a map of column-key → cell, where a cell is an
	 * array with at least the keys 'v' (display value) and 'kind' (rendering hint).
	 *
	 * The count is clamped to 1–25 so that preview requests are always lightweight
	 * regardless of what the caller passes in.
	 *
	 * @since 1.0.0
	 *
	 * @param int $count Number of preview rows to build (clamped to 1–25).
	 *
	 * @return array{ columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, array{v: mixed, kind: string}>> }
	 */
	public function preview( int $count ): array {
		$count   = max( 1, min( 25, $count ) );
		$columns = $this->get_preview_columns();
		$rows    = array();

		for ( $i = 0; $i < $count; $i++ ) {
			$rows[] = $this->build_preview_row();
		}

		return array(
			'columns' => $columns,
			'rows'    => $rows,
		);
	}

	/**
	 * Return the column definitions for the preview table.
	 *
	 * Each column is an associative array with at least:
	 *   - 'key'   (string) – matches a key in each preview row.
	 *   - 'label' (string) – human-readable column header.
	 *
	 * Override this in concrete generators to return resource-specific columns.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array{key: string, label: string}>
	 */
	protected function get_preview_columns(): array {
		return array(
			array(
				'key'   => 'id',
				'label' => __( 'ID', 'easycommerce-fakerpress' ),
			),
			array(
				'key'   => 'value',
				'label' => __( 'Value', 'easycommerce-fakerpress' ),
			),
		);
	}

	/**
	 * Build a single representative preview row without persisting anything.
	 *
	 * Uses only FakerPHP and loaded sample data — never writes to the DB.
	 * Override this in concrete generators to return resource-specific cells.
	 *
	 * Each cell must be an array with:
	 *   - 'v'    (mixed)  – the display value.
	 *   - 'kind' (string) – rendering hint: mono | money | num | status | badge | stars | text.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array{v: mixed, kind: string}>
	 */
	protected function build_preview_row(): array {
		return array(
			'id'    => array(
				'v'    => $this->faker->numberBetween( 10000, 99999 ),
				'kind' => 'mono',
			),
			'value' => array(
				'v'    => $this->faker->words( 2, true ),
				'kind' => 'text',
			),
		);
	}

	/**
	 * Load sample data for the current locale
	 *
	 * Loads locale-specific sample data from JSON files for use in data generation.
	 * Each generator can override this method to specify which data files to load
	 * based on the resource type and current locale setting.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Array of sample data arrays loaded from JSON files.
	 */
	protected function load_sample_data(): array {
		// Default implementation returns empty array.
		// Child classes should override this method to load their specific data.
		return array();
	}

	/**
	 * Load JSON file and return decoded data
	 *
	 * Helper method to load and decode JSON files containing sample data.
	 * Uses WordPress Filesystem API for secure file operations with proper
	 * error handling and JSON decoding.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file_path Path to JSON file.
	 *
	 * @return array|null Decoded JSON data or null on failure.
	 */
	protected function load_json_file( string $file_path ): ?array {
		// Initialize WordPress Filesystem if not already available.
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// Check if file exists using WP Filesystem.
		if ( ! $wp_filesystem->exists( $file_path ) ) {
			$this->log( "Sample data file not found: {$file_path}", 'warning' );
			return null;
		}

		// Read file content using WP Filesystem.
		$json_content = $wp_filesystem->get_contents( $file_path );
		if ( false === $json_content ) {
			$this->log( "Failed to read sample data file: {$file_path}", 'error' );
			return null;
		}

		// Decode JSON content.
		$data = json_decode( $json_content, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$this->log( "Failed to decode JSON from file: {$file_path} - " . json_last_error_msg(), 'error' );
			return null;
		}

		return $data;
	}

	/**
	 * Get sample data file path for a specific resource and locale
	 *
	 * Constructs the file path for sample data JSON files based on resource type
	 * and locale. Provides a standardized way to locate sample data files.
	 *
	 * @since 1.0.0
	 *
	 * @param string $resource_type The resource type (e.g., 'products', 'customers').
	 * @param string $filename      The filename without extension.
	 *
	 * @return string Full path to the sample data file.
	 */
	protected function get_sample_data_path( string $resource_type, string $filename ): string {
		$locale      = $this->get_faker_locale();
		$sample_path = easycommerce_fakerpress()->get_sample_data_directory();
		return $sample_path . "/{$resource_type}/{$locale}/{$filename}.json";
	}

	/**
	 * Log generation activity
	 *
	 * Records generation activities for debugging and monitoring purposes.
	 * Only logs when WP_DEBUG_LOG is enabled to avoid performance impact
	 * in production environments. Includes structured logging with resource
	 * type, log level, and additional context information.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $message Log message describing the activity.
	 * @param string               $level   Log level ('info', 'warning', 'error', 'debug').
	 * @param array<string, mixed> $context Additional context data for debugging.
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
