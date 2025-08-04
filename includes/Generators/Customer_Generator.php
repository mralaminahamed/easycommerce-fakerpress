<?php
/**
 * Customer Generator
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Customer;
use Exception;
use WP_Error;

/**
 * Customer Generator Class
 *
 * Generates fake customer data for EasyCommerce
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
	 * @return array|WP_Error|false Single customer data, error, or false on failure.
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

			// Generate comprehensive customer data
			$billing_address  = $this->generate_billing_address( $first_name, $last_name, $email );
			$shipping_address = $this->generate_shipping_address( $first_name, $last_name );
			$customer_meta    = $this->generate_customer_meta();

			// Use EasyCommerce Customer model with complete data structure
			$customer = new Customer();
			$created  = $customer->create( array(
				// Required fields
				'email'      => $email,
				'name'       => $full_name,
				
				// Optional core fields
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'role'       => 'customer',
				'password'   => wp_generate_password( 12, true, true ),
				
				// Customer meta data
				'meta'       => array_merge(
					array(
						'phone'            => $this->faker->phoneNumber,
						'photo'            => '', // Could integrate with media library
						'billing_address'  => $billing_address,
						'shipping_address' => $shipping_address,
					),
					$customer_meta
				),
			) );

			if ( ! $created ) {
				return new WP_Error( 'customer_creation_failed', 'Failed to create customer using EasyCommerce model.' );
			}

			return array(
				'id'             => $customer->get_id(),
				'name'           => $full_name,
				'email'          => $email,
				'phone'          => $billing_address['phone'],
				'billing_city'   => $billing_address['city'],
				'shipping_city'  => ! empty( $shipping_address ) ? $shipping_address['city'] : $billing_address['city'],
				'customer_since' => $customer_meta['customer_since'],
			);
		} catch ( Exception $e ) {
			$this->log( 'Customer creation failed: ' . $e->getMessage(), 'error' );
			
			return new WP_Error( 'customer_creation_failed', $e->getMessage() );
		}
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
		$countries = array( 'US', 'CA', 'GB', 'AU', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE' );
		$country   = $this->faker->randomElement( $countries );
		
		return array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'email'      => $email,
			'phone'      => $this->generate_phone_number( $country ),
			'company'    => $this->faker->optional( 0.3 )->company,
			'address_1'  => $this->faker->streetAddress,
			'address_2'  => $this->faker->optional( 0.3 )->secondaryAddress,
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
	 *
	 * @return array Shipping address data (empty if same as billing).
	 */
	private function generate_shipping_address( string $first_name, string $last_name ): array {
		// 75% chance same as billing address (return empty array)
		if ( $this->faker->boolean( 75 ) ) {
			return array();
		}

		// Generate different shipping address
		$countries = array( 'US', 'CA', 'GB', 'AU', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE' );
		$country   = $this->faker->randomElement( $countries );
		
		return array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'company'    => $this->faker->optional( 0.2 )->company,
			'address_1'  => $this->faker->streetAddress,
			'address_2'  => $this->faker->optional( 0.3 )->secondaryAddress,
			'city'       => $this->faker->city,
			'state'      => $this->generate_state( $country ),
			'country'    => $country,
			'postcode'   => $this->generate_postcode( $country ),
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
		$join_date = $this->faker->dateTimeBetween( '-3 years', '-1 month' );
		$last_login = $this->faker->optional( 0.8 )->dateTimeBetween( $join_date, 'now' );
		
		return array(
			// Customer preferences
			'customer_preferences' => array(
				'newsletter'             => $this->faker->boolean( 65 ),
				'sms_notifications'      => $this->faker->boolean( 35 ),
				'email_notifications'    => $this->faker->boolean( 80 ),
				'marketing_opt_in'       => $this->faker->boolean( 55 ),
				'preferred_language'     => $this->faker->randomElement( array( 'en', 'es', 'fr', 'de', 'it' ) ),
				'currency'               => 'USD',
				'timezone'               => $this->faker->timezone,
				'communication_method'   => $this->faker->randomElement( array( 'email', 'sms', 'both' ) ),
			),
			
			// Customer statistics (initial values - will be updated by orders)
			'customer_since'         => $join_date->format( 'Y-m-d H:i:s' ),
			'total_orders'           => 0,
			'total_spent'            => '0.00',
			'average_order_value'    => '0.00',
			'last_order_date'        => null,
			'last_login'             => $last_login ? $last_login->format( 'Y-m-d H:i:s' ) : null,
			
			// Loyalty and engagement
			'loyalty_tier'           => 'bronze',
			'loyalty_points'         => 0,
			'referral_code'          => strtoupper( $this->faker->lexify( '????' ) . $this->faker->numerify( '##' ) ),
			'referred_by'            => $this->faker->optional( 0.15 )->randomNumber( 4 ), // Customer ID who referred
			
			// Personal information (optional)
			'birth_date'             => $this->faker->optional( 0.6 )->date( 'Y-m-d', '-18 years' ),
			'gender'                 => $this->faker->optional( 0.5 )->randomElement( array( 'male', 'female', 'other', 'prefer_not_to_say' ) ),
			'occupation'             => $this->faker->optional( 0.4 )->jobTitle,
			
			// Marketing and engagement
			'source'                 => $this->faker->randomElement( array( 'organic', 'google_ads', 'facebook', 'referral', 'email', 'direct' ) ),
			'utm_campaign'           => $this->faker->optional( 0.3 )->words( 2, true ),
			'tags'                   => $this->generate_customer_tags(),
			
			// Customer service
			'notes'                  => $this->faker->optional( 0.2 )->paragraph( 2 ),
			'vip_status'             => $this->faker->boolean( 5 ), // 5% VIP customers
			'account_status'         => 'active',
			'email_verified'         => $this->faker->boolean( 85 ),
			'phone_verified'         => $this->faker->boolean( 60 ),
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
			'high_value', 'frequent_buyer', 'bargain_hunter', 'early_adopter',
			'loyal_customer', 'gift_buyer', 'bulk_buyer', 'international',
			'mobile_user', 'newsletter_subscriber', 'social_media_follower',
			'review_writer', 'referrer', 'seasonal_buyer', 'cart_abandoner'
		);
		
		return $this->faker->randomElements( $available_tags, $this->faker->numberBetween( 0, 4 ) );
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
			'GB' => '+44-##-####-####',
			'AU' => '+61-#-####-####',
			'DE' => '+49-###-###-####',
			'FR' => '+33-#-##-##-##-##',
			'IT' => '+39-###-###-####',
			'ES' => '+34-###-###-###',
			'NL' => '+31-##-###-####',
			'BE' => '+32-##-###-####',
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
				$counties = array( 'London', 'Manchester', 'Birmingham', 'Leeds', 'Glasgow', 'Liverpool', 'Edinburgh', 'Bristol' );
				return $this->faker->randomElement( $counties );
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
			'GB' => '?? ##??',
			'AU' => '####',
			'DE' => '#####',
			'FR' => '#####',
			'IT' => '#####',
			'ES' => '#####',
			'NL' => '#### ??',
			'BE' => '####',
		);
		
		$pattern = $patterns[ $country ] ?? '#####';
		
		return $this->faker->bothify( $pattern );
	}
}