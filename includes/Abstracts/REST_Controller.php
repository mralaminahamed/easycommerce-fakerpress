<?php
/**
 * Abstract REST Controller Class.
 *
 * @package EasyCommerceFakerPress\Abstracts
 * @since   1.0.0
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
abstract class REST_Controller extends WP_REST_Controller {

	/**
	 * REST API namespace
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $namespace = 'easycommerce-fakerpress/v1';

	/**
	 * Register REST API routes
	 *
	 * Registers the generation endpoint for the specific resource type.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->get_rest_base() . '/generate',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'generate_items' ),
					'permission_callback' => array( $this, 'generate_items_permissions_check' ),
					'args'                => $this->get_generation_params(),
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
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function generate_items( WP_REST_Request $request ) {
		$count = $request->get_param( 'count' );

		if ( ! $count || $count <= 0 ) {
			return new WP_Error(
				'invalid_count',
				__( 'Count parameter is required and must be greater than 0.', 'easycommerce-fakerpress' ),
				array( 'status' => 400 )
			);
		}

		// Pass all request parameters to the generator.
		$params = $request->get_params();

		$generator = $this->get_generator_instance();

		// Set generator parameters if the generator supports it.
		if ( method_exists( $generator, 'set_generation_params' ) ) {
			$generator->set_generation_params( $params );
		}

		$result = $generator->generate( (int) $count );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Check permissions for generating items
	 *
	 * Verifies that the current user has permission to generate data.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return bool|WP_Error True if permission granted, error otherwise.
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
	 * @return array Parameters schema.
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
				'enum'              => array( 'en_US', 'en_GB', 'fr_FR', 'de_DE', 'es_ES', 'it_IT', 'ja_JP', 'zh_CN' ),
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
						'description' => __( 'Start date (YYYY-MM-DD format).', 'easycommerce-fakerpress' ),
						'type'        => 'string',
						'format'      => 'date',
					),
					'end'   => array(
						'description' => __( 'End date (YYYY-MM-DD format).', 'easycommerce-fakerpress' ),
						'type'        => 'string',
						'format'      => 'date',
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
	 * Validate count parameter
	 *
	 * Validates the count parameter for generation requests.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed           $value   Parameter value.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param   Parameter name.
	 *
	 * @return bool|WP_Error True if valid, WP_Error otherwise.
	 */
	public function validate_count( $value, WP_REST_Request $request, string $param ) {
		if ( ! is_numeric( $value ) || $value <= 0 || $value > 100 ) {
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
	 * @return array Schema definition.
	 */
	public function get_public_item_schema(): array {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => sprintf( '%s Generation Response', ucfirst( $this->get_resource_type() ) ),
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
	 * Get REST base for the endpoint
	 *
	 * Must be implemented by child classes to define the endpoint base.
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	abstract protected function get_rest_base(): string;

	/**
	 * Get generator instance
	 *
	 * Must be implemented by child classes to return the appropriate generator.
	 *
	 * @since 1.0.0
	 *
	 * @return Generator Generator instance.
	 */
	abstract protected function get_generator_instance(): Generator;

	/**
	 * Get resource type name
	 *
	 * Must be implemented by child classes to define the resource type.
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	abstract protected function get_resource_type(): string;

	/**
	 * Get resource-specific schema properties
	 *
	 * Can be overridden by child classes to add specific schema properties.
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific properties.
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
	 * @return array Resource-specific parameters.
	 */
	protected function get_resource_specific_params(): array {
		return array();
	}
}
