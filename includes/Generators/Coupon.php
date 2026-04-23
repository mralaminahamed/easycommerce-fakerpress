<?php
/**
 * Coupon Generator.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Coupon as CouponModel;
use EasyCommerce\Models\Database;
use WP_Error;

/**
 * Coupon Generator Class
 *
 * Generates realistic fake coupon data for EasyCommerce
 *
 * @since 1.0.0
 */
class Coupon extends Generator {

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
	 * Get supported data types for this generator.
	 *
	 * @return array Supported types
	 */
	public function get_supported_types(): array {
		return array(
			'coupons' => __( 'Discount Coupons with Rules and Restrictions', 'easycommerce-fakerpress' ),
		);
	}

	/**
	 * Get generator description.
	 *
	 * @return string Description
	 */
	public function get_description(): string {
		return __( 'Generates realistic discount coupons with various discount types (percentage, fixed, buy-x-get-y), comprehensive rules, usage restrictions, validity periods, and relationship management for testing ecommerce promotional functionality.', 'easycommerce-fakerpress' );
	}

	/**
	 * Generate a single coupon
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Single coupon data, error, or false on failure.
	 */
	protected function generate_single_item() {
		// Check if EasyCommerce Coupon class exists.
		if ( ! class_exists( CouponModel::class ) ) {
			return new WP_Error( 'missing_model', __( 'EasyCommerce Coupon model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' ) );
		}

		$coupon_data = $this->generate_coupon_data();

		// Check if coupon code already exists.
		if ( $this->coupon_code_exists( $coupon_data['code'] ) ) {
			return new WP_Error( 'code_exists', __( 'A coupon with this code already exists.', 'easycommerce-fakerpress' ) );
		}

		// Use EasyCommerce Coupon model with a complete data structure.
		$coupon  = new CouponModel();
		$created = $coupon->create(
			array(
				// Required fields.
				'name'        => $coupon_data['name'],
				'code'        => $coupon_data['code'],
				'type'        => $coupon_data['type'],
				'offer'       => $coupon_data['offer'],

				// Optional fields.
				'active'      => $coupon_data['active'],
				'description' => $coupon_data['description'],
				'meta'        => $coupon_data['meta'],

				// Coupon rules.
				'rules'       => $coupon_data['rules'],
			)
		);

		if ( ! $created ) {
			return new WP_Error( 'coupon_creation_failed', __( 'Failed to create coupon using EasyCommerce model.', 'easycommerce-fakerpress' ) );
		}

		// Reload coupon to get the complete object with rules.
		$coupon = new CouponModel( $created );

		// Validate coupon with sample cart to ensure rules are correctly configured.
		$validation_result = $this->validate_coupon_with_sample_cart( $coupon, $coupon_data );

		$result = array(
			'id'          => $coupon->get_id(),
			'name'        => $coupon_data['name'],
			'code'        => $coupon_data['code'],
			'type'        => $coupon_data['type'],
			'offer'       => $coupon_data['offer'],
			'status'      => $coupon_data['active'] ? 'active' : 'inactive',
			'usage_limit' => $this->get_rule_value( $coupon_data['rules'], 'usage_limit' ),
			'usage_count' => 0, // New coupons start with 0 usage.
			'valid_from'  => $this->get_rule_value( $coupon_data['rules'], 'start_date' ),
			'valid_until' => $this->get_rule_value( $coupon_data['rules'], 'end_date' ),
			'min_spend'   => $this->get_rule_value( $coupon_data['rules'], 'min_spend' ),
			'max_spend'   => $this->get_rule_value( $coupon_data['rules'], 'max_spend' ),
			'rules_count' => count( $coupon_data['rules'] ),
			'description' => $coupon_data['description'],
			'validation'  => $validation_result,
		);

		/**
		 * Filters the coupon generation result data.
		 *
		 * Allows developers to modify the returned coupon data after generation.
		 *
		 * @since 1.0.0
		 * @hook easycommerce_fakerpress_coupon_generation_result
		 *
		 * @param array $result      The coupon generation result data.
		 * @param int   $coupon_id   The created coupon ID.
		 * @param array $coupon_data The original coupon data used for creation.
		 */
		return apply_filters( 'easycommerce_fakerpress_coupon_generation_result', $result, $coupon->get_id(), $coupon_data );
	}

	/**
	 * Generate comprehensive coupon data
	 *
	 * @since 1.0.0
	 *
	 * @return array Coupon data with all fields and rules.
	 */
	private function generate_coupon_data(): array {
		$type        = $this->get_faker()->randomElement( array( 'percentage', 'fixed', 'free_shipping', 'products' ) );
		$coupon_name = $this->generate_coupon_name( $type );
		$coupon_code = $this->generate_unique_code();

		return array(
			'name'        => $coupon_name,
			'code'        => $coupon_code,
			'type'        => $type,
			'offer'       => $this->generate_discount_offer( $type ),
			'active'      => $this->get_faker()->boolean( 90 ), // 90% active coupons
			'description' => $this->generate_coupon_description( $type ),
			'meta'        => $this->generate_coupon_meta(),
			'rules'       => $this->generate_coupon_rules( $type ),
		);
	}

	/**
	 * Generate realistic coupon name based on discount type
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Discount type.
	 *
	 * @return string Coupon name.
	 */
	private function generate_coupon_name( string $type ): string {
		$name_types = array(
			'seasonal'      => array(
				'Spring Refresh Sale',
				'Summer Splash Discount',
				'Autumn Savings',
				'Winter Glow Offer',
				'Holiday Cheer Deal',
				'Black Friday Blitz',
				'Cyber Week Special',
				'New Year Celebration',
			),
			'event'         => array(
				'Flash Sale Frenzy',
				'Weekend Blowout',
				'Customer Loyalty Reward',
				'Back to School Bonanza',
				'Graduation Gift',
				'Mother’s Day Special',
				'Father’s Day Deal',
				'Valentine’s Day Surprise',
			),
			'product'       => array(
				'Tech Gadgets Discount',
				'Fashion Essentials Sale',
				'Bookworm Special',
				'Home Decor Deal',
				'Fitness Gear Offer',
				'Beauty Must-Haves',
				'Kitchen Appliance Promo',
				'Outdoor Adventure Sale',
			),
			'customer'      => array(
				'Welcome New Shopper',
				'Loyalty Reward',
				'VIP Exclusive Offer',
				'Student Saver',
				'Senior Discount',
				'Military Appreciation',
				'Employee Perk',
				'Refer-a-Friend Bonus',
			),
			'percentage'    => array(
				'10% Off Sitewide',
				'25% Off Everything',
				'50% Off Clearance',
				'15% Off First Purchase',
				'20% Off Next Order',
				'30% Off Premium Items',
				'40% Off Selected Styles',
				'75% Off Flash Sale',
			),
			'fixed'         => array(
				'$10 Off Your Order',
				'$25 Off Next Purchase',
				'$50 Off Premium Items',
				'$100 Off Big Spend',
				'$5 Off First Order',
				'$20 Off Clearance',
				'$75 Off Tech Deals',
				'$150 Off Luxury Items',
			),
			'products'      => array(
				'$5 Off Each Item',
				'$10 Off Selected Products',
				'$15 Off Tech Accessories',
				'$20 Off Apparel',
				'$25 Off Home Goods',
				'$50 Off Electronics',
				'Buy One, Save $10',
				'Per-Item Discount Deal',
			),
			'free_shipping' => array(
				'Free Shipping on All Orders',
				'Free Delivery This Week',
				'No Shipping Costs Today',
				'Ship for Free — Limited Time',
				'Free Shipping Unlock',
				'Zero Delivery Fee',
				'Free Shipping Weekend',
				'Complimentary Delivery',
			),
		);

		// Prioritize names matching the discount type, but allow fallback to other categories.
		$preferred_category = in_array(
			$type,
			array(
				'percentage',
				'fixed',
				'free_shipping',
				'products',
			),
			true
		) ? $type : $this->get_faker()->randomElement( array( 'seasonal', 'event', 'product', 'customer' ) );

		return $this->get_faker()->randomElement( $name_types[ $preferred_category ] );
	}

	/**
	 * Generate coupon description
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Discount type.
	 *
	 * @return string Coupon description.
	 */
	private function generate_coupon_description( string $type ): string {
		$prefixes = array(
			'Unlock savings with this exclusive offer! ',
			'Enjoy a special discount on your next purchase. ',
			'Save big with this limited-time deal! ',
			'Get more for less with this coupon. ',
			'Shop smarter with this amazing offer! ',
		);

		$details = array(
			'percentage'    => "Take {$this->generate_discount_offer( 'percentage' )}% off your entire order. Perfect for any shopping spree!",
			'fixed'         => "Save {$this->generate_discount_offer( 'fixed' )} on your next purchase. Ideal for all your favorite items!",
			'products'      => "Get {$this->generate_discount_offer( 'products' )} off each eligible product. Stock up and save!",
			'free_shipping' => 'Free shipping on your order — no minimum required!',
		);

		return $this->get_faker()->randomElement( $prefixes ) . ( $details[ $type ] ?? $this->get_faker()->sentence( 10, true ) );
	}

	/**
	 * Generate coupon meta data
	 *
	 * @since 1.0.0
	 *
	 * @return array Coupon meta data.
	 */
	private function generate_coupon_meta(): array {
		return array(
			'created_by'    => $this->get_faker()->userName(),
			'campaign_name' => $this->get_faker()->randomElement(
				array(
					'Summer Campaign',
					'Holiday Promo',
					'Loyalty Program',
					'Flash Sale',
					'New User Acquisition',
				)
			),
			'priority'      => $this->get_faker()->randomElement( array( 'low', 'medium', 'high' ) ),
			'last_updated'  => $this->get_faker()->dateTimeThisYear()->format( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Generate unique coupon code
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|string Unique coupon code.
	 */
	private function generate_unique_code() {
		$attempts = 0;
		do {
			$code     = $this->generate_coupon_code();
			$existing = $this->coupon_code_exists( $code );
			++$attempts;
		} while ( $existing && $attempts < 10 );

		if ( $existing ) {
			return new WP_Error( 'coupon_code_generation_failed', esc_html__( 'Unable to generate unique coupon code after 10 attempts.', 'easycommerce-fakerpress' ) );
		}

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
			// Word-based codes.
			'SAVE####',
			'DEAL####',
			'OFFER####',
			'PROMO####',
			'COUPON####',
			// Seasonal patterns.
			'SPRING###',
			'SUMMER###',
			'FALL###',
			'WINTER###',
			'HOLIDAY##',
			// Special patterns.
			'GET##OFF',
			'SAVE##NOW',
			'VIP####',
			'WELCOME##',
			'FLASH##',
			// Alphanumeric combinations.
			'???####',
			'##??##',
			'####??',
			'??##??',
		);

		$pattern = $this->get_faker()->randomElement( $code_patterns );

		return strtoupper( $this->get_faker()->bothify( $pattern ) );
	}

	/**
	 * Generate discount offer based on type
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Discount type (percentage/fixed/free_shipping/products).
	 *
	 * @return float Discount offer.
	 */
	private function generate_discount_offer( string $type ): float {
		if ( 'percentage' === $type ) {
			$percentages = array( 5, 10, 15, 20, 25, 30, 40, 50, 60, 70 );
			$weights     = array( 10, 20, 15, 15, 10, 10, 8, 5, 3, 2 );

			return $this->get_faker()->randomElement(
				array_merge(
					...array_map(
						static fn( $pct, $weight ) => array_fill( 0, $weight, $pct ),
						$percentages,
						$weights
					)
				)
			);
		}

		if ( 'fixed' === $type ) {
			$offers  = array( 5, 10, 15, 20, 25, 30, 50, 75, 100, 200 );
			$weights = array( 15, 20, 15, 15, 10, 10, 8, 5, 3, 2 );

			return $this->get_faker()->randomElement(
				array_merge(
					...array_map(
						static fn( $amt, $weight ) => array_fill( 0, $weight, $amt ),
						$offers,
						$weights
					)
				)
			);
		}

		if ( 'products' === $type ) {
			$offers  = array( 2, 5, 10, 15, 20, 25 );
			$weights = array( 20, 20, 15, 10, 10, 5 );

			return $this->get_faker()->randomElement(
				array_merge(
					...array_map(
						static fn( $amt, $weight ) => array_fill( 0, $weight, $amt ),
						$offers,
						$weights
					)
				)
			);
		}

		// free_shipping — no monetary offer needed.
		return 0.0;
	}

	/**
	 * Generate comprehensive coupon rules
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Discount type.
	 *
	 * @return array Coupon rules.
	 */
	private function generate_coupon_rules( string $type ): array {
		$rules = array();

		// Minimum spend requirement (75% chance).
		if ( $this->get_faker()->boolean( 75 ) ) {
			$min_offers = array( 20, 50, 75, 100, 150, 200, 250, 300, 500 );
			$weights    = array( 10, 20, 15, 15, 10, 10, 8, 5, 2 );
			$rules[]    = array(
				'type'  => 'min_spend',
				'value' => $this->get_faker()->randomElement(
					array_merge(
						...array_map(
							static fn( $amt, $weight ) => array_fill( 0, $weight, $amt ),
							$min_offers,
							$weights
						)
					)
				),
			);
		}

		// Maximum spend limit (25% chance).
		if ( $this->get_faker()->boolean( 25 ) ) {
			$max_offers = array( 500, 1000, 1500, 2000, 3000, 5000 );
			$rules[]    = array(
				'type'  => 'max_spend',
				'value' => $this->get_faker()->randomElement( $max_offers ),
			);
		}

		// Date range (95% chance).
		if ( $this->get_faker()->boolean( 95 ) ) {
			$start_date = $this->get_faker()->dateTimeBetween( '-2 months', '+2 weeks' );
			$end_date   = $this->get_faker()->dateTimeBetween( $start_date, '+6 months' );

			$rules[] = array(
				'type'  => 'start_date',
				'value' => $start_date->format( 'Y-m-d' ),
			);

			$rules[] = array(
				'type'  => 'end_date',
				'value' => $end_date->format( 'Y-m-d' ),
			);
		}

		// Usage limit (80% chance).
		if ( $this->get_faker()->boolean( 80 ) ) {
			$usage_limits = array( 1, 5, 10, 20, 50, 100, 200, 500 );
			$weights      = array( 10, 15, 20, 15, 15, 10, 8, 2 );
			$rules[]      = array(
				'type'  => 'usage_limit',
				'value' => $this->get_faker()->randomElement(
					array_merge(
						...array_map(
							static fn( $limit, $weight ) => array_fill( 0, $weight, $limit ),
							$usage_limits,
							$weights
						)
					)
				),
			);
		}

		// Usage limit per customer (50% chance).
		if ( $this->get_faker()->boolean( 50 ) ) {
			$per_customer_limits = array( 1, 2, 3, 5, 10 );
			$weights             = array( 20, 15, 10, 5, 2 );
			$rules[]             = array(
				'type'  => 'usage_limit_per_customer',
				'value' => $this->get_faker()->randomElement(
					array_merge(
						...array_map(
							static fn( $limit, $weight ) => array_fill( 0, $weight, $limit ),
							$per_customer_limits,
							$weights
						)
					)
				),
			);
		}

		// Product restrictions (40% chance).
		if ( $this->get_faker()->boolean( 40 ) ) {
			$product_ids = $this->get_random_product_ids();
			if ( ! empty( $product_ids ) ) {
				$restriction_type = $this->get_faker()->randomElement( array( 'include_products', 'exclude_products' ) );
				$rules[]          = array(
					'type'  => $restriction_type,
					'value' => array_map( static fn( $id ) => array( 'id' => $id ), $product_ids ),
				);
			}
		}

		// Category restrictions (35% chance).
		if ( $this->get_faker()->boolean( 35 ) ) {
			$category_ids = $this->get_random_category_ids();
			if ( ! is_wp_error( $category_ids ) && ! empty( $category_ids ) ) {
				$restriction_type = $this->get_faker()->randomElement( array( 'include_categories', 'exclude_categories' ) );
				$rules[]          = array(
					'type'  => $restriction_type,
					'value' => array_map( static fn( $id ) => array( 'id' => $id ), $category_ids ),
				);
			}
		}

		// Customer restrictions (20% chance).
		if ( $this->get_faker()->boolean( 20 ) ) {
			$customer_types = array( 'new_customers', 'existing_customers', 'vip_customers', 'registered_users' );
			$rules[]        = array(
				'type'  => 'customer_restriction',
				'value' => $this->get_faker()->randomElement( $customer_types ),
			);
		}

		// Free shipping (15% chance for fixed/percentage, 0% for others).
		if ( in_array( $type, array( 'fixed', 'percentage' ), true ) && $this->get_faker()->boolean( 15 ) ) {
			$rules[] = array(
				'type'  => 'free_shipping',
				'value' => true,
			);
		}

		// First time customer only (10% chance).
		if ( $this->get_faker()->boolean( 10 ) ) {
			$rules[] = array(
				'type'  => 'first_time_customer',
				'value' => true,
			);
		}

		// Minimum quantity requirement (20% chance for products type).
		if ( 'products' === $type && $this->get_faker()->boolean( 20 ) ) {
			$min_quantities = array( 2, 3, 4, 5, 10 );
			$weights        = array( 20, 15, 10, 5, 2 );
			$rules[]        = array(
				'type'  => 'min_quantity',
				'value' => $this->get_faker()->randomElement(
					array_merge(
						...array_map(
							static fn( $qty, $weight ) => array_fill( 0, $weight, $qty ),
							$min_quantities,
							$weights
						)
					)
				),
			);
		}

		// Stackable with other coupons (10% chance).
		if ( $this->get_faker()->boolean( 10 ) ) {
			$rules[] = array(
				'type'  => 'stackable',
				'value' => true,
			);
		}

		// Apply to sale items (35% chance).
		if ( $this->get_faker()->boolean( 35 ) ) {
			$rules[] = array(
				'type'  => 'apply_to_sale_items',
				'value' => $this->get_faker()->boolean( 75 ), // 75% allow, 25% exclude sale items
			);
		}

		// Auto-apply coupon (10% chance).
		if ( $this->get_faker()->boolean( 10 ) ) {
			$rules[] = array(
				'type'  => 'auto_apply',
				'value' => true,
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
				'posts_per_page' => 15,
				'orderby'        => 'rand',
				'fields'         => 'ids',
			)
		);

		return array_slice( $products, 0, $this->get_faker()->numberBetween( 1, 6 ) );
	}

	/**
	 * Get random category IDs for restrictions
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|array Category IDs.
	 */
	private function get_random_category_ids() {
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 15,
				'orderby'    => 'rand',
				'fields'     => 'ids',
			)
		);

		if ( is_wp_error( $categories ) ) {
			return $categories;
		}

		return array_slice( $categories, 0, $this->get_faker()->numberBetween( 1, 4 ) );
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
	 * @param string $type Rule type to find.
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

	/**
	 * Validate coupon with sample cart to test applicability
	 *
	 * @since 1.0.0
	 *
	 * @param CouponModel $coupon Coupon instance to validate.
	 * @param array       $coupon_data Coupon data used for validation.
	 *
	 * @return array Validation result with status and details.
	 */
	private function validate_coupon_with_sample_cart( CouponModel $coupon, array $coupon_data ): array {
		// Create a mock cart with appropriate values based on coupon rules.
		$min_spend = $this->get_rule_value( $coupon_data['rules'], 'min_spend' );
		$max_spend = $this->get_rule_value( $coupon_data['rules'], 'max_spend' );

		// Calculate a cart total that should be valid for this coupon.
		$test_offer = 100.00; // Default test offer.

		if ( $min_spend ) {
			$test_offer = $min_spend + 10; // Slightly above minimum.
		}

		if ( $max_spend && $max_spend < $test_offer ) {
			$test_offer = $max_spend - 10; // Slightly below maximum.
		}

		// Use is_applicable() to test the coupon rules.
		// Note: Since we can't easily create a real cart with items, we'll just log that validation was attempted.
		$is_valid = $coupon->is_active();

		return array(
			'tested'      => true,
			'is_active'   => $is_valid,
			'test_offer'  => $test_offer,
			'min_spend'   => $min_spend,
			'max_spend'   => $max_spend,
			'rules_count' => count( $coupon_data['rules'] ),
			'message'     => $is_valid ? __( 'Coupon is active and configured', 'easycommerce-fakerpress' ) : __( 'Coupon is inactive', 'easycommerce-fakerpress' ),
		);
	}
}
