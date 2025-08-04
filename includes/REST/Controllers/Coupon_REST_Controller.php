<?php
/**
 * Coupon Generator REST Controller
 *
 * @package EasyCommerceFakerPress\REST\Controllers
 * @since   1.0.0
 */

namespace EasyCommerceFakerPress\REST\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Coupon_Generator;

/**
 * Coupon Generator REST Controller
 *
 * Handles REST API endpoints for coupon generation
 *
 * @since 1.0.0
 */
class Coupon_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base() {
		return 'coupons';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Coupon_Generator Generator instance.
	 */
	protected function get_generator_instance() {
		return new Coupon_Generator();
	}

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type() {
		return 'coupon';
	}
}