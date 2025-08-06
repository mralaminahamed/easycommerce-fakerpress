<?php
/**
 * Customer Generator.
 *
 * @package EasyCommerceFakerPress\Generators
 * @since   1.0.0
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Customer;
use Exception;
use RuntimeException;
use WP_Error;

/**
 * Customer Generator Class
 *
 * Generates realistic fake customer data for EasyCommerce
 *
 * @since 1.0.0
 */
class Customer_Generator extends Generator {

	/**
	 * Get the resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'customer';
	}

	/**
	 * Generate a single customer
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Single customer data, error, or false on failure.
	 */
	protected function generate_single_item() {
		try {
			// Check if EasyCommerce Customer class exists.
			if ( ! class_exists( Customer::class ) ) {
				return new WP_Error( 'missing_model', 'EasyCommerce Customer model not found. Please ensure EasyCommerce plugin is active.' );
			}

			$first_name = $this->faker->firstName;
			$last_name  = $this->faker->lastName;
			$email      = $this->faker->unique()->safeEmail;
			$full_name  = $first_name . ' ' . $last_name;

			// Generate comprehensive customer data.
			$billing_address  = $this->generate_billing_address( $first_name, $last_name, $email );
			$shipping_address = $this->generate_shipping_address( $first_name, $last_name, $billing_address['country'] );
			$customer_meta    = $this->generate_customer_meta();

			// Check if user with this email already exists.
			if ( email_exists( $email ) ) {
				return new WP_Error( 'email_exists', 'A user with this email address already exists.' );
			}

			// Use EasyCommerce Customer model with complete data structure.
			$customer = new Customer();
			$created  = $customer->create(
				array(
					// Required fields.
					'email'      => $email,
					'name'       => $full_name,

					// Optional core fields.
					'first_name' => $first_name,
					'last_name'  => $last_name,
					'role'       => 'customer',
					'username'   => $this->generate_unique_username( $first_name, $last_name ),
					'password'   => wp_generate_password( 16, true, true ),

					// Customer meta data.
					'meta'       => array_merge(
						array(
							'phone'            => $billing_address['phone'],
							'photo'            => $this->generate_customer_photo(),
							'billing_address'  => $billing_address,
							'shipping_address' => ! empty( $shipping_address ) ? $shipping_address : $billing_address,
						),
						$customer_meta
					),
				)
			);

			if ( ! $created ) {
				return new WP_Error( 'customer_creation_failed', 'Failed to create customer using EasyCommerce model.' );
			}

			// Initialize customer statistics based on customer age.
			$this->initialize_customer_history( $customer, $customer_meta );

			return array(
				'id'              => $customer->get_id(),
				'name'            => $full_name,
				'email'           => $email,
				'username'        => $customer->get_meta( 'username' ),
				'phone'           => $billing_address['phone'],
				'billing_city'    => $billing_address['city'],
				'billing_country' => $billing_address['country'],
				'shipping_city'   => ! empty( $shipping_address ) ? $shipping_address['city'] : $billing_address['city'],
				'customer_since'  => $customer_meta['customer_since'],
				'loyalty_tier'    => $customer_meta['loyalty_tier'],
				'total_orders'    => $customer_meta['total_orders'],
				'total_spent'     => '$' . number_format( $customer_meta['total_spent'], 2 ),
				'last_login'      => $customer_meta['last_login'],
			);
		} catch ( Exception $e ) {
			$this->log( 'Customer creation failed: ' . $e->getMessage(), 'error' );
			return new WP_Error( 'customer_creation_failed', $e->getMessage() );
		}
	}

	/**
	 * Generate unique username
	 *
	 * @since 1.0.0
	 *
	 * @param string $first_name First name.
	 * @param string $last_name  Last name.
	 *
	 * @return string Unique username.
	 * @throws RuntimeException If unable to generate a unique username after 10 attempts.
	 */
	private function generate_unique_username( string $first_name, string $last_name ): string {
		$base_username = strtolower( $first_name . '.' . $last_name );
		$username      = sanitize_user( $base_username, true );
		$attempts      = 0;

		while ( username_exists( $username ) && $attempts < 10 ) {
			$username = sanitize_user( $base_username . $this->faker->numberBetween( 1, 999 ), true );
			++$attempts;
		}

		if ( username_exists( $username ) ) {
			throw new RuntimeException( 'Unable to generate unique username after 10 attempts.' );
		}

		return $username;
	}

	/**
	 * Generate customer photo (placeholder)
	 *
	 * @since 1.0.0
	 *
	 * @return string Photo URL or empty string.
	 */
	private function generate_customer_photo(): string {
		// Placeholder for WordPress media library integration.
		return (string) $this->faker->optional( 0.3 )->imageUrl( 200, 200, 'people' );
	}

	/**
	 * Generate comprehensive billing address
	 *
	 * @since 1.0.0
	 *
	 * @param string $first_name First name.
	 * @param string $last_name  Last name.
	 * @param string $email      Email address.
	 *
	 * @return array Billing address data.
	 */
	private function generate_billing_address( string $first_name, string $last_name, string $email ): array {
		$countries = array( 'US', 'CA', 'GB', 'AU', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'JP', 'IN', 'BR', 'MX' );
		$country   = $this->faker->randomElement( $countries );

		return array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'email'      => $email,
			'phone'      => $this->generate_phone_number( $country ),
			'company'    => $this->faker->optional( 0.25 )->company,
			'address_1'  => $this->faker->streetAddress,
			'address_2'  => $this->faker->optional( 0.35 )->secondaryAddress,
			'city'       => $this->faker->city,
			'state'      => $this->generate_state( $country ),
			'country'    => $country,
			'postcode'   => $this->generate_postcode( $country ),
		);
	}

	/**
	 * Generate shipping address
	 *
	 * @since 1.0.0
	 *
	 * @param string $first_name First name.
	 * @param string $last_name  Last name.
	 * @param string $billing_country Billing country code for consistency.
	 *
	 * @return array Shipping address data (empty if same as billing).
	 */
	private function generate_shipping_address( string $first_name, string $last_name, string $billing_country ): array {
		// 70% chance same as billing address (return empty array)
		if ( $this->faker->boolean( 70 ) ) {
			return array();
		}

		// 80% chance shipping address is in the same country
		$country = $this->faker->boolean( 80 ) ? $billing_country : $this->faker->randomElement( array( 'US', 'CA', 'GB', 'AU', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'JP', 'IN', 'BR', 'MX' ) );

		return array(
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'company'      => $this->faker->optional( 0.2 )->company,
			'address_1'    => $this->faker->streetAddress,
			'address_2'    => $this->faker->optional( 0.3 )->secondaryAddress,
			'city'         => $this->faker->city,
			'state'        => $this->generate_state( $country ),
			'country'      => $country,
			'postcode'     => $this->generate_postcode( $country ),
			'instructions' => $this->faker->optional( 0.4 )->sentence( 6, true ),
		);
	}

	/**
	 * Generate customer metadata with realistic patterns
	 *
	 * @since 1.0.0
	 *
	 * @return array Customer metadata.
	 */
	private function generate_customer_meta(): array {
		$join_date  = $this->faker->dateTimeBetween( '-5 years', '-1 week' );
		$last_login = $this->faker->optional( 0.85 )->dateTimeBetween( $join_date, 'now' );

		// Generate realistic customer history based on how long they've been a customer.
		$customer_age_days = $join_date->diff( new \DateTime() )->days;
		$customer_history  = $this->generate_realistic_customer_history( $customer_age_days );

		return array_merge(
			array(
				// Customer preferences.
				'customer_preferences' => array(
					'newsletter'           => $this->faker->boolean( 70 ),
					'sms_notifications'    => $this->faker->boolean( 40 ),
					'email_notifications'  => $this->faker->boolean( 85 ),
					'marketing_opt_in'     => $this->faker->boolean( 60 ),
					'preferred_language'   => $this->faker->randomElement( array( 'en_US', 'es_ES', 'fr_FR', 'de_DE', 'it_IT', 'ja_JP', 'pt_BR', 'hi_IN' ) ),
					'currency'             => $this->faker->randomElement( array( 'USD', 'CAD', 'GBP', 'AUD', 'EUR', 'JPY', 'INR', 'BRL', 'MXN' ) ),
					'timezone'             => $this->faker->timezone,
					'communication_method' => $this->faker->randomElement( array( 'email', 'sms', 'both', 'none' ) ),
					'preferred_categories' => $this->faker->randomElements( array( 'Electronics', 'Fashion', 'Books', 'Home', 'Sports', 'Beauty' ), $this->faker->numberBetween( 1, 3 ) ),
				),

				// Customer statistics.
				'customer_since'       => $join_date->format( 'Y-m-d H:i:s' ),
				'last_login'           => null !== $last_login ? $last_login->format( 'Y-m-d H:i:s' ) : null,

				// Loyalty and engagement.
				'referral_code'        => strtoupper( $this->faker->lexify( '????' ) . $this->faker->numerify( '###' ) ),
				'referred_by'          => $this->faker->optional( 0.2 )->randomNumber( 5 ), // Customer ID who referred.
				'referral_count'       => $this->faker->numberBetween( 0, 5 ),

				// Personal information.
				'birth_date'           => $this->faker->optional( 0.65 )->date( 'Y-m-d', '-18 years' ),
				'gender'               => $this->faker->optional( 0.55 )->randomElement( array( 'male', 'female', 'non_binary', 'prefer_not_to_say' ) ),
				'occupation'           => $this->faker->optional( 0.45 )->jobTitle,
				'marital_status'       => $this->faker->optional( 0.4 )->randomElement( array( 'single', 'married', 'divorced', 'widowed' ) ),

				// Marketing and engagement.
				'source'               => $this->faker->randomElement( array( 'organic', 'google_ads', 'facebook_ads', 'instagram', 'referral', 'email_campaign', 'direct', 'affiliate' ) ),
				'utm_campaign'         => $this->faker->optional( 0.35 )->words( 3, true ),
				'utm_source'           => $this->faker->optional( 0.35 )->randomElement( array( 'google', 'facebook', 'twitter', 'linkedin', 'email' ) ),
				'utm_medium'           => $this->faker->optional( 0.35 )->randomElement( array( 'cpc', 'social', 'email', 'referral', 'organic' ) ),
				'tags'                 => $this->generate_customer_tags(),

				// Customer service.
				'notes'                => $this->faker->optional( 0.25 )->paragraph( 3, true ),
				'vip_status'           => $this->faker->boolean( 8 ), // 8% VIP customers
				'account_status'       => $this->faker->randomElement( array( 'active', 'inactive', 'pending' ) ),
				'email_verified'       => $this->faker->boolean( 90 ),
				'phone_verified'       => $this->faker->boolean( 65 ),
			),
			$customer_history
		);
	}

	/**
	 * Generate customer tags for segmentation
	 *
	 * @since 1.0.0
	 *
	 * @return array Customer tags.
	 */
	private function generate_customer_tags(): array {
		$available_tags = array(
			'high_value_customer',
			'frequent_shopper',
			'bargain_seeker',
			'early_adopter',
			'loyal_customer',
			'gift_shopper',
			'bulk_purchaser',
			'international_customer',
			'mobile_shopper',
			'newsletter_subscriber',
			'social_media_engaged',
			'product_reviewer',
			'referrer',
			'seasonal_shopper',
			'cart_abandoner',
			'new_customer',
			'returning_customer',
		);

		return $this->faker->randomElements( $available_tags, $this->faker->numberBetween( 1, 5 ) );
	}

	/**
	 * Generate phone number based on country
	 *
	 * @since 1.0.0
	 *
	 * @param string $country Country code.
	 *
	 * @return string Phone number.
	 */
	private function generate_phone_number( string $country ): string {
		$patterns = array(
			'US' => '+1-###-###-####',
			'CA' => '+1-###-###-####',
			'GB' => '+44-####-######',
			'AU' => '+61-#-####-####',
			'DE' => '+49-###-#######',
			'FR' => '+33-#-##-##-##-##',
			'IT' => '+39-###-###-####',
			'ES' => '+34-###-###-###',
			'NL' => '+31-##-###-####',
			'BE' => '+32-###-###-###',
			'JP' => '+81-##-####-####',
			'IN' => '+91-####-######',
			'BR' => '+55-##-#####-####',
			'MX' => '+52-###-###-####',
		);

		$pattern = $patterns[ $country ] ?? '+1-###-###-####';
		return $this->faker->numerify( $pattern );
	}

	/**
	 * Generate state/province based on country
	 *
	 * @since 1.0.0
	 *
	 * @param string $country Country code.
	 *
	 * @return string State/province.
	 */
	private function generate_state( string $country ): string {
		switch ( $country ) {
			case 'US':
				return $this->faker->stateAbbr;
			case 'CA':
				$provinces = array( 'AB', 'BC', 'MB', 'NB', 'NL', 'NS', 'NT', 'NU', 'ON', 'PE', 'QC', 'SK', 'YT' );
				return $this->faker->randomElement( $provinces );
			case 'AU':
				$states = array( 'NSW', 'VIC', 'QLD', 'WA', 'SA', 'TAS', 'ACT', 'NT' );
				return $this->faker->randomElement( $states );
			case 'GB':
				$counties = array( 'Greater London', 'Manchester', 'West Midlands', 'West Yorkshire', 'Glasgow', 'Merseyside', 'South Yorkshire', 'Hampshire' );
				return $this->faker->randomElement( $counties );
			case 'JP':
				$prefectures = array( 'Tokyo', 'Osaka', 'Kyoto', 'Hokkaido', 'Aichi', 'Fukuoka', 'Kanagawa', 'Saitama' );
				return $this->faker->randomElement( $prefectures );
			case 'IN':
				$states = array( 'Maharashtra', 'Delhi', 'Karnataka', 'Tamil Nadu', 'Gujarat', 'West Bengal', 'Rajasthan', 'Uttar Pradesh' );
				return $this->faker->randomElement( $states );
			case 'BR':
				$states = array( 'SP', 'RJ', 'MG', 'RS', 'BA', 'PE', 'CE', 'PR' );
				return $this->faker->randomElement( $states );
			case 'MX':
				$states = array( 'CDMX', 'Jalisco', 'Nuevo León', 'Puebla', 'Guanajuato', 'Veracruz', 'Yucatán', 'Chihuahua' );
				return $this->faker->randomElement( $states );
			default:
				return $this->faker->state;
		}
	}

	/**
	 * Generate postcode based on country
	 *
	 * @since 1.0.0
	 *
	 * @param string $country Country code.
	 *
	 * @return string Postcode.
	 */
	private function generate_postcode( string $country ): string {
		$patterns = array(
			'US' => '#####',
			'CA' => '?#? #?#',
			'GB' => '??# #??',
			'AU' => '####',
			'DE' => '#####',
			'FR' => '#####',
			'IT' => '#####',
			'ES' => '#####',
			'NL' => '#### ??',
			'BE' => '####',
			'JP' => '###-####',
			'IN' => '######',
			'BR' => '#####-###',
			'MX' => '#####',
		);

		$pattern = $patterns[ $country ] ?? '#####';

		return $this->faker->bothify( $pattern );
	}

	/**
	 * Generate realistic customer history based on customer age
	 *
	 * @since 1.0.0
	 *
	 * @param int $customer_age_days Number of days since customer joined.
	 *
	 * @return array Customer history data.
	 */
	private function generate_realistic_customer_history( int $customer_age_days ): array {
		// Base probability of having made purchases increases with customer age.
		$purchase_probability = min( 0.95, $customer_age_days / 365 * 0.4 + 0.1 );

		// Determine if customer has made purchases.
		$has_purchases = $this->faker->boolean( $purchase_probability * 100 );

		if ( ! $has_purchases ) {
			return array(
				'total_orders'        => 0,
				'total_spent'         => 0.00,
				'average_order_value' => 0.00,
				'last_order_date'     => null,
				'loyalty_tier'        => 'bronze',
				'loyalty_points'      => 0,
				'cart_abandonments'   => $this->faker->numberBetween( 0, 3 ),
				'coupon_usage'        => 0,
			);
		}

		// Generate realistic purchase history.
		$months_active        = max( 1, $customer_age_days / 30 );
		$avg_orders_per_month = $this->faker->randomFloat( 2, 0.05, 3.0 );
		$total_orders         = max( 1, round( $months_active * $avg_orders_per_month ) );

		// Generate realistic spending patterns.
		$avg_order_value = $this->faker->randomFloat( 2, 30, 500 );
		$total_spent     = $total_orders * $avg_order_value;

		// Add variance for realism.
		$total_spent *= $this->faker->randomFloat( 2, 0.8, 1.5 );

		// Determine loyalty tier.
		$loyalty_tier = $this->determine_loyalty_tier( $total_spent );

		// Calculate loyalty points (1 point per $1, with tier bonuses).
		$base_points     = floor( $total_spent );
		$tier_multiplier = array(
			'bronze'   => 1.0,
			'silver'   => 1.2,
			'gold'     => 1.5,
			'platinum' => 2.0,
		);
		$loyalty_points  = floor( $base_points * $tier_multiplier[ $loyalty_tier ] );

		// Determine last order date.
		$last_order_days_ago = $this->faker->numberBetween( 1, min( 365, $customer_age_days ) );
		$last_order_date     = $this->faker->dateTimeBetween( "-{$last_order_days_ago} days", 'now' );

		// Generate additional engagement metrics.
		$cart_abandonments = $this->faker->numberBetween( 0, ceil( $total_orders / 2 ) );
		$coupon_usage      = $this->faker->numberBetween( 0, ceil( $total_orders / 3 ) );

		return array(
			'total_orders'        => $total_orders,
			'total_spent'         => $total_spent,
			'average_order_value' => $total_spent / $total_orders,
			'last_order_date'     => $last_order_date->format( 'Y-m-d H:i:s' ),
			'loyalty_tier'        => $loyalty_tier,
			'loyalty_points'      => $loyalty_points,
			'cart_abandonments'   => $cart_abandonments,
			'coupon_usage'        => $coupon_usage,
		);
	}

	/**
	 * Determine loyalty tier based on total spent
	 *
	 * @since 1.0.0
	 *
	 * @param float $total_spent Total amount spent by customer.
	 *
	 * @return string Loyalty tier.
	 */
	private function determine_loyalty_tier( float $total_spent ): string {
		if ( $total_spent >= 7500 ) {
			return 'platinum';
		}

		if ( $total_spent >= 3000 ) {
			return 'gold';
		}

		if ( $total_spent >= 1000 ) {
			return 'silver';
		}

		return 'bronze';
	}

	/**
	 * Initialize customer statistics in WordPress user meta
	 *
	 * @since 1.0.0
	 *
	 * @param Customer $customer      Customer object.
	 * @param array    $customer_meta Customer metadata.
	 *
	 * @return void
	 */
	private function initialize_customer_history( Customer $customer, array $customer_meta ): void {
		$customer->update_meta( 'total_orders', $customer_meta['total_orders'] );
		$customer->update_meta( 'total_spent', number_format( $customer_meta['total_spent'], 2, '.', '' ) );
		$customer->update_meta( 'average_order_value', number_format( $customer_meta['average_order_value'], 2, '.', '' ) );
		$customer->update_meta( 'last_order_date', $customer_meta['last_order_date'] );
		$customer->update_meta( 'loyalty_tier', $customer_meta['loyalty_tier'] );
		$customer->update_meta( 'loyalty_points', $customer_meta['loyalty_points'] );
		$customer->update_meta( 'cart_abandonments', $customer_meta['cart_abandonments'] );
		$customer->update_meta( 'coupon_usage', $customer_meta['coupon_usage'] );
		$customer->update_meta( 'first_name', $customer->get_meta( 'first_name' ) );
		$customer->update_meta( 'last_name', $customer->get_meta( 'last_name' ) );
		$customer->update_meta( 'username', $customer->get_meta( 'username' ) );
	}
}
