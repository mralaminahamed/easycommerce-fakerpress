<?php
/**
 * Coupon Generator.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Coupon;
use EasyCommerce\Models\Database;
use Exception;
use RuntimeException;
use WP_Error;

/**
 * Coupon Generator Class
 *
 * Generates realistic fake coupon data for EasyCommerce
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
	 * @return array|WP_Error Single coupon data, error, or false on failure.
	 */
	protected function generate_single_item() {
		try {
			// Check if EasyCommerce Coupon class exists.
			if ( ! class_exists( Coupon::class ) ) {
				return new WP_Error( 'missing_model', 'EasyCommerce Coupon model not found. Please ensure EasyCommerce plugin is active.' );
			}

			$coupon_data = $this->generate_coupon_data();

			// Check if coupon code already exists.
			if ( $this->coupon_code_exists( $coupon_data['code'] ) ) {
				return new WP_Error( 'code_exists', 'A coupon with this code already exists.' );
			}

			// Use EasyCommerce Coupon model with complete data structure.
			$coupon  = new Coupon();
			$created = $coupon->create(
				array(
					// Required fields.
					'name'          => $coupon_data['name'],
					'code'          => $coupon_data['code'],
					'discount_type' => $coupon_data['discount_type'],
					'amount'        => $coupon_data['amount'],

					// Optional fields.
					'active'        => $coupon_data['active'],
					'description'   => $coupon_data['description'],
					'meta'          => $coupon_data['meta'],

					// Coupon rules.
					'rules'         => $coupon_data['rules'],
				)
			);

			if ( ! $created ) {
				return new WP_Error( 'coupon_creation_failed', 'Failed to create coupon using EasyCommerce model.' );
			}

			// Reload coupon to get the complete object with rules.
			$coupon = new Coupon( $created );

			return array(
				'id'            => $coupon->get_id(),
				'name'          => $coupon_data['name'],
				'code'          => $coupon_data['code'],
				'discount_type' => $coupon_data['discount_type'],
				'amount'        => $coupon_data['amount'],
				'status'        => $coupon_data['active'] ? 'active' : 'inactive',
				'usage_limit'   => $this->get_rule_value( $coupon_data['rules'], 'usage_limit' ),
				'usage_count'   => 0, // New coupons start with 0 usage.
				'valid_from'    => $this->get_rule_value( $coupon_data['rules'], 'start_date' ),
				'valid_until'   => $this->get_rule_value( $coupon_data['rules'], 'end_date' ),
				'min_spend'     => $this->get_rule_value( $coupon_data['rules'], 'min_spend' ),
				'max_spend'     => $this->get_rule_value( $coupon_data['rules'], 'max_spend' ),
				'rules_count'   => count( $coupon_data['rules'] ),
				'description'   => $coupon_data['description'],
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
	 * @throws Exception If unable to generate a unique coupon code after 10 attempts.
	 */
	private function generate_coupon_data(): array {
		$discount_type = $this->faker->randomElement( array( 'percentage', 'fixed', 'fixed_product', 'buy_x_get_y' ) );
		$coupon_name   = $this->generate_coupon_name( $discount_type );
		$coupon_code   = $this->generate_unique_code();

		return array(
			'name'          => $coupon_name,
			'code'          => $coupon_code,
			'discount_type' => $discount_type,
			'amount'        => $this->generate_discount_amount( $discount_type ),
			'active'        => $this->faker->boolean( 90 ), // 90% active coupons
			'description'   => $this->generate_coupon_description( $discount_type ),
			'meta'          => $this->generate_coupon_meta(),
			'rules'         => $this->generate_coupon_rules( $discount_type ),
		);
	}

	/**
	 * Generate realistic coupon name based on discount type
	 *
	 * @since 1.0.0
	 *
	 * @param string $discount_type Discount type.
	 *
	 * @return string Coupon name.
	 */
	private function generate_coupon_name( string $discount_type ): string {
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
			'fixed_product' => array(
				'$5 Off Each Item',
				'$10 Off Selected Products',
				'$15 Off Tech Accessories',
				'$20 Off Apparel',
				'$25 Off Home Goods',
				'$50 Off Electronics',
				'Buy One, Save $10',
				'Per-Item Discount Deal',
			),
			'buy_x_get_y'   => array(
				'Buy 1 Get 1 Half Price',
				'Buy 2 Get 1 Free',
				'Buy 3 Get 50% Off',
				'Buy One, Get One 25% Off',
				'BOGO Special Offer',
				'Buy 2, Save $20',
				'Multi-Buy Discount',
				'Bundle and Save Deal',
			),
		);

		// Prioritize names matching the discount type, but allow fallback to other categories.
		$preferred_category = in_array(
			$discount_type,
			array(
				'percentage',
				'fixed',
				'fixed_product',
				'buy_x_get_y',
			),
			true
		) ? $discount_type : $this->faker->randomElement( array( 'seasonal', 'event', 'product', 'customer' ) );

		return $this->faker->randomElement( $name_types[ $preferred_category ] );
	}

	/**
	 * Generate coupon description
	 *
	 * @since 1.0.0
	 *
	 * @param string $discount_type Discount type.
	 *
	 * @return string Coupon description.
	 */
	private function generate_coupon_description( string $discount_type ): string {
		$prefixes = array(
			'Unlock savings with this exclusive offer! ',
			'Enjoy a special discount on your next purchase. ',
			'Save big with this limited-time deal! ',
			'Get more for less with this coupon. ',
			'Shop smarter with this amazing offer! ',
		);

		$details = array(
			'percentage'    => "Take {$this->generate_discount_amount( 'percentage' )}% off your entire order. Perfect for any shopping spree!",
			'fixed'         => "Save {$this->generate_discount_amount( 'fixed' )} on your next purchase. Ideal for all your favorite items!",
			'fixed_product' => "Get {$this->generate_discount_amount( 'fixed_product' )} off each eligible product. Stock up and save!",
			'buy_x_get_y'   => 'Buy one item and get another at a discount or free! Great for building your collection.',
		);

		return $this->faker->randomElement( $prefixes ) . ( $details[ $discount_type ] ?? $this->faker->sentence( 10, true ) );
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
			'created_by'    => $this->faker->userName(),
			'campaign_name' => $this->faker->randomElement(
				array(
					'Summer Campaign',
					'Holiday Promo',
					'Loyalty Program',
					'Flash Sale',
					'New User Acquisition',
				)
			),
			'priority'      => $this->faker->randomElement( array( 'low', 'medium', 'high' ) ),
			'last_updated'  => $this->faker->dateTimeThisYear()->format( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Generate unique coupon code
	 *
	 * @since 1.0.0
	 *
	 * @return string Unique coupon code.
	 * @throws RuntimeException If unable to generate a unique coupon code after 10 attempts.
	 */
	private function generate_unique_code(): string {
		$attempts = 0;
		do {
			$code     = $this->generate_coupon_code();
			$existing = $this->coupon_code_exists( $code );
			++$attempts;
		} while ( $existing && $attempts < 10 );

		if ( $existing ) {
			throw new RuntimeException( 'Unable to generate unique coupon code after 10 attempts.' );
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

		$pattern = $this->faker->randomElement( $code_patterns );

		return strtoupper( $this->faker->bothify( $pattern ) );
	}

	/**
	 * Generate discount amount based on type
	 *
	 * @since 1.0.0
	 *
	 * @param string $discount_type Discount type (percentage/fixed/fixed_product/buy_x_get_y).
	 *
	 * @return float Discount amount.
	 */
	private function generate_discount_amount( string $discount_type ): float {
		if ( 'percentage' === $discount_type ) {
			$percentages = array( 5, 10, 15, 20, 25, 30, 40, 50, 60, 70 );
			$weights     = array( 10, 20, 15, 15, 10, 10, 8, 5, 3, 2 );

			return $this->faker->randomElement(
				array_merge(
					...array_map(
						fn( $pct, $weight ) => array_fill( 0, $weight, $pct ),
						$percentages,
						$weights
					)
				)
			);
		}

		if ( 'fixed' === $discount_type ) {
			$amounts = array( 5, 10, 15, 20, 25, 30, 50, 75, 100, 200 );
			$weights = array( 15, 20, 15, 15, 10, 10, 8, 5, 3, 2 );

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

		if ( 'fixed_product' === $discount_type ) {
			$amounts = array( 2, 5, 10, 15, 20, 25 );
			$weights = array( 20, 20, 15, 10, 10, 5 );

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

		// buy_x_get_y.
		$amounts = array( 25, 50, 100 ); // Percentage off for the "get y" item.
		$weights = array( 20, 15, 5 );

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

	/**
	 * Generate comprehensive coupon rules
	 *
	 * @since 1.0.0
	 *
	 * @param string $discount_type Discount type.
	 *
	 * @return array Coupon rules.
	 */
	private function generate_coupon_rules( string $discount_type ): array {
		$rules = array();

		// Minimum spend requirement (75% chance).
		if ( $this->faker->boolean( 75 ) ) {
			$min_amounts = array( 20, 50, 75, 100, 150, 200, 250, 300, 500 );
			$weights     = array( 10, 20, 15, 15, 10, 10, 8, 5, 2 );
			$rules[]     = array(
				'type'  => 'min_spend',
				'value' => $this->faker->randomElement(
					array_merge(
						...array_map(
							fn( $amt, $weight ) => array_fill( 0, $weight, $amt ),
							$min_amounts,
							$weights
						)
					)
				),
			);
		}

		// Maximum spend limit (25% chance).
		if ( $this->faker->boolean( 25 ) ) {
			$max_amounts = array( 500, 1000, 1500, 2000, 3000, 5000 );
			$rules[]     = array(
				'type'  => 'max_spend',
				'value' => $this->faker->randomElement( $max_amounts ),
			);
		}

		// Date range (95% chance).
		if ( $this->faker->boolean( 95 ) ) {
			$start_date = $this->faker->dateTimeBetween( '-2 months', '+2 weeks' );
			$end_date   = $this->faker->dateTimeBetween( $start_date, '+6 months' );

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
		if ( $this->faker->boolean( 80 ) ) {
			$usage_limits = array( 1, 5, 10, 20, 50, 100, 200, 500 );
			$weights      = array( 10, 15, 20, 15, 15, 10, 8, 2 );
			$rules[]      = array(
				'type'  => 'usage_limit',
				'value' => $this->faker->randomElement(
					array_merge(
						...array_map(
							fn( $limit, $weight ) => array_fill( 0, $weight, $limit ),
							$usage_limits,
							$weights
						)
					)
				),
			);
		}

		// Usage limit per customer (50% chance).
		if ( $this->faker->boolean( 50 ) ) {
			$per_customer_limits = array( 1, 2, 3, 5, 10 );
			$weights             = array( 20, 15, 10, 5, 2 );
			$rules[]             = array(
				'type'  => 'usage_limit_per_customer',
				'value' => $this->faker->randomElement(
					array_merge(
						...array_map(
							fn( $limit, $weight ) => array_fill( 0, $weight, $limit ),
							$per_customer_limits,
							$weights
						)
					)
				),
			);
		}

		// Product restrictions (40% chance).
		if ( $this->faker->boolean( 40 ) ) {
			$product_ids = $this->get_random_product_ids();
			if ( ! empty( $product_ids ) ) {
				$restriction_type = $this->faker->randomElement( array( 'include_products', 'exclude_products' ) );
				$rules[]          = array(
					'type'  => $restriction_type,
					'value' => array_map( fn( $id ) => array( 'id' => $id ), $product_ids ),
				);
			}
		}

		// Category restrictions (35% chance).
		if ( $this->faker->boolean( 35 ) ) {
			$category_ids = $this->get_random_category_ids();
			if ( ! empty( $category_ids ) ) {
				$restriction_type = $this->faker->randomElement( array( 'include_categories', 'exclude_categories' ) );
				$rules[]          = array(
					'type'  => $restriction_type,
					'value' => array_map( fn( $id ) => array( 'id' => $id ), $category_ids ),
				);
			}
		}

		// Customer restrictions (20% chance).
		if ( $this->faker->boolean( 20 ) ) {
			$customer_types = array( 'new_customers', 'existing_customers', 'vip_customers', 'registered_users' );
			$rules[]        = array(
				'type'  => 'customer_restriction',
				'value' => $this->faker->randomElement( $customer_types ),
			);
		}

		// Free shipping (15% chance for fixed/percentage, 0% for others).
		if ( in_array( $discount_type, array( 'fixed', 'percentage' ), true ) && $this->faker->boolean( 15 ) ) {
			$rules[] = array(
				'type'  => 'free_shipping',
				'value' => true,
			);
		}

		// First time customer only (10% chance).
		if ( $this->faker->boolean( 10 ) ) {
			$rules[] = array(
				'type'  => 'first_time_customer',
				'value' => true,
			);
		}

		// Minimum quantity requirement (20% chance for fixed_product/buy_x_get_y).
		if ( in_array(
			$discount_type,
			array(
				'fixed_product',
				'buy_x_get_y',
			),
			true
		) && $this->faker->boolean( 20 ) ) {
			$min_quantities = array( 2, 3, 4, 5, 10 );
			$weights        = array( 20, 15, 10, 5, 2 );
			$rules[]        = array(
				'type'  => 'min_quantity',
				'value' => $this->faker->randomElement(
					array_merge(
						...array_map(
							fn( $qty, $weight ) => array_fill( 0, $weight, $qty ),
							$min_quantities,
							$weights
						)
					)
				),
			);
		}

		// Buy X Get Y specific rules (only for buy_x_get_y).
		if ( 'buy_x_get_y' === $discount_type ) {
			$rules[] = array(
				'type'  => 'buy_quantity',
				'value' => $this->faker->randomElement( array( 1, 2, 3 ) ),
			);
			$rules[] = array(
				'type'  => 'get_quantity',
				'value' => $this->faker->randomElement( array( 1, 2 ) ),
			);
		}

		// Stackable with other coupons (10% chance).
		if ( $this->faker->boolean( 10 ) ) {
			$rules[] = array(
				'type'  => 'stackable',
				'value' => true,
			);
		}

		// Apply to sale items (35% chance).
		if ( $this->faker->boolean( 35 ) ) {
			$rules[] = array(
				'type'  => 'apply_to_sale_items',
				'value' => $this->faker->boolean( 75 ), // 75% allow, 25% exclude sale items
			);
		}

		// Auto-apply coupon (10% chance).
		if ( $this->faker->boolean( 10 ) ) {
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

		return array_slice( $products, 0, $this->faker->numberBetween( 1, 6 ) );
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
				'number'     => 15,
				'orderby'    => 'rand',
				'fields'     => 'ids',
			)
		);

		if ( is_wp_error( $categories ) ) {
			return array();
		}

		return array_slice( $categories, 0, $this->faker->numberBetween( 1, 4 ) );
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
}
