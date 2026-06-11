<?php
/**
 * Abstract REST Controller Class for EasyCommerce FakerPress
 *
 * Base class for all REST API controllers providing common functionality for
 * data generation endpoints. Extends WordPress REST Controller with additional
 * features for parameter validation, schema generation, and generator integration.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Abstracts
 */

namespace EasyCommerceFakerPress\Abstracts;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Abstract REST Controller Class
 *
 * Provides the foundation for all REST API controllers in EasyCommerce FakerPress.
 * Extends WordPress REST Controller with specialized functionality for data
 * generation endpoints, including parameter validation, schema generation,
 * and seamless integration with generator classes.
 *
 * Key Features:
 * - WordPress REST API integration
 * - Automatic parameter validation and sanitization
 * - JSON Schema generation for API documentation
 * - Generator class integration
 * - WordPress action/filter hooks
 * - Comprehensive error handling
 *
 * @since 1.0.0
 */
abstract class Controller extends WP_REST_Controller {

	/**
	 * REST API namespace
	 *
	 * Defines the namespace for all REST API endpoints in the plugin.
	 * Follows WordPress REST API conventions for versioning and organization.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $namespace = 'easycommerce-fakerpress/v1';

	/**
	 * Get REST base for the endpoint
	 *
	 * Abstract method that must be implemented by all concrete controller classes.
	 * Defines the base path segment for the REST API endpoint (e.g., 'products',
	 * 'customers', 'orders'). This forms part of the complete endpoint URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base path segment (lowercase, plural form).
	 */
	abstract protected function get_rest_base(): string;

	/**
	 * Get generator instance
	 *
	 * Abstract method that must be implemented by all concrete controller classes.
	 * Returns a properly configured generator instance for the specific resource type.
	 * The generator handles the actual data creation logic for the endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return Generator Configured generator instance for the resource type.
	 */
	abstract protected function get_generator_instance(): Generator;

	/**
	 * Get resource type name
	 *
	 * Abstract method that must be implemented by all concrete controller classes.
	 * Returns the singular form of the resource type (e.g., 'product', 'customer').
	 * Used for API responses, logging, and WordPress action/filter naming.
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type identifier (singular, lowercase).
	 */
	abstract protected function get_resource_type(): string;

	/**
	 * Get human-readable label for the resource type
	 *
	 * Abstract method that must be implemented by all concrete controller classes.
	 * Returns a human-readable, translated label for the resource type (e.g., 'Product', 'Order').
	 * Used in API documentation, error messages, and user-facing text.
	 *
	 * @since 1.0.0
	 *
	 * @return string Human-readable resource type label (title case, translated).
	 */
	abstract protected function get_resource_type_label(): string;

	/**
	 * Register REST API routes
	 *
	 * Registers the data generation endpoint for the specific resource type.
	 * Creates a POST endpoint at /{namespace}/{rest_base}/generate that accepts
	 * generation parameters and returns created data. Includes parameter validation,
	 * permission checks, and JSON schema for API documentation.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$rest_base = $this->get_rest_base();

		/**
		 * Filters the REST API parameters for a specific endpoint.
		 *
		 * Allows developers to modify the parameter schema for specific endpoints,
		 * such as adding custom parameters, changing validation rules, or adding
		 * new options for particular resource types.
		 *
		 * @since 1.0.0
		 * @hook  easycommerce_fakerpress_rest_params_{$rest_base}
		 *
		 * @param array<string, mixed> $params Default generation parameters from get_generation_params().
		 */
		$params = apply_filters( "easycommerce_fakerpress_rest_params_{$rest_base}", $this->get_generation_params() );

		register_rest_route(
			$this->namespace,
			'/' . $rest_base . '/generate',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'generate_items' ),
					'permission_callback' => array( $this, 'generate_items_permissions_check' ),
					'args'                => $params,
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $rest_base . '/preview',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'preview_items' ),
					'permission_callback' => array( $this, 'generate_items_permissions_check' ),
					'args'                => $params,
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Preview items endpoint callback
	 *
	 * Returns a read-only preview of what would be generated — without persisting
	 * anything to the database. The response contains 'columns' (table header
	 * definitions) and 'rows' (an array of representative data cells).
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the REST API request.
	 *
	 * @return WP_REST_Response|WP_Error REST response with preview data or error.
	 */
	public function preview_items( WP_REST_Request $request ) {
		$generator         = $this->get_generator_instance();
		$params            = $request->get_params();
		$supported_locales = array_keys( easycommerce_fakerpress()->get_locale_labels() );

		// Mirror generate_items locale handling exactly.
		$locale = $params['locale'] ?? 'en_US';
		if ( ! in_array( $locale, $supported_locales, true ) ) {
			$generator->log( "Unsupported locale '{$locale}' used; falling back to 'en_US'.", 'warning' );
			$locale = 'en_US';
		}

		$generator->set_locale( $locale );
		$generator->set_faker();
		$generator->set_generation_params( $params );

		$count = isset( $params['count'] ) ? (int) $params['count'] : 10;

		return rest_ensure_response( $generator->preview( $count ) );
	}

	/**
	 * Generate items endpoint callback
	 *
	 * Handles POST requests to the generation endpoint, validates parameters,
	 * configures the generator, and returns the generated data. Includes
	 * comprehensive error handling, locale configuration, and WordPress action hooks
	 * for extensibility. Fires actions before and after generation for monitoring.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the REST API request.
	 *
	 * @return WP_REST_Response|WP_Error REST response with generated data or error details.
	 */
	public function generate_items( WP_REST_Request $request ) {
		$rest_base = $this->get_rest_base();

		$count = $request->get_param( 'count' );

		if ( ! $count || $count <= 0 ) {
			return new WP_Error(
				'invalid_count',
				__( 'Count parameter is required and must be greater than 0.', 'easycommerce-fakerpress' ),
				array( 'status' => 400 )
			);
		}

		// Pass all request parameters to the generator.
		$params            = $request->get_params();
		$generator         = $this->get_generator_instance();
		$supported_locales = array_keys( easycommerce_fakerpress()->get_locale_labels() );

		// Set faker and locale.
		$locale = $params['locale'] ?? 'en_US';
		if ( ! in_array( $locale, $supported_locales, true ) ) {
			$generator->log( "Unsupported locale '{$locale}' used; falling back to 'en_US'.", 'warning' );
			$locale = 'en_US';
		}
		$generator->set_locale( $locale );
		$generator->set_faker();
		$generator->set_generation_params( $params );

		$result = $generator->generate( (int) $count );

		if ( is_wp_error( $result ) ) {
			$generator->log( 'Generation failed: ' . $result->get_error_message(), 'error', $params );
			return $result;
		}

		$total_output = count( $result );

		$message = sprintf(
			// translators: Total output.
			_n( '%1$s item successfully created.', '%1$s items successfully created.', $total_output, 'easycommerce-fakerpress' ),
			$total_output
		);

		/**
		 * Filters the success message returned by the REST API generation endpoint.
		 *
		 * Allows developers to customize the success message or add additional information
		 * to the API response based on the generated results.
		 *
		 * @since 1.0.0
		 * @hook  easycommerce_fakerpress_rest_message
		 *
		 * @param string $message         The default success message.
		 * @param array  $result          The generation results array.
		 * @param string $resource_type   The type of resource that was generated.
		 */
		$message = apply_filters( 'easycommerce_fakerpress_rest_message', $message, $result, $this->get_resource_type() );

		$response = array(
			'message'                  => $message,
			$this->get_resource_type() => $result,
		);

		/**
		 * Filters the REST API response data.
		 *
		 * Allows developers to modify the response data before it's returned to the client.
		 *
		 * @since 1.0.0
		 * @hook easycommerce_fakerpress_rest_response
		 *
		 * @param array           $response     The REST API response data array.
		 * @param array           $result       The generation results array.
		 * @param string          $resource_type The type of resource that was generated.
		 * @param WP_REST_Request $request      Full data about the original request.
		 */
		$response = apply_filters( 'easycommerce_fakerpress_rest_response', $response, $result, $this->get_resource_type(), $request );

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Check permissions for generating items
	 *
	 * Verifies that the current user has the necessary permissions to generate data.
	 * Requires the 'manage_options' capability, which is typically held by administrators.
	 * Returns appropriate error responses for unauthorized access attempts.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the REST API request.
	 *
	 * @return bool|WP_Error True if user has permission, WP_Error with details if denied.
	 */
	public function generate_items_permissions_check( WP_REST_Request $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to generate data.', 'easycommerce-fakerpress' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Get generation endpoint parameters
	 *
	 * Returns the complete parameter schema for the generation endpoint,
	 * including base parameters (count, locale, seed) and resource-specific
	 * parameters defined by child classes. Used for parameter validation,
	 * API documentation, and request processing.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array<string, mixed>> Parameter schema definitions.
	 */
	public function get_generation_params(): array {
		$base_params = array(
			'count'         => array(
				'description'       => __( 'Number of items to generate.', 'easycommerce-fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'maximum'           => 100,
				'required'          => true,
				'sanitize_callback' => 'absint',
				'validate_callback' => array( $this, 'validate_count' ),
			),
			'locale'        => array(
				'description'       => __( 'Locale for generated data (e.g., en_US, fr_FR, de_DE).', 'easycommerce-fakerpress' ),
				'type'              => 'string',
				'default'           => 'en_US',
				'enum'              => array_keys( easycommerce_fakerpress()->get_locale_labels() ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'seed'          => array(
				'description'       => __( 'Random seed for reproducible data generation.', 'easycommerce-fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			),
			'status'        => array(
				'description'       => __( 'Status filter for generated items.', 'easycommerce-fakerpress' ),
				'type'              => 'string',
				'enum'              => array( 'active', 'inactive', 'draft', 'pending', 'completed', 'cancelled' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'date_range'    => array(
				'description' => __( 'Date range for generated items.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'start' => array(
						'description'       => __( 'Start date (YYYY-MM-DD format).', 'easycommerce-fakerpress' ),
						'type'              => 'string',
						'format'            => 'date',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => array( $this, 'validate_date' ),
					),
					'end'   => array(
						'description'       => __( 'End date (YYYY-MM-DD format).', 'easycommerce-fakerpress' ),
						'type'              => 'string',
						'format'            => 'date',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => array( $this, 'validate_date' ),
					),
				),
			),
			'relationships' => array(
				'description' => __( 'Control relationship creation with existing data.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'create_missing' => array(
						'description' => __( 'Create missing related items if needed.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'link_existing'  => array(
						'description' => __( 'Link to existing items when possible.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
				),
			),
			'meta_options'  => array(
				'description' => __( 'Metadata generation options.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'include_meta'  => array(
						'description' => __( 'Include additional metadata.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'custom_fields' => array(
						'description' => __( 'Generate custom fields.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => false,
					),
				),
			),
		);

		// Merge with resource-specific parameters.
		$resource_params = $this->get_resource_specific_params();

		return array_merge( $base_params, $resource_params );
	}

	/**
	 * Validate date parameter
	 *
	 * Validates that date parameters are in the correct YYYY-MM-DD format.
	 * Used for date range parameters in generation requests to ensure
	 * proper date handling and prevent invalid date-related errors.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $value   Date string to validate.
	 * @param WP_REST_Request $request Request object containing all parameters.
	 * @param string          $param   Parameter name being validated.
	 *
	 * @return bool|WP_Error True if date format is valid, WP_Error if invalid.
	 */
	public function validate_date( string $value, WP_REST_Request $request, string $param ) {
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return new WP_Error(
				'invalid_date',
				__( 'Date must be in YYYY-MM-DD format.', 'easycommerce-fakerpress' )
			);
		}
		return true;
	}

	/**
	 * Validate count parameter
	 *
	 * Validates the count parameter for generation requests, ensuring it's
	 * a positive integer within the allowed range (1-100). Prevents invalid
	 * or potentially harmful generation requests.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string|mixed $value    Parameter value to validate (should be numeric).
	 * @param WP_REST_Request  $request  Request object containing all parameters.
	 * @param string           $param    Parameter name being validated.
	 *
	 * @return bool|WP_Error True if count is valid, WP_Error with details if invalid.
	 */
	public function validate_count( $value, WP_REST_Request $request, string $param ) {
		$int_value = (int) $value;
		if ( ! is_numeric( $value ) || $int_value <= 0 || $int_value > 100 ) {
			return new WP_Error(
				'invalid_count',
				__( 'Count must be a number between 1 and 100.', 'easycommerce-fakerpress' )
			);
		}

		return true;
	}

	/**
	 * Get public schema for the endpoint
	 *
	 * Returns the JSON Schema definition for the REST API endpoint response.
	 * Used for API documentation, client validation, and ensuring consistent
	 * response structures. Includes base properties and resource-specific extensions.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> JSON Schema definition for the API response.
	 */
	public function get_public_item_schema(): array {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// translators: Resource type.
			'title'      => sprintf( __( '%s Generation Response', 'easycommerce-fakerpress' ), $this->get_resource_type_label() ),
			'type'       => 'object',
			'properties' => array(
				'generated' => array(
					'description' => __( 'Number of items generated.', 'easycommerce-fakerpress' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		// Add resource-specific properties.
		$resource_properties = $this->get_resource_specific_properties();
		if ( ! empty( $resource_properties ) ) {
			$schema['properties'] = array_merge( $schema['properties'], $resource_properties );
		}

		return $schema;
	}

	/**
	 * Get resource-specific schema properties
	 *
	 * Returns additional JSON Schema properties specific to the resource type.
	 * Can be overridden by child controller classes to add custom response
	 * properties for their specific resource type (e.g., product attributes,
	 * customer fields). Returns empty array by default.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Resource-specific schema properties array.
	 */
	protected function get_resource_specific_properties(): array {
		return array();
	}

	/**
	 * Get resource-specific generation parameters
	 *
	 * Returns additional parameter definitions specific to the resource type.
	 * Can be overridden by child controller classes to add custom parameters
	 * for their specific generation requirements (e.g., product categories,
	 * customer demographics). Returns empty array by default.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Resource-specific parameter definitions.
	 */
	protected function get_resource_specific_params(): array {
		return array();
	}

	/**
	 * Sanitize array parameter
	 *
	 * Sanitizes array parameters for REST API requests using WordPress
	 * sanitization functions. Ensures all array values are properly cleaned
	 * to prevent XSS and other injection attacks. Returns empty array if
	 * input is not an array.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Parameter value that should be an array.
	 *
	 * @return array<string, mixed> Sanitized array with cleaned values.
	 */
	public function sanitize_array( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		return array_map( 'sanitize_text_field', $value );
	}
}
