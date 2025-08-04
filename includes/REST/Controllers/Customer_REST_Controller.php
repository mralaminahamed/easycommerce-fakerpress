<?php
/**
 * Customer Generator REST Controller
 *
 * @package EasyCommerceFakerPress\REST\Controllers
 * @since   1.0.0
 */

namespace EasyCommerceFakerPress\REST\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Customer_Generator;

/**
 * Customer Generator REST Controller
 *
 * Handles REST API endpoints for customer generation
 *
 * @since 1.0.0
 */
class Customer_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base() {
		return 'customers';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Customer_Generator Generator instance.
	 */
	protected function get_generator_instance() {
		return new Customer_Generator();
	}

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type() {
		return 'customer';
	}

}