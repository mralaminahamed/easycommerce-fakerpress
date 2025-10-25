<?php
/**
 * Abstract REST Controller Class.
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
 * Base class for all REST API controllers with common functionality
 * for data generation endpoints.
 *
 * @since 1.0.0
 */
abstract class Controller extends WP_REST_Controller {

	/**
	 * REST API namespace
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $namespace = 'easycommerce-fakerpress/v1';

	/**
	 * Get REST base for the endpoint
	 *
	 * Must be implemented by child classes to define the endpoint base.
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base path (e.g., 'products').
	 */
	abstract protected function get_rest_base(): string;

	/**
	 * Get generator instance
	 *
	 * Must be implemented by child classes to return the appropriate generator.
	 *
	 * @since 1.0.0
	 *
	 * @return Generator Generator instance for the resource.
	 */
	abstract protected function get_generator_instance(): Generator;

	/**
	 * Get resource type name
	 *
	 * Must be implemented by child classes to define the resource type.
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type (e.g., 'product').
	 */
	abstract protected function get_resource_type(): string;

	/**
	 * Get human-readable label for the resource type
	 *
	 * Must be implemented by child classes to provide a user-friendly
	 * label for the resource type being handled.
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type label (e.g., 'Product', 'Order').
	 */
	abstract protected function get_resource_type_label(): string;

	/**
	 * Register REST API routes
	 *
	 * Registers the generation endpoint for the specific resource type.
	 * Integrated via the parent plugin's 'rest_api_init' action.
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
		 * Allows modifying the parameters used for data generation on a per-endpoint basis.
		 * The dynamic portion of the filter name, `$rest_base`, refers to the endpoint's
		 * REST base path (e.g., 'products', 'orders', etc.).
		 *
		 * @since 1.0.0
		 *
		 * @param array $params The default generation parameters from get_generation_params().
		 *
		 * @return array Modified parameters array.
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
	}

	/**
	 * Generate items endpoint callback
	 *
	 * Handles the generation request and returns the generated data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, WP_Error on failure.
	 */
	public function generate_items( WP_REST_Request $request ) {
		$rest_base = $this->get_rest_base();

		/**
		 * The base path for the REST API routes.
		 *
		 * This variable defines the foundational path segment used in REST API endpoint URLs.
		 * It acts as a prefix for all API requests within the defined namespace, providing
		 * a consistent structure for API routing.
		 *
		 * Example: If the $rest_base is set to 'example', the resulting endpoint paths would
		 * follow the format '/wp-json/{namespace}/example/...'.
		 *
		 * @var WP_REST_Request $requestFull data about the request.
		 */
		do_action( "easycommerce_fakerpress_rest_generate_before_{$rest_base}", $request );

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

		/**
		 * Defines the base path for a REST API endpoint.
		 *
		 * This variable is utilized in routing within the REST API to construct
		 * the endpoint URL. It serves as a key component for specifying the
		 * namespace or relative base for the API routes. Changes to this value
		 * can affect endpoint accessibility and should be carefully managed.
		 *
		 * Typically, this value is used in conjunction with other parameters or
		 * methods to create full REST routes. Ensure it adheres to naming
		 * conventions and does not conflict with existing endpoints.
		 *
		 * @var array           $result The result of the fake data
		 * @var WP_REST_Request $request Full data about the request.
		 */
		do_action( "easycommerce_fakerpress_rest_generate_after_{$rest_base}", $result, $request );

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Check permissions for generating items
	 *
	 * Verifies that the current user has permission to generate data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return bool|WP_Error True if permission granted, WP_Error otherwise.
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
	 * Returns the parameters schema for the generation endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of parameter definitions.
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
	 * Ensures date is in YYYY-MM-DD format.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $value   Date string.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param   Parameter name.
	 *
	 * @return bool|WP_Error True if valid, WP_Error otherwise.
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
	 * Validates the count parameter for generation requests.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string|mixed $value    Parameter value to validate.
	 * @param WP_REST_Request  $request  Request object.
	 * @param string           $param    Parameter name.
	 *
	 * @return bool|WP_Error True if valid, WP_Error otherwise.
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
	 * Returns the schema for API documentation and validation.
	 *
	 * @since 1.0.0
	 *
	 * @return array Schema definition for the response.
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
	 * Can be overridden by child classes to add specific schema properties.
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific schema properties.
	 */
	protected function get_resource_specific_properties(): array {
		return array();
	}

	/**
	 * Get resource-specific generation parameters
	 *
	 * Can be overridden by child classes to add specific generation parameters.
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific parameter definitions.
	 */
	protected function get_resource_specific_params(): array {
		return array();
	}

	/**
	 * Sanitize array parameter
	 *
	 * Sanitizes array parameters for API requests.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Parameter value to sanitize.
	 *
	 * @return array Sanitized array value.
	 */
	public function sanitize_array( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		return array_map( 'sanitize_text_field', $value );
	}
}
