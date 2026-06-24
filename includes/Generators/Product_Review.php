<?php
/**
 * Product Review Generator for EasyCommerce FakerPress
 *
 * Generates realistic product reviews for EasyCommerce stores including
 * ratings, comments, and customer feedback with proper WordPress comment integration.
 *
 * @since   2.0.3
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
use EasyCommerceFakerPress\Abstracts\Generator;
use WP_Error;

/**
 * Product Review Generator Class
 *
 * Generates comprehensive and realistic product review data for EasyCommerce stores.
 * Creates reviews with ratings, customer feedback, and proper WordPress comment integration.
 *
 * Generated Data Includes:
 * - Product ratings (1-5 stars)
 * - Customer review comments
 * - Verified purchase status
 * - Review timestamps
 * - Customer information linkage
 *
 * @since 2.0.3
 */
class Product_Review extends Generator {

	/**
	 * Get the resource type name
	 *
	 * @since 2.0.3
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'product-review';
	}

	/**
	 * Generate a single product review item.
	 *
	 * Creates a realistic product review with rating, content, and customer information.
	 * Links the review to existing products and customers for data integrity.
	 *
	 * @since 2.0.3
	 *
	 * @return array|WP_Error Product review data array or error.
	 */
	protected function generate_single_item() {
		// Get existing products and customers for realistic relationships.
		$specific_id = isset( $this->generation_params['product_id'] ) ? (int) $this->generation_params['product_id'] : 0;
		$products    = $specific_id ? array( $specific_id ) : $this->get_existing_products();
		$customers   = $this->get_existing_customers();

		if ( empty( $products ) ) {
			return new WP_Error(
				'no_products',
				__( 'No products found. Please generate products first.', 'easycommerce-fakerpress' )
			);
		}

		if ( empty( $customers ) ) {
			return new WP_Error(
				'no_customers',
				__( 'No customers found. Please generate customers first.', 'easycommerce-fakerpress' )
			);
		}

		// Select random product and customer.
		$product  = $this->get_faker()->randomElement( $products );
		$customer = $this->get_faker()->randomElement( $customers );

		// Generate review data.
		$review_data = array(
			'product_id'     => (int) $product,
			'customer_id'    => $customer->ID,
			'customer_name'  => $customer->display_name ? $customer->display_name : $customer->user_login,
			'customer_email' => $customer->user_email,
			'rating'         => $this->generate_rating(),
			'content'        => $this->generate_review_content(),
			'status'         => $this->get_faker()->randomElement( array( '1', '0' ) ), // 1 = approved, 0 = pending
			'verified'       => $this->get_faker()->boolean( 80 ), // 80% verified purchases
			'created_at'     => $this->get_faker()->dateTimeBetween( '-1 year', 'now' )->format( 'Y-m-d H:i:s' ),
		);

		/**
		 * Filters the product review data before creating the review.
		 *
		 * Allows developers to modify review data including rating, content, and metadata
		 * before the review is created in the WordPress comments system.
		 *
		 * @since 2.0.3
		 * @hook easycommerce_fakerpress_product_review_data_before_create
		 *
		 * @param array $review_data {
		 *     Review data array.
		 *
		 *     @type int    $product_id     Product ID the review is for.
		 *     @type int    $customer_id    Customer ID who wrote the review.
		 *     @type string $customer_name  Customer display name.
		 *     @type string $customer_email Customer email address.
		 *     @type int    $rating         Review rating (1-5).
		 *     @type string $content        Review content text.
		 *     @type string $status         Review status (1=approved, 0=pending).
		 *     @type bool   $verified       Whether it's a verified purchase.
		 *     @type string $created_at     Review creation timestamp.
		 * }
		 */
		$review_data = apply_filters(
			'easycommerce_fakerpress_product_review_data_before_create',
			$review_data
		);

		// Create the review using WordPress comment system.
		$comment_data = array(
			'comment_post_ID'      => $review_data['product_id'],
			'comment_author'       => $review_data['customer_name'],
			'comment_author_email' => $review_data['customer_email'],
			'comment_author_url'   => '',
			'comment_content'      => $review_data['content'],
			'comment_type'         => 'review',
			'comment_parent'       => 0,
			'user_id'              => $review_data['customer_id'],
			'comment_author_IP'    => $this->get_faker()->ipv4,
			'comment_agent'        => $this->get_faker()->userAgent,
			'comment_date'         => $review_data['created_at'],
			'comment_date_gmt'     => get_gmt_from_date( $review_data['created_at'] ),
			'comment_approved'     => $review_data['status'],
		);

		$comment_id = wp_insert_comment( $comment_data );

		if ( ! $comment_id ) {
			return new WP_Error(
				'creation_failed',
				__( 'Failed to create product review.', 'easycommerce-fakerpress' )
			);
		}

		// Add rating meta.
		add_comment_meta( $comment_id, 'rating', $review_data['rating'] );

		// Add verified purchase meta if applicable.
		if ( $review_data['verified'] ) {
			add_comment_meta( $comment_id, 'verified', '1' );
		}

		/**
		 * Fires after a product review has been created.
		 *
		 * @since 2.0.3
		 * @hook easycommerce_fakerpress_product_review_created
		 *
		 * @param int   $comment_id  The created comment/review ID.
		 * @param array $review_data The review data used for creation.
		 */
		do_action( 'easycommerce_fakerpress_product_review_created', $comment_id, $review_data );

		return array(
			'id'          => $comment_id,
			'product_id'  => $review_data['product_id'],
			'customer_id' => $review_data['customer_id'],
			'rating'      => $review_data['rating'],
			'content'     => $review_data['content'],
			'status'      => $review_data['status'],
			'verified'    => $review_data['verified'],
			'created_at'  => $review_data['created_at'],
		);
	}

	/**
	 * Generate a realistic review rating.
	 *
	 * Creates a weighted rating distribution favoring higher ratings
	 * to simulate real customer review patterns.
	 *
	 * @since 2.0.3
	 *
	 * @return int Rating between 1-5.
	 */
	private function generate_rating(): int {
		$weights = array(
			5 => 40, // 40% 5-star
			4 => 30, // 30% 4-star
			3 => 15, // 15% 3-star
			2 => 10, // 10% 2-star
			1 => 5,  // 5% 1-star
		);

		$total_weight = array_sum( $weights );
		$random       = $this->get_faker()->numberBetween( 1, $total_weight );

		foreach ( $weights as $rating => $weight ) {
			$random -= $weight;
			if ( $random <= 0 ) {
				return $rating;
			}
		}

		return 5; // Fallback.
	}

	/**
	 * Generate realistic review content.
	 *
	 * Creates varied review text based on the rating and product type.
	 *
	 * @since 2.0.3
	 *
	 * @return string Review content.
	 */
	private function generate_review_content(): string {
		$positive_templates = array(
			'Excellent quality and fast shipping!',
			'Exactly as described. Very satisfied with my purchase.',
			'Great product! Would definitely recommend to friends.',
			'Fantastic value for money. Better than expected.',
			'Outstanding customer service and product quality.',
		);

		$neutral_templates = array(
			'Product is okay, does what it says.',
			'Decent quality for the price.',
			'Met my expectations, nothing special.',
			'Average product, but it works.',
			'Not bad, but could be improved.',
		);

		$negative_templates = array(
			'Not satisfied with the quality.',
			'Product arrived damaged.',
			'Poor customer service experience.',
			'Does not match the description.',
			'Waste of money, would not recommend.',
		);

		$rating = $this->generate_rating();

		if ( $rating >= 4 ) {
			$template = $this->get_faker()->randomElement( $positive_templates );
		} elseif ( $rating >= 3 ) {
			$template = $this->get_faker()->randomElement( $neutral_templates );
		} else {
			$template = $this->get_faker()->randomElement( $negative_templates );
		}

		// Add some variation to make reviews more realistic.
		$additional_comments = array(
			' ' . $this->get_faker()->sentence( $this->get_faker()->numberBetween( 3, 8 ) ),
			' The packaging was excellent.',
			' Arrived sooner than expected.',
			' Good communication from seller.',
			' Will buy again.',
			' Could be improved in some areas.',
		);

		if ( $this->get_faker()->boolean( 60 ) ) {
			$template .= $this->get_faker()->randomElement( $additional_comments );
		}

		return $template;
	}

	/**
	 * Get existing products for review creation.
	 *
	 * Retrieves published products to ensure reviews are linked to valid products.
	 *
	 * @since 2.0.3
	 *
	 * @return array Array of product objects.
	 */
	private function get_existing_products(): array {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 100, // Limit to avoid performance issues.
			'fields'         => 'ids',
		);

		$query = new \WP_Query( $args );
		return $query->posts;
	}

	/**
	 * Get existing customers for review creation.
	 *
	 * Retrieves users with customer role to ensure reviews have valid customer data.
	 *
	 * @since 2.0.3
	 *
	 * @return array Array of customer user objects.
	 */
	private function get_existing_customers(): array {
		$args = array(
			'role'   => 'customer',
			'number' => 100, // Limit to avoid performance issues.
		);

		$users = get_users( $args );
		return $users;
	}

	/**
	 * Generate reviews then recalculate average_rating for every affected product.
	 *
	 * @since 2.1.0
	 *
	 * @param int $count Number of reviews to generate.
	 * @return array|WP_Error
	 */
	public function generate( int $count ) {
		$results = parent::generate( $count );

		if ( is_wp_error( $results ) ) {
			return $results;
		}

		$product_ids = array_unique(
			array_filter( array_column( $results, 'product_id' ) )
		);

		foreach ( $product_ids as $product_id ) {
			$this->recalculate_product_rating( (int) $product_id );
		}

		return $results;
	}

	/**
	 * Recalculate and persist average_rating + rating_count post meta.
	 *
	 * @since 2.1.0
	 *
	 * @param int $product_id Post ID of the product.
	 */
	private function recalculate_product_rating( int $product_id ): void {
		$comments = get_comments(
			array(
				'post_id' => $product_id,
				'status'  => 'approve',
				'type'    => 'review',
			)
		);

		$ratings = array();
		foreach ( (array) $comments as $comment ) {
			if ( ! ( $comment instanceof \WP_Comment ) ) {
				continue;
			}

			$rating = (int) get_comment_meta( (int) $comment->comment_ID, 'rating', true );
			if ( $rating > 0 ) {
				$ratings[] = $rating;
			}
		}

		$average = count( $ratings ) > 0
			? round( array_sum( $ratings ) / count( $ratings ), 2 )
			: 0;

		update_post_meta( $product_id, 'average_rating', $average );
		update_post_meta( $product_id, 'rating_count', count( $ratings ) );
	}
}
