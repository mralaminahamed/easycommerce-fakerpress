<?php
/**
 * Coupon Generator
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Coupon;
use EasyCommerce\Models\Database;
use Exception;
use WP_Error;

/**
 * Coupon Generator Class
 *
 * Generates fake coupon data for EasyCommerce
 *
 * @since 1.0.0
 */
class Coupon_Generator extends Generator {

	/**
	 * Get the resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'coupon';
	}

	/**
	 * Generate a single coupon
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error|false Single coupon data, error, or false on failure.
	 */
	protected function generate_single_item() {
		try {
			// Check if EasyCommerce Coupon class exists.
			if ( ! class_exists( Coupon::class ) ) {
				return new WP_Error( 'missing_model', 'EasyCommerce Coupon model not found. Please ensure EasyCommerce plugin is active.' );
			}

			$coupon_data = $this->generate_coupon_data();

			// Check if coupon code already exists
			if ( $this->coupon_code_exists( $coupon_data['code'] ) ) {
				return new WP_Error( 'code_exists', 'A coupon with this code already exists.' );
			}

			// Use EasyCommerce Coupon model with complete data structure
			$coupon  = new Coupon();
			$created = $coupon->create(
				array(
					// Required fields
					'name'          => $coupon_data['name'],
					'code'          => $coupon_data['code'],
					'discount_type' => $coupon_data['discount_type'],
					'amount'        => $coupon_data['amount'],

					// Optional fields
					'active'        => $coupon_data['active'],

					// Coupon rules (handled by EasyCommerce model)
					'rules'         => $coupon_data['rules'],
				)
			);

			if ( ! $created ) {
				return new WP_Error( 'coupon_creation_failed', 'Failed to create coupon using EasyCommerce model.' );
			}

			// Reload coupon to get the complete object with rules
			$coupon = new Coupon( $created );

			return array(
				'id'            => $coupon->get_id(),
				'name'          => $coupon_data['name'],
				'code'          => $coupon_data['code'],
				'discount_type' => $coupon_data['discount_type'],
				'amount'        => $coupon_data['amount'],
				'status'        => $coupon_data['active'] ? 'active' : 'inactive',
				'usage_limit'   => $this->get_rule_value( $coupon_data['rules'], 'usage_limit' ),
				'usage_count'   => 0, // New coupons start with 0 usage
				'valid_from'    => $this->get_rule_value( $coupon_data['rules'], 'start_date' ),
				'valid_until'   => $this->get_rule_value( $coupon_data['rules'], 'end_date' ),
				'min_spend'     => $this->get_rule_value( $coupon_data['rules'], 'min_spend' ),
				'max_spend'     => $this->get_rule_value( $coupon_data['rules'], 'max_spend' ),
				'rules_count'   => count( $coupon_data['rules'] ),
			);
		} catch ( Exception $e ) {
			$this->log( 'Coupon creation failed: ' . $e->getMessage(), 'error' );

			return new WP_Error( 'coupon_creation_failed', $e->getMessage() );
		}
	}

	/**
	 * Generate comprehensive coupon data
	 *
	 * @since 1.0.0
	 *
	 * @return array Coupon data with all fields and rules.
	 */
	private function generate_coupon_data(): array {
		$discount_type = $this->faker->randomElement( array( 'percentage', 'fixed' ) );
		$coupon_name   = $this->generate_coupon_name();
		$coupon_code   = $this->generate_unique_code();

		return array(
			'name'          => $coupon_name,
			'code'          => $coupon_code,
			'discount_type' => $discount_type,
			'amount'        => $this->generate_discount_amount( $discount_type ),
			'active'        => $this->faker->boolean( 85 ), // 85% active coupons
			'rules'         => $this->generate_coupon_rules(),
		);
	}

	/**
	 * Generate realistic coupon name
	 *
	 * @since 1.0.0
	 *
	 * @return string Coupon name.
	 */
	private function generate_coupon_name(): string {
		$name_types = array(
			'seasonal'   => array(
				'Spring Sale',
				'Summer Savings',
				'Fall Special',
				'Winter Deals',
				'Holiday Discount',
				'Black Friday',
				'Cyber Monday',
				'New Year Sale',
			),
			'event'      => array(
				'Flash Sale',
				'Weekend Special',
				'Customer Appreciation',
				'Back to School',
				'Graduation Sale',
				'Mother\'s Day',
				'Father\'s Day',
				'Valentine\'s Special',
			),
			'product'    => array(
				'Electronics Discount',
				'Fashion Sale',
				'Book Lovers Deal',
				'Home & Garden',
				'Sports Equipment',
				'Beauty Products',
				'Tech Gadgets',
				'Kitchen Essentials',
			),
			'customer'   => array(
				'First Time Buyer',
				'Loyal Customer',
				'VIP Member',
				'Student Discount',
				'Senior Citizen',
				'Military Discount',
				'Employee Special',
				'Referral Bonus',
			),
			'percentage' => array(
				'10% Off Everything',
				'25% Off Sale',
				'50% Off Selected',
				'15% Off Orders',
				'20% Off First Order',
				'30% Off Premium',
				'Buy 2 Get 1 Free',
				'Half Price Deal',
			),
		);

		$category = $this->faker->randomElement( array_keys( $name_types ) );

		return $this->faker->randomElement( $name_types[ $category ] );
	}

	/**
	 * Generate unique coupon code
	 *
	 * @since 1.0.0
	 *
	 * @return string Unique coupon code.
	 */
	private function generate_unique_code(): string {
		$attempts = 0;
		do {
			$code     = $this->generate_coupon_code();
			$existing = $this->coupon_code_exists( $code );
			++$attempts;
		} while ( $existing && $attempts < 10 );

		return $code;
	}

	/**
	 * Generate random coupon code
	 *
	 * @since 1.0.0
	 *
	 * @return string Random coupon code.
	 */
	private function generate_coupon_code(): string {
		$code_patterns = array(
			// Word-based codes
			'SAVE###',
			'DEAL###',
			'OFFER###',
			'SALE###',
			'DISCOUNT###',

			// Letter-number combinations
			'???###',
			'??##??',
			'###???',

			// Seasonal patterns
			'SPRING##',
			'SUMMER##',
			'FALL##',
			'WINTER##',

			// Special patterns
			'GET##OFF',
			'SAVE##NOW',
			'BUY#GET#',
			'VIP###',
			'WELCOME##',
		);

		$pattern = $this->faker->randomElement( $code_patterns );

		return strtoupper( $this->faker->bothify( $pattern ) );
	}

	/**
	 * Generate discount amount based on type
	 *
	 * @since 1.0.0
	 *
	 * @param string $discount_type Discount type (percentage/fixed).
	 *
	 * @return float Discount amount.
	 */
	private function generate_discount_amount( string $discount_type ): float {
		if ( $discount_type === 'percentage' ) {
			// Common percentage discounts
			$percentages = array( 5, 10, 15, 20, 25, 30, 40, 50, 60, 75 );
			$weights     = array( 5, 20, 15, 20, 15, 10, 8, 5, 1, 1 );

			return $this->faker->randomElement(
				array_merge(
					...array_map(
						fn( $pct, $weight ) => array_fill( 0, $weight, $pct ),
						$percentages,
						$weights
					)
				)
			);
		} else {
			// Fixed amount discounts
			$amounts = array( 5, 10, 15, 20, 25, 50, 75, 100, 150, 200 );
			$weights = array( 10, 20, 15, 15, 12, 10, 8, 5, 3, 2 );

			return $this->faker->randomElement(
				array_merge(
					...array_map(
						fn( $amt, $weight ) => array_fill( 0, $weight, $amt ),
						$amounts,
						$weights
					)
				)
			);
		}
	}

	/**
	 * Generate comprehensive coupon rules
	 *
	 * @since 1.0.0
	 *
	 * @return array Coupon rules.
	 */
	private function generate_coupon_rules(): array {
		$rules = array();

		// Minimum spend requirement (80% chance)
		if ( $this->faker->boolean( 80 ) ) {
			$min_amounts = array( 25, 50, 75, 100, 150, 200, 300, 500 );
			$rules[]     = array(
				'type'  => 'min_spend',
				'value' => $this->faker->randomElement( $min_amounts ),
			);
		}

		// Maximum spend limit (20% chance)
		if ( $this->faker->boolean( 20 ) ) {
			$max_amounts = array( 500, 1000, 1500, 2000, 5000 );
			$rules[]     = array(
				'type'  => 'max_spend',
				'value' => $this->faker->randomElement( $max_amounts ),
			);
		}

		// Date range (90% chance)
		if ( $this->faker->boolean( 90 ) ) {
			$start_date = $this->faker->dateTimeBetween( '-1 month', '+1 week' );
			$end_date   = $this->faker->dateTimeBetween( $start_date, '+3 months' );

			$rules[] = array(
				'type'  => 'start_date',
				'value' => $start_date->format( 'Y-m-d' ),
			);

			$rules[] = array(
				'type'  => 'end_date',
				'value' => $end_date->format( 'Y-m-d' ),
			);
		}

		// Usage limit (70% chance)
		if ( $this->faker->boolean( 70 ) ) {
			$usage_limits = array( 1, 5, 10, 25, 50, 100, 250, 500, 1000 );
			$weights      = array( 5, 10, 15, 20, 15, 15, 10, 8, 2 );

			$usage_limit = $this->faker->randomElement(
				array_merge(
					...array_map(
						fn( $limit, $weight ) => array_fill( 0, $weight, $limit ),
						$usage_limits,
						$weights
					)
				)
			);

			$rules[] = array(
				'type'  => 'usage_limit',
				'value' => $usage_limit,
			);
		}

		// Usage limit per customer (40% chance)
		if ( $this->faker->boolean( 40 ) ) {
			$per_customer_limits = array( 1, 2, 3, 5 );
			$rules[]             = array(
				'type'  => 'usage_limit_per_customer',
				'value' => $this->faker->randomElement( $per_customer_limits ),
			);
		}

		// Product restrictions (30% chance)
		if ( $this->faker->boolean( 30 ) ) {
			$product_ids = $this->get_random_product_ids();
			if ( ! empty( $product_ids ) ) {
				$restriction_type = $this->faker->randomElement( array( 'include_products', 'exclude_products' ) );
				$rules[]          = array(
					'type'  => $restriction_type,
					'value' => array_map( fn( $id ) => array( 'id' => $id ), $product_ids ),
				);
			}
		}

		// Category restrictions (25% chance)
		if ( $this->faker->boolean( 25 ) ) {
			$category_ids = $this->get_random_category_ids();
			if ( ! empty( $category_ids ) ) {
				$restriction_type = $this->faker->randomElement( array( 'include_categories', 'exclude_categories' ) );
				$rules[]          = array(
					'type'  => $restriction_type,
					'value' => array_map( fn( $id ) => array( 'id' => $id ), $category_ids ),
				);
			}
		}

		// Customer restrictions (15% chance)
		if ( $this->faker->boolean( 15 ) ) {
			$customer_types = array( 'new_customers', 'existing_customers', 'vip_customers' );
			$rules[]        = array(
				'type'  => 'customer_restriction',
				'value' => $this->faker->randomElement( $customer_types ),
			);
		}

		// Free shipping (10% chance)
		if ( $this->faker->boolean( 10 ) ) {
			$rules[] = array(
				'type'  => 'free_shipping',
				'value' => true,
			);
		}

		// First time customer only (8% chance)
		if ( $this->faker->boolean( 8 ) ) {
			$rules[] = array(
				'type'  => 'first_time_customer',
				'value' => true,
			);
		}

		// Minimum quantity requirement (15% chance)
		if ( $this->faker->boolean( 15 ) ) {
			$min_quantities = array( 2, 3, 5, 10 );
			$rules[]        = array(
				'type'  => 'min_quantity',
				'value' => $this->faker->randomElement( $min_quantities ),
			);
		}

		// Stackable with other coupons (5% chance)
		if ( $this->faker->boolean( 5 ) ) {
			$rules[] = array(
				'type'  => 'stackable',
				'value' => true,
			);
		}

		// Apply to sale items (30% chance)
		if ( $this->faker->boolean( 30 ) ) {
			$rules[] = array(
				'type'  => 'apply_to_sale_items',
				'value' => $this->faker->boolean( 70 ), // 70% allow, 30% exclude sale items
			);
		}

		return $rules;
	}

	/**
	 * Get random product IDs for restrictions
	 *
	 * @since 1.0.0
	 *
	 * @return array Product IDs.
	 */
	private function get_random_product_ids(): array {
		$products = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 10,
				'orderby'        => 'rand',
				'fields'         => 'ids',
			)
		);

		return array_slice( $products, 0, $this->faker->numberBetween( 1, 5 ) );
	}

	/**
	 * Get random category IDs for restrictions
	 *
	 * @since 1.0.0
	 *
	 * @return array Category IDs.
	 */
	private function get_random_category_ids(): array {
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 10,
				'orderby'    => 'rand',
				'fields'     => 'ids',
			)
		);

		if ( is_wp_error( $categories ) ) {
			return array();
		}

		return array_slice( $categories, 0, $this->faker->numberBetween( 1, 3 ) );
	}

	/**
	 * Check if coupon code already exists using EasyCommerce model
	 *
	 * @since 1.0.0
	 *
	 * @param string $code Coupon code to check.
	 *
	 * @return bool True if code exists, false otherwise.
	 */
	private function coupon_code_exists( string $code ): bool {
		$db       = new Database( 'coupons' );
		$existing = $db->get_row( array( 'code' => $code ) );

		return ! empty( $existing );
	}

	/**
	 * Get rule value by type
	 *
	 * @since 1.0.0
	 *
	 * @param array  $rules Rules array.
	 * @param string $type  Rule type to find.
	 *
	 * @return mixed Rule value or null if not found.
	 */
	private function get_rule_value( array $rules, string $type ) {
		foreach ( $rules as $rule ) {
			if ( $rule['type'] === $type ) {
				return $rule['value'];
			}
		}

		return null;
	}
}
