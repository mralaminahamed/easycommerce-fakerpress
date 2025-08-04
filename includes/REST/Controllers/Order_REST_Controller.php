<?php
/**
 * Order Generator REST Controller
 *
 * @package EasyCommerceFakerPress\REST\Controllers
 * @since   1.0.0
 */

namespace EasyCommerceFakerPress\REST\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Order_Generator;

/**
 * Order Generator REST Controller
 *
 * Handles REST API endpoints for order generation
 *
 * @since 1.0.0
 */
class Order_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base() {
		return 'orders';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Order_Generator Generator instance.
	 */
	protected function get_generator_instance() {
		return new Order_Generator();
	}

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type() {
		return 'order';
	}
}