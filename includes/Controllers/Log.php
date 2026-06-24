<?php
/**
 * Log Generator REST Controller
 *
 * @since   2.1.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Log as LogGenerator;

/**
 * Log Generator REST Controller
 *
 * Handles REST API endpoints for log entry generation.
 *
 * @since 2.1.0
 */
class Log extends Controller {

	/**
	 * Get resource type name.
	 *
	 * @since 2.1.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'log';
	}

	/**
	 * Get resource type label for logs.
	 *
	 * @since 2.1.0
	 *
	 * @return string The translated label for log resource type.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Log', 'easycommerce-fakerpress' );
	}

	/**
	 * Get REST base for the endpoint.
	 *
	 * @since 2.1.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'logs';
	}

	/**
	 * Get generator instance.
	 *
	 * @since 2.1.0
	 *
	 * @return LogGenerator Generator instance.
	 */
	protected function get_generator_instance(): LogGenerator {
		return new LogGenerator();
	}

	/**
	 * Get resource-specific parameters.
	 *
	 * @since 2.1.0
	 *
	 * @return array Resource-specific parameters.
	 */
	protected function get_resource_specific_params(): array {
		return array(
			'log_types' => array(
				'description'       => __( 'Log severity types to generate.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'info', 'warning', 'error', 'success' ),
				),
				'default'           => array( 'info', 'warning', 'error', 'success' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'objects'   => array(
				'description'       => __( 'Object types to generate log entries for.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'order', 'product', 'customer', 'coupon', 'refund', 'cart', 'transaction', 'system' ),
				),
				'default'           => array( 'order', 'product', 'customer', 'coupon', 'refund', 'cart', 'transaction', 'system' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
		);
	}

	/**
	 * Get resource-specific schema properties.
	 *
	 * @since 2.1.0
	 *
	 * @return array Resource-specific properties.
	 */
	protected function get_resource_specific_properties(): array {
		return array(
			'logs' => array(
				'description' => __( 'Generated log entries data.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'        => array(
							'type' => 'integer',
						),
						'object'    => array(
							'type' => 'string',
						),
						'action'    => array(
							'type' => 'string',
						),
						'object_id' => array(
							'type' => 'integer',
						),
						'user_id'   => array(
							'type' => 'integer',
						),
						'type'      => array(
							'type' => 'string',
						),
						'note'      => array(
							'type' => 'string',
						),
						'is_public' => array(
							'type' => 'integer',
						),
					),
				),
			),
		);
	}
}
