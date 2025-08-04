<?php
/**
 * Coupon Generator
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
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
		$coupon_code   = $this->generate_unique_code();
		$coupon_name   = $this->generate_coupon_name();
		$discount_type = $this->faker->randomElement( array( 'percentage', 'fixed' ) );
		$amount        = $this->generate_discount_amount( $discount_type );

		// Insert into EasyCommerce coupons table.
		$coupon_data = array(
			'status'        => 1, // Active.
			'name'          => $coupon_name,
			'code'          => $coupon_code,
			'discount_type' => $discount_type,
			'amount'        => $amount,
			'active'        => 1,
			'created_at'    => $this->faker->dateTimeBetween( '-6 months', 'now' )->format( 'Y-m-d H:i:s' ),
			'updated_at'    => wp_date( 'Y-m-d H:i:s' ),
		);

		$coupons_table = $this->wpdb->prefix . 'coupons';
		$this->wpdb->insert( $coupons_table, $coupon_data );
		$coupon_id = $this->wpdb->insert_id;

		if ( ! $coupon_id ) {
			return false;
		}

		try {
			// Add coupon rules.
			$this->add_coupon_rules( $coupon_id );

			return array(
				'id'     => $coupon_id,
				'name'   => $coupon_name,
				'code'   => $coupon_code,
				'amount' => $amount,
				'type'   => $discount_type,
			);
		} catch ( Exception $e ) {
			// Clean up the created coupon if rules insertion fails.
			$this->wpdb->delete( $coupons_table, array( 'id' => $coupon_id ) );

			return new WP_Error( 'coupon_creation_failed', $e->getMessage() );
		}
	}

	/**
	 * Generate a unique coupon code
	 *
	 * Attempts to generate a unique coupon code by checking against existing codes
	 * in the database. Will retry up to 10 times if duplicates are found.
	 *
	 * @since 1.0.0
	 *
	 * @return string Unique coupon code.
	 */
	private function generate_unique_code(): string {
		$attempts = 0;
		do {
			$code     = $this->generate_coupon_code();
			$existing = $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT id FROM {$this->wpdb->prefix}coupons WHERE code = %s",
					$code
				)
			);
			++$attempts;
		} while ( $existing && $attempts < 10 );

		return $code;
	}

	/**
	 * Generate a random coupon code
	 *
	 * Creates a random alphanumeric coupon code using various patterns.
	 *
	 * @since 1.0.0
	 *
	 * @return string Generated coupon code.
	 */
	private function generate_coupon_code(): string {
		$patterns = array(
			'[A-Z]{4}[0-9]{2}',     // ABCD12.
			'[A-Z]{3}[0-9]{3}',     // ABC123.
			'[A-Z]{2}[0-9]{4}',     // AB1234.
			'SAVE[0-9]{2}',         // SAVE20.
			'GET[0-9]{2}OFF',       // GET15OFF.
			'[A-Z]{6}',             // ABCDEF.
		);

		return $this->faker->regexify( $this->faker->randomElement( $patterns ) );
	}

	/**
	 * Generate a descriptive coupon name
	 *
	 * Creates a human-readable name for the coupon based on various themes.
	 *
	 * @since 1.0.0
	 *
	 * @return string Generated coupon name.
	 */
	private function generate_coupon_name(): string {
		$themes = array(
			'seasonal' => array( 'Summer Sale', 'Winter Discount', 'Spring Special', 'Holiday Offer', 'Black Friday Deal' ),
			'customer' => array( 'New Customer Welcome', 'Loyal Customer Reward', 'VIP Member Discount', 'First Time Buyer' ),
			'product'  => array( 'Electronics Sale', 'Fashion Week', 'Home & Garden', 'Sports Equipment', 'Book Lovers' ),
			'general'  => array( 'Flash Sale', 'Weekend Special', 'Limited Time', 'Clearance Event', 'Mega Discount' ),
		);

		$theme_key = $this->faker->randomElement( array_keys( $themes ) );
		return $this->faker->randomElement( $themes[ $theme_key ] );
	}

	/**
	 * Generate discount amount based on type
	 *
	 * Creates appropriate discount amounts for percentage or fixed discount types.
	 *
	 * @since 1.0.0
	 *
	 * @param string $discount_type The type of discount ('percentage' or 'fixed').
	 *
	 * @return float Generated discount amount.
	 */
	private function generate_discount_amount( string $discount_type ): float {
		if ( 'percentage' === $discount_type ) {
			// Percentage discounts: 5% to 50%.
			return $this->faker->randomFloat( 2, 5, 50 );
		}

		// Fixed amount discounts: $5 to $100.
		return $this->faker->randomFloat( 2, 5, 100 );
	}

	/**
	 * Add rules and restrictions to coupon
	 *
	 * Creates various rules and restrictions for the coupon including usage limits,
	 * expiry dates, and applicable conditions.
	 *
	 * @since 1.0.0
	 *
	 * @param int $coupon_id The coupon ID to add rules to.
	 *
	 * @return void
	 */
	private function add_coupon_rules( int $coupon_id ): void {
		$coupon_rules_table = $this->wpdb->prefix . 'coupon_rules';

		// Generate expiry date (30 days to 1 year from now).
		$expiry_date = $this->faker->dateTimeBetween( '+30 days', '+1 year' )->format( 'Y-m-d H:i:s' );

		// Usage limits.
		$usage_limit_per_coupon = $this->faker->optional( 0.7 )->numberBetween( 10, 1000 );
		$usage_limit_per_user   = $this->faker->optional( 0.5 )->numberBetween( 1, 5 );

		// Minimum order amount.
		$minimum_amount = $this->faker->optional( 0.6 )->randomFloat( 2, 20, 200 );

		// Maximum discount amount (for percentage coupons).
		$maximum_discount_amount = $this->faker->optional( 0.4 )->randomFloat( 2, 10, 500 );

		$rules = array(
			array(
				'coupon_id'  => $coupon_id,
				'rule_type'  => 'expiry_date',
				'rule_value' => $expiry_date,
				'created_at' => wp_date( 'Y-m-d H:i:s' ),
			),
		);

		if ( $usage_limit_per_coupon ) {
			$rules[] = array(
				'coupon_id'  => $coupon_id,
				'rule_type'  => 'usage_limit_per_coupon',
				'rule_value' => $usage_limit_per_coupon,
				'created_at' => wp_date( 'Y-m-d H:i:s' ),
			);
		}

		if ( $usage_limit_per_user ) {
			$rules[] = array(
				'coupon_id'  => $coupon_id,
				'rule_type'  => 'usage_limit_per_user',
				'rule_value' => $usage_limit_per_user,
				'created_at' => wp_date( 'Y-m-d H:i:s' ),
			);
		}

		if ( $minimum_amount ) {
			$rules[] = array(
				'coupon_id'  => $coupon_id,
				'rule_type'  => 'minimum_amount',
				'rule_value' => number_format( $minimum_amount, 2, '.', '' ),
				'created_at' => wp_date( 'Y-m-d H:i:s' ),
			);
		}

		if ( $maximum_discount_amount ) {
			$rules[] = array(
				'coupon_id'  => $coupon_id,
				'rule_type'  => 'maximum_discount_amount',
				'rule_value' => number_format( $maximum_discount_amount, 2, '.', '' ),
				'created_at' => wp_date( 'Y-m-d H:i:s' ),
			);
		}

		// Add product/category restrictions (optional).
		if ( $this->faker->boolean( 30 ) ) {
			$restricted_categories = $this->get_random_product_categories();
			if ( ! empty( $restricted_categories ) ) {
				$rules[] = array(
					'coupon_id'  => $coupon_id,
					'rule_type'  => 'allowed_categories',
					'rule_value' => implode( ',', $restricted_categories ),
					'created_at' => wp_date( 'Y-m-d H:i:s' ),
				);
			}
		}

		// Add customer restrictions (optional).
		if ( $this->faker->boolean( 20 ) ) {
			$customer_groups = array( 'new_customers', 'vip_customers', 'returning_customers' );
			$rules[]         = array(
				'coupon_id'  => $coupon_id,
				'rule_type'  => 'customer_group',
				'rule_value' => $this->faker->randomElement( $customer_groups ),
				'created_at' => wp_date( 'Y-m-d H:i:s' ),
			);
		}

		foreach ( $rules as $rule ) {
			$this->wpdb->insert( $coupon_rules_table, $rule );
		}
	}

	/**
	 * Get random product categories for coupon restrictions
	 *
	 * Retrieves a random selection of product categories that can be used
	 * for coupon restrictions.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of category IDs.
	 */
	private function get_random_product_categories(): array {
		$categories_table = $this->wpdb->prefix . 'product_categories';

		return $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT id FROM {$categories_table} ORDER BY RAND() LIMIT %d",
				$this->faker->numberBetween( 1, 3 )
			)
		);
	}
}
