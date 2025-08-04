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
		$first_name = $this->faker->firstName;
		$last_name  = $this->faker->lastName;
		$email      = $this->faker->unique()->safeEmail;
		$full_name  = $first_name . ' ' . $last_name;

		// Generate billing and shipping addresses
		$billing_address  = $this->generate_billing_address( $first_name, $last_name, $email );
		$shipping_address = $this->generate_shipping_address( $first_name, $last_name );

		// Generate customer metadata
		$customer_meta = $this->generate_customer_meta();

		try {
			$customer = new Customer();
			$created  = $customer->create( array(
				'email'      => $email,
				'name'       => $full_name,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'role'       => 'customer',
				'password'   => wp_generate_password(),
				'meta'       => array_merge(
					array(
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
				'id'    => $customer->get_id(),
				'name'  => $full_name,
				'email' => $email,
			);
		} catch ( Exception $e ) {
			return new WP_Error( 'customer_creation_failed', $e->getMessage() );
		}
	}

	/**
	 * Generate billing address
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
		return array(
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
	}

	/**
	 * Generate shipping address
	 *
	 * @since 1.0.0
	 *
	 * @param string $first_name First name.
	 * @param string $last_name  Last name.
	 *
	 * @return array Shipping address data.
	 */
	private function generate_shipping_address( string $first_name, string $last_name ): array {
		// 80% chance same as billing, 20% chance different address
		if ( $this->faker->boolean( 80 ) ) {
			return array(); // Empty means use billing address
		}

		return array(
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

	/**
	 * Generate customer metadata
	 *
	 * @since 1.0.0
	 *
	 * @return array Customer metadata.
	 */
	private function generate_customer_meta(): array {
		$join_date = $this->faker->dateTimeBetween( '-2 years', 'now' );

		return array(
			'customer_preferences' => array(
				'newsletter'          => $this->faker->boolean( 70 ),
				'sms_notifications'   => $this->faker->boolean( 40 ),
				'email_notifications' => $this->faker->boolean( 80 ),
				'preferred_language'  => $this->faker->randomElement( array( 'en', 'es', 'fr' ) ),
				'currency'            => 'USD',
				'timezone'            => $this->faker->timezone,
			),
			'customer_since'       => $join_date->format( 'Y-m-d H:i:s' ),
			'total_orders'         => 0,
			'total_spent'          => '0.00',
			'average_order_value'  => '0.00',
			'loyalty_tier'         => 'bronze',
			'loyalty_points'       => 0,
			'birth_date'           => $this->faker->optional( 0.6 )->date( 'Y-m-d', '-18 years' ),
			'gender'               => $this->faker->optional( 0.5 )->randomElement( array( 'male', 'female', 'other' ) ),
			'phone'                => $this->faker->phoneNumber,
			'marketing_opt_in'     => $this->faker->boolean( 60 ),
			'last_login'           => $this->faker->optional( 0.8 )->dateTimeBetween( '-30 days', 'now' )?->format( 'Y-m-d H:i:s' ),
		);
	}
}
