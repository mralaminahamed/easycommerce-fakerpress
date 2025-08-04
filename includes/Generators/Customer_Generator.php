<?php
/**
 * Customer Generator
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
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
		$first_name = $this->faker->firstName;
		$last_name  = $this->faker->lastName;
		$email      = $this->faker->unique()->safeEmail;

		$user_data = array(
			'user_login'   => $this->generate_username( $first_name, $last_name ),
			'user_email'   => $email,
			'user_pass'    => wp_generate_password(),
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'display_name' => $first_name . ' ' . $last_name,
			'role'         => 'customer',
		);

		$user_id = wp_insert_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		if ( ! $user_id ) {
			return false;
		}

		try {
			$this->add_customer_meta( $user_id, $first_name, $last_name, $email );

			return array(
				'id'    => $user_id,
				'name'  => $user_data['display_name'],
				'email' => $email,
			);
		} catch ( Exception $e ) {
			// Clean up the created user if meta insertion fails.
			wp_delete_user( $user_id );

			return new WP_Error( 'customer_creation_failed', $e->getMessage() );
		}
	}

	/**
	 * Generate unique username
	 *
	 * Creates a unique username based on first and last name, with fallback
	 * patterns if the initial username already exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $first_name First name.
	 * @param string $last_name  Last name.
	 *
	 * @return string Unique username.
	 */
	private function generate_username( string $first_name, string $last_name ): string {
		$base_username = strtolower( $first_name . $last_name );
		$base_username = sanitize_user( $base_username );

		// If username doesn't exist, use it.
		if ( ! username_exists( $base_username ) ) {
			return $base_username;
		}

		// Try variations with numbers.
		for ( $i = 1; $i <= 99; $i++ ) {
			$username = $base_username . $i;
			if ( ! username_exists( $username ) ) {
				return $username;
			}
		}

		// Fallback to random string.
		return $base_username . wp_rand( 100, 999 );
	}

	/**
	 * Add customer metadata
	 *
	 * Creates comprehensive metadata for the customer including addresses,
	 * preferences, and statistics.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id    User ID.
	 * @param string $first_name First name.
	 * @param string $last_name  Last name.
	 * @param string $email      Email address.
	 *
	 * @return void
	 */
	private function add_customer_meta( int $user_id, string $first_name, string $last_name, string $email ): void {
		// Generate billing address.
		$billing_address = array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'company'    => $this->faker->optional( 0.3 )->company,
			'address_1'  => $this->faker->streetAddress,
			'address_2'  => $this->faker->optional( 0.3 )->secondaryAddress,
			'city'       => $this->faker->city,
			'state'      => $this->faker->stateAbbr,
			'postcode'   => $this->faker->postcode,
			'country'    => 'US',
			'email'      => $email,
			'phone'      => $this->faker->phoneNumber,
		);

		// Generate shipping address (80% chance same as billing).
		$shipping_address = $billing_address;
		if ( $this->faker->boolean( 20 ) ) {
			$shipping_address = array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'company'    => $this->faker->optional( 0.2 )->company,
				'address_1'  => $this->faker->streetAddress,
				'address_2'  => $this->faker->optional( 0.3 )->secondaryAddress,
				'city'       => $this->faker->city,
				'state'      => $this->faker->stateAbbr,
				'postcode'   => $this->faker->postcode,
				'country'    => 'US',
			);
		}

		// Customer preferences.
		$preferences = array(
			'newsletter'          => $this->faker->boolean( 70 ),
			'sms_notifications'   => $this->faker->boolean( 40 ),
			'email_notifications' => $this->faker->boolean( 80 ),
			'preferred_language'  => $this->faker->randomElement( array( 'en', 'es', 'fr' ) ),
			'currency'            => 'USD',
			'timezone'            => $this->faker->timezone,
		);

		// Customer statistics (initial values).
		$join_date = $this->faker->dateTimeBetween( '-2 years', 'now' );

		update_user_meta( $user_id, 'billing_address', $billing_address );
		update_user_meta( $user_id, 'shipping_address', $shipping_address );
		update_user_meta( $user_id, 'customer_preferences', $preferences );
		update_user_meta( $user_id, 'customer_since', $join_date->format( 'Y-m-d H:i:s' ) );
		update_user_meta( $user_id, 'total_orders', 0 );
		update_user_meta( $user_id, 'total_spent', '0.00' );
		update_user_meta( $user_id, 'average_order_value', '0.00' );
		update_user_meta( $user_id, 'loyalty_tier', 'bronze' );
		update_user_meta( $user_id, 'loyalty_points', 0 );
		update_user_meta( $user_id, 'birth_date', $this->faker->optional( 0.6 )->date( 'Y-m-d', '-18 years' ) );
		update_user_meta( $user_id, 'gender', $this->faker->optional( 0.5 )->randomElement( array( 'male', 'female', 'other' ) ) );
		update_user_meta( $user_id, 'phone', $this->faker->phoneNumber );
		update_user_meta( $user_id, 'marketing_opt_in', $this->faker->boolean( 60 ) );
		update_user_meta( $user_id, 'last_login', $this->faker->optional( 0.8 )->dateTimeBetween( '-30 days', 'now' )?->format( 'Y-m-d H:i:s' ) );
	}
}
