<?php
/**
 * Abstract REST Controller Class
 *
 * @package EasyCommerceFakerPress\Abstracts
 * @since   1.0.0
 */

namespace EasyCommerceFakerPress\Abstracts;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Abstract REST Controller Class
 *
 * Base class for all REST controllers with common functionality
 *
 * @since 1.0.0
 */
abstract class REST_Controller extends WP_REST_Controller {
	/**
	 * Endpoint namespace
	 *
	 * @var string
	 */
	protected $namespace = 'easycommerce-fakerpress/v1';

	/**
	 * Route base
	 *
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * Generator instance
	 *
	 * @var Generator
	 */
	protected $generator;

	/**
	 * Maximum count per request
	 *
	 * @var int
	 */
	protected $max_count = 100;

	/**
	 * Default count
	 *
	 * @var int
	 */
	protected $default_count = 10;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->rest_base = $this->get_rest_base();
		$this->generator = $this->get_generator_instance();
	}

	/**
	 * Register routes
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/generate',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'generate_items' ),
					'permission_callback' => array( $this, 'check_permissions' ),
					'args'                => $this->get_generation_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	}

	/**
	 * Generate items endpoint
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function generate_items( WP_REST_Request $request ) {
		try {
			$count = $this->sanitize_count( $request->get_param( 'count' ) );
			
			$validation_result = $this->validate_generation_request( $request, $count );
			if ( is_wp_error( $validation_result ) ) {
				return $validation_result;
			}

			/**
			 * Action fired before generating items
			 *
			 * @since 1.0.0
			 *
			 * @param int             $count   Number of items to generate.
			 * @param string          $type    Resource type.
			 * @param WP_REST_Request $request Request object.
			 */
			do_action( 'ecfp_before_generate_items', $count, $this->get_resource_type(), $request );

			$result = $this->generator->generate( $count );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			/**
			 * Action fired after generating items
			 *
			 * @since 1.0.0
			 *
			 * @param array           $result  Generation results.
			 * @param int             $count   Number of items requested.
			 * @param string          $type    Resource type.
			 * @param WP_REST_Request $request Request object.
			 */
			do_action( 'ecfp_after_generate_items', $result, $count, $this->get_resource_type(), $request );

			return $this->success_response( $result );

		} catch ( \Exception $e ) {
			return $this->error_response(
				'generation_failed',
				sprintf(
					/* translators: 1: Resource type, 2: Error message */
					__( '%1$s generation failed: %2$s', 'easycommerce-fakerpress' ),
					ucfirst( $this->get_resource_type() ),
					$e->getMessage()
				),
				500
			);
		}
	}

	/**
	 * Check permissions for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if permission granted, WP_Error otherwise.
	 */
	public function check_permissions( WP_REST_Request $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->error_response(
				'rest_forbidden',
				sprintf(
					/* translators: %s: Resource type */
					__( 'You do not have permissions to generate %s.', 'easycommerce-fakerpress' ),
					$this->get_resource_type_plural()
				),
				403
			);
		}

		/**
		 * Filter permission check result
		 *
		 * @since 1.0.0
		 *
		 * @param bool            $allowed  Whether permission is granted.
		 * @param string          $type     Resource type.
		 * @param WP_REST_Request $request  Request object.
		 */
		return apply_filters( 'ecfp_rest_permission_check', true, $this->get_resource_type(), $request );
	}

	/**
	 * Get generation parameters schema
	 *
	 * @since 1.0.0
	 *
	 * @return array Generation parameters.
	 */
	public function get_generation_params() {
		$params = array(
			'count' => array(
				'description' => sprintf(
					/* translators: %s: Resource type plural */
					__( 'Number of %s to generate.', 'easycommerce-fakerpress' ),
					$this->get_resource_type_plural()
				),
				'type'        => 'integer',
				'default'     => $this->default_count,
				'minimum'     => 1,
				'maximum'     => $this->max_count,
				'required'    => false,
			),
		);

		/**
		 * Filter generation parameters
		 *
		 * @since 1.0.0
		 *
		 * @param array  $params Parameters array.
		 * @param string $type   Resource type.
		 */
		return apply_filters( 'ecfp_rest_generation_params', $params, $this->get_resource_type() );
	}

	/**
	 * Get item schema
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->get_resource_type() . '_generation',
			'type'       => 'object',
			'properties' => array_merge(
				$this->get_standard_response_properties(),
				$this->get_resource_specific_properties()
			)
		);

		/**
		 * Filter the item schema
		 *
		 * @since 1.0.0
		 *
		 * @param array  $schema Item schema.
		 * @param string $type   Resource type.
		 */
		return apply_filters( 'ecfp_rest_item_schema', $schema, $this->get_resource_type() );
	}

	/**
	 * Get standard response properties
	 *
	 * @since 1.0.0
	 *
	 * @return array Standard properties.
	 */
	protected function get_standard_response_properties() {
		return array(
			'success' => array(
				'description' => __( 'Whether the request was successful.', 'easycommerce-fakerpress' ),
				'type'        => 'boolean',
				'readonly'    => true,
			),
			'message' => array(
				'description' => __( 'Human-readable message describing the result.', 'easycommerce-fakerpress' ),
				'type'        => 'string',
				'readonly'    => true,
			),
			'data'    => array(
				'description' => __( 'Generation result data.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'generated' => array(
						'description' => sprintf(
							/* translators: %s: Resource type plural */
							__( 'Number of %s generated.', 'easycommerce-fakerpress' ),
							$this->get_resource_type_plural()
						),
						'type'        => 'integer',
					),
				),
			),
		);
	}

	/**
	 * Create success response
	 *
	 * @since 1.0.0
	 *
	 * @param array $result Generation result data.
	 *
	 * @return WP_REST_Response Success response.
	 */
	protected function success_response( array $result ) {
		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => $this->get_success_message( $result['generated'] ),
				'data'    => $result,
			),
			201
		);
	}

	/**
	 * Create error response
	 *
	 * @since 1.0.0
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @param int    $status  HTTP status code.
	 * @param mixed  $data    Additional error data.
	 *
	 * @return WP_Error Error response.
	 */
	protected function error_response( $code, $message, $status = 400, $data = null ) {
		return new WP_Error( $code, $message, array( 'status' => $status, 'data' => $data ) );
	}

	/**
	 * Get success message
	 *
	 * @since 1.0.0
	 *
	 * @param int $count Number of items generated.
	 *
	 * @return string Success message.
	 */
	protected function get_success_message( $count ) {
		return sprintf(
			/* translators: 1: Number generated, 2: Resource type singular, 3: Resource type plural */
			_n(
				'%1$d %2$s generated successfully.',
				'%1$d %3$s generated successfully.',
				$count,
				'easycommerce-fakerpress'
			),
			$count,
			$this->get_resource_type(),
			$this->get_resource_type_plural()
		);
	}

	/**
	 * Sanitize count parameter
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $count Count value to sanitize.
	 *
	 * @return int Sanitized count.
	 */
	protected function sanitize_count( $count ) {
		$count = absint( $count );
		return $count > 0 ? $count : $this->default_count;
	}

	/**
	 * Validate generation request
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @param int             $count   Requested count.
	 *
	 * @return true|WP_Error True if valid, WP_Error otherwise.
	 */
	protected function validate_generation_request( WP_REST_Request $request, $count ) {
		if ( $count <= 0 || $count > $this->max_count ) {
			return $this->error_response(
				'invalid_count',
				sprintf(
					/* translators: 1: Maximum count, 2: Resource type plural */
					__( 'Count must be between 1 and %1$d for %2$s.', 'easycommerce-fakerpress' ),
					$this->max_count,
					$this->get_resource_type_plural()
				),
				400
			);
		}

		return true;
	}

	/**
	 * Get REST base for the endpoint
	 *
	 * Must be implemented by child classes
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	abstract protected function get_rest_base();

	/**
	 * Get generator instance
	 *
	 * Must be implemented by child classes
	 *
	 * @since 1.0.0
	 *
	 * @return Generator Generator instance.
	 */
	abstract protected function get_generator_instance();

	/**
	 * Get resource type name
	 *
	 * Must be implemented by child classes
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	abstract protected function get_resource_type();

	/**
	 * Get resource type plural name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type plural.
	 */
	protected function get_resource_type_plural() {
		return $this->get_resource_type() . 's';
	}

	/**
	 * Get resource-specific schema properties
	 *
	 * Can be overridden by child classes
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific properties.
	 */
	protected function get_resource_specific_properties() {
		return array();
	}
}