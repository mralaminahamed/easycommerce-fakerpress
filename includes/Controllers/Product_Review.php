<?php
/**
 * Product Review Controller for EasyCommerce FakerPress
 *
 * REST API controller for product review generation endpoints.
 * Handles HTTP requests and responses for product review data generation.
 *
 * @since   2.0.3
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Product_Review as ProductReviewGenerator;

/**
 * Product Review Controller Class
 *
 * Provides REST API endpoints for generating product reviews.
 * Handles validation, generation, and response formatting for product review data.
 *
 * @since 2.0.3
 */
class Product_Review extends Controller {

	/**
	 * Get the resource name for this controller
	 *
	 * @since 2.0.3
	 *
	 * @return string Resource name.
	 */
	protected function get_resource_name(): string {
		return 'product-reviews';
	}

	/**
	 * Get the REST base for this controller
	 *
	 * @since 2.0.3
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'product-reviews';
	}

	/**
	 * Get generator instance
	 *
	 * @since 2.0.3
	 *
	 * @return \EasyCommerceFakerPress\Abstracts\Generator Generator instance.
	 */
	protected function get_generator_instance(): \EasyCommerceFakerPress\Abstracts\Generator {
		return new ProductReviewGenerator();
	}

	/**
	 * Get resource type
	 *
	 * @since 2.0.3
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'product-review';
	}

	/**
	 * Get resource type label
	 *
	 * @since 2.0.3
	 *
	 * @return string Resource type label.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Product Review', 'easycommerce-fakerpress' );
	}

	/**
	 * Get parameter configuration for product review generation
	 *
	 * @since 2.0.3
	 *
	 * @return array Parameter configuration.
	 */
	protected function get_parameter_config(): array {
		return array(
			'count' => array(
				'description' => __( 'Number of product reviews to generate', 'easycommerce-fakerpress' ),
				'type'        => 'integer',
				'minimum'     => 1,
				'maximum'     => 1000,
				'default'     => 10,
				'required'    => false,
			),
		);
	}

	/**
	 * Validate generation parameters
	 *
	 * Performs additional validation specific to product reviews.
	 *
	 * @since 2.0.3
	 *
	 * @param array $params Parameters to validate.
	 * @return true|\WP_Error True if valid, WP_Error if invalid.
	 */
	protected function validate_parameters( array $params ) {
		// Check if products exist.
		$product_query = new \WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
			)
		);

		if ( ! $product_query->have_posts() ) {
			return new \WP_Error(
				'no_products',
				__( 'No published products found. Please generate products before creating reviews.', 'easycommerce-fakerpress' )
			);
		}

		// Check if customers exist.
		$customer_query = get_users(
			array(
				'role'   => 'customer',
				'number' => 1,
			)
		);

		if ( empty( $customer_query ) ) {
			return new \WP_Error(
				'no_customers',
				__( 'No customers found. Please generate customers before creating reviews.', 'easycommerce-fakerpress' )
			);
		}

		return parent::validate_parameters( $params );
	}
}
