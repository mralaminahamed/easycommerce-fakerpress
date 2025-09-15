<?php
/**
 * Order Generator.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Customer;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Location;
use Exception;
use WP_Error;
use WP_User;

/**
 * Order Generator Class
 *
 * Generates fake order data for EasyCommerce
 *
 * @since 1.0.0
 */
class Order_Generator extends Generator {

	/**
	 * Generation parameters from REST API
	 *
	 * @var array
	 */
	private array $generation_params = array();

	/**
	 * Set generation parameters
	 *
	 * @since 1.0.0
	 *
	 * @param array $params Generation parameters.
	 *
	 * @return void
	 */
	public function set_generation_params( array $params ): void {
		$this->generation_params = $params;
	}

	/**
	 * Get the resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'order';
	}

	/**
	 * Generate a single order
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Single order data, error, or false on failure.
	 */
	protected function generate_single_item() {
		try {
			// Check if EasyCommerce Order class exists.
			if ( ! class_exists( Order::class ) ) {
				return new WP_Error( 'missing_model', 'EasyCommerce Order model not found. Please ensure EasyCommerce plugin is active.' );
			}

			$customer   = $this->get_customer_for_order();
			$variations = $this->get_random_product_variations();

			if ( ! $customer ) {
				return new WP_Error( 'no_customers', 'No customers found for order generation. Please create customers first.' );
			}

			if ( empty( $variations ) ) {
				return new WP_Error( 'no_variations', 'No product variations found for order generation. Please create products with variations first.' );
			}

			// Convert variations to order items format required by EasyCommerce.
			$order_items = $this->convert_variations_to_items( $variations );
			$subtotal    = $this->calculate_subtotal( $order_items );
			$order_meta  = $this->generate_order_meta( $customer, $subtotal );
			$total       = $this->calculate_total( $subtotal, $order_meta );

			// Use EasyCommerce Order model with complete data structure.
			$order   = new Order();
			$created = $order->create(
				array(
					// Required fields.
					'customer_id'    => $customer['id'],
					'total'          => $total,

					// Optional core fields.
					'status'         => $this->generate_order_status(),
					'fulfill_status' => $this->generate_fulfillment_status(),
					'payment_method' => $this->generate_payment_method(),

					// Order items (required).
					'items'          => $order_items,

					// Order metadata.
					'meta'           => $order_meta,
				)
			);

			if ( ! $created ) {
				return new WP_Error( 'order_creation_failed', 'Failed to create order using EasyCommerce model.' );
			}

			// Update customer statistics.
			$this->update_customer_stats( $customer['id'], $total );

			return array(
				'id'             => $order->get_id(),
				'customer'       => $customer['name'],
				'customer_email' => $customer['email'],
				'total'          => '$' . number_format( $total, 2 ),
				'status'         => $order->get_status(),
				'payment_method' => $order_meta['payment_details']['method'],
				'items_count'    => $this->count_order_items( $order_items ),
				'created_date'   => current_time( 'Y-m-d H:i:s' ),
			);
		} catch ( Exception $e ) {
			$this->log( 'Order creation failed: ' . $e->getMessage(), 'error' );

			return new WP_Error( 'order_creation_failed', $e->getMessage() );
		}
	}

	/**
	 * Get customer for order based on generation parameters
	 *
	 * @since 1.0.0
	 *
	 * @return array|false Customer data or false if none found.
	 */
	private function get_customer_for_order() {
		$customer_type        = $this->generation_params['customer_type'] ?? 'mixed';
		$specific_customer_id = $this->generation_params['specific_customer_id'] ?? null;

		switch ( $customer_type ) {
			case 'existing':
				return $this->get_random_customer();

			case 'new':
				return $this->create_new_customer();

			case 'specific':
				if ( $specific_customer_id ) {
					return $this->get_specific_customer( $specific_customer_id );
				}

				// Fallback to random if no specific ID provided.
				return $this->get_random_customer();

			case 'mixed':
			default:
				// 70% existing customers, 30% new customers for realistic distribution.
				return $this->faker->boolean( 70 ) ? $this->get_random_customer() : $this->create_new_customer();
		}
	}

	/**
	 * Get a random customer for order generation using EasyCommerce Customer model
	 *
	 * @since 1.0.0
	 *
	 * @return array|false Random customer user object or false if none found.
	 */
	private function get_random_customer() {
		// Use EasyCommerce Customer model's customer_list method to get customers with proper capabilities.
		$customer_data = Customer::customer_list( null, 1, 50 );
		$customers     = $customer_data['users'] ?? array();

		if ( empty( $customers ) ) {
			return false;
		}

		// Use faker to randomly select from available customers.
		return $this->faker->randomElement( $customers );
	}

	/**
	 * Get a specific customer by ID
	 *
	 * @since 1.0.0
	 *
	 * @param int $customer_id Customer ID.
	 *
	 * @return array|false Customer data or false if not found.
	 */
	private function get_specific_customer( int $customer_id ) {
		try {
			$customer = new Customer( $customer_id );
			if ( $customer->exists() ) {
				return array(
					'id'         => $customer->get_id(),
					'name'       => $customer->get_name(),
					'email'      => $customer->get_email(),
					'first_name' => $customer->get_first_name(),
					'last_name'  => $customer->get_last_name(),
					'role'       => $customer->get_role(),
				);
			}
		} catch ( Exception $e ) {
			$this->log( 'Failed to get specific customer: ' . $e->getMessage(), 'warning' );
		}

		return false;
	}

	/**
	 * Create a new customer for order generation
	 *
	 * @since 1.0.0
	 *
	 * @return array|false New customer data or false on failure.
	 */
	private function create_new_customer() {
		try {
			// Use Customer_Generator to create a new customer.
			$customer_generator = new Customer_Generator();
			$result             = $customer_generator->generate_single_item();

			if ( is_wp_error( $result ) || ! $result ) {
				return false;
			}

			// Return customer in expected format.
			return array(
				'id'         => $result['id'],
				'name'       => $result['name'],
				'email'      => $result['email'],
				'first_name' => $result['first_name'] ?? '',
				'last_name'  => $result['last_name'] ?? '',
				'role'       => 'customer',
			);
		} catch ( Exception $e ) {
			$this->log( 'Failed to create new customer: ' . $e->getMessage(), 'warning' );

			return false;
		}
	}

	/**
	 * Get random product variations for order items using EasyCommerce models
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of Product_Variation objects.
	 */
	private function get_random_product_variations(): array {
		// Get a larger pool of available variations to choose from.
		$db              = new Database( 'product_variations' );
		$variations_data = $db->get_rows(
			array( 'status' => 'in_stock' ),
			100, // Get more variations for better randomization.
			0,
			'RAND()'
		);

		if ( empty( $variations_data ) ) {
			return array();
		}

		// Use faker to randomly select variations (1-5 items per order).
		$selected_count           = $this->faker->numberBetween( 1, 5 );
		$selected_variations_data = $this->faker->randomElements(
			$variations_data,
			min( $selected_count, count( $variations_data ) )
		);

		$variations = array();
		foreach ( $selected_variations_data as $variation_data ) {
			$variation = new Product_Variation( $variation_data->id );
			if ( $variation->exists() ) {
				$variations[] = $variation;
			}
		}

		return $variations;
	}

	/**
	 * Convert product variations to order items format for EasyCommerce
	 *
	 * @since 1.0.0
	 *
	 * @param array $variations Array of Product_Variation objects.
	 *
	 * @return array Order items formatted for EasyCommerce Order model.
	 */
	private function convert_variations_to_items( array $variations ): array {
		$order_items = array();

		foreach ( $variations as $variation ) {
			if ( ! $variation->exists() ) {
				continue;
			}

			$product_id = $variation->get_product_id();
			$price_id   = $variation->get_price_id();
			$quantity   = $this->faker->numberBetween( 1, 3 );
			$rate       = $variation->get_regular_price();
			$subtotal   = $rate * $quantity;

			if ( ! isset( $order_items[ $product_id ] ) ) {
				$order_items[ $product_id ] = array();
			}

			// Enhanced metadata for order items.
			$item_meta = $this->generate_order_item_meta( $variation, $quantity, $rate, $subtotal );

			$order_items[ $product_id ][ $price_id ] = array(
				'quantity'     => $quantity,
				'rate'         => $rate,
				'price'        => $subtotal,
				'tax_class_id' => $variation->get_tax_class(),
				'tax_rate'     => $this->generate_item_tax_rate(),
				'subtotal'     => $subtotal,
				'meta'         => $item_meta,
			);
		}

		return $order_items;
	}

	/**
	 * Calculate order subtotal from items
	 *
	 * @since 1.0.0
	 *
	 * @param array $order_items Order items array.
	 *
	 * @return float Subtotal amount.
	 */
	private function calculate_subtotal( array $order_items ): float {
		$subtotal = 0;

		foreach ( $order_items as $product_id => $variations ) {
			foreach ( $variations as $price_id => $item ) {
				$subtotal += $item['price']; // Use the pre-calculated price.
			}
		}

		return $subtotal;
	}

	/**
	 * Generate comprehensive order metadata
	 *
	 * @since 1.0.0
	 *
	 * @param float $subtotal Order subtotal.
	 *
	 * @param array $customer Customer user object.
	 *
	 * @return array Order metadata.
	 */
	private function generate_order_meta( array $customer, float $subtotal ): array {
		$customer_model   = new Customer( $customer['id'] );
		$billing_address  = $customer_model->get_billing_address() ? $customer_model->get_billing_address() : $this->generate_fallback_address( $customer );
		$shipping_address = $customer_model->get_shipping_address() ? $customer_model->get_shipping_address() : $billing_address;

		return array(
			'addresses'        => array(
				'billing'  => $billing_address,
				'shipping' => $shipping_address,
			),
			'payment_details'  => $this->generate_payment_details(),
			'shipping_details' => $this->generate_shipping_details( $subtotal ),
			'tax_details'      => $this->generate_tax_details( $subtotal ),
			'coupon_details'   => $this->generate_coupon_details( $subtotal ),
			'order_notes'      => $this->faker->optional( 0.3 )->paragraph( 2 ),
			'source_info'      => $this->generate_source_info(),
			'fulfillment'      => array(
				'status'             => $this->generate_fulfillment_status(),
				'tracking_number'    => $this->faker->optional( 0.6 )->regexify( '[A-Z]{2}[0-9]{10}' ),
				'estimated_delivery' => $this->faker->dateTimeBetween( 'now', '+2 weeks' )->format( 'Y-m-d' ),
				'carrier'            => $this->faker->randomElement(
					array(
						'UPS',
						'FedEx',
						'USPS',
						'DHL',
						'Local Delivery',
					)
				),
			),
		);
	}

	/**
	 * Generate realistic order status
	 *
	 * @since 1.0.0
	 *
	 * @return string Order status.
	 */
	private function generate_order_status(): string {
		$statuses = array(
			'pending'    => 25,  // 25% chance
			'processing' => 35,  // 35% chance
			'completed'  => 30,  // 30% chance
			'cancelled'  => 5,   // 5% chance
			'on_hold'    => 3,   // 3% chance
			'refunded'   => 2,   // 2% chance
		);

		return $this->faker->randomElement(
			array_merge(
				...array_map(
					fn( $status, $weight ) => array_fill( 0, $weight, $status ),
					array_keys( $statuses ),
					$statuses
				)
			)
		);
	}

	/**
	 * Generate realistic payment method
	 *
	 * @since 1.0.0
	 *
	 * @return string Payment method.
	 */
	private function generate_payment_method(): string {
		$methods = array(
			'stripe'           => 40,  // 40% chance
			'paypal'           => 25,  // 25% chance
			'bank_transfer'    => 15,  // 15% chance
			'cash_on_delivery' => 10,  // 10% chance
			'credit_card'      => 10,  // 10% chance
		);

		return $this->faker->randomElement(
			array_merge(
				...array_map(
					fn( $method, $weight ) => array_fill( 0, $weight, $method ),
					array_keys( $methods ),
					$methods
				)
			)
		);
	}

	/**
	 * Generate payment details
	 *
	 * @since 1.0.0
	 *
	 * @return array Payment details.
	 */
	private function generate_payment_details(): array {
		$payment_method = $this->generate_payment_method();

		$details = array(
			'method'           => $payment_method,
			'status'           => $this->faker->randomElement( array( 'completed', 'pending', 'failed', 'refunded' ) ),
			'transaction_id'   => $this->faker->uuid,
			'payment_date'     => $this->faker->dateTimeBetween( '-30 days', 'now' )->format( 'Y-m-d H:i:s' ),
			'gateway_response' => $this->faker->sentence(),
		);

		// Add method-specific details.
		switch ( $payment_method ) {
			case 'stripe':
				$details['stripe_charge_id'] = 'ch_' . $this->faker->regexify( '[a-zA-Z0-9]{24}' );
				$details['last4']            = $this->faker->numerify( '####' );
				$details['brand']            = $this->faker->randomElement(
					array(
						'visa',
						'mastercard',
						'amex',
						'discover',
					)
				);
				break;
			case 'paypal':
				$details['paypal_transaction_id'] = $this->faker->regexify( '[A-Z0-9]{15}' );
				$details['payer_email']           = $this->faker->email;
				break;
			case 'bank_transfer':
				$details['bank_reference'] = $this->faker->regexify( '[A-Z0-9]{10}' );
				break;
		}

		return $details;
	}

	/**
	 * Generate shipping details
	 *
	 * @since 1.0.0
	 *
	 * @param float $subtotal Order subtotal.
	 *
	 * @return array Shipping details.
	 */
	private function generate_shipping_details( float $subtotal ): array {
		$shipping_methods = array(
			'standard'  => array(
				'min'    => 5.99,
				'max'    => 12.99,
				'days'   => '5-7',
				'weight' => 50,
			),
			'express'   => array(
				'min'    => 15.99,
				'max'    => 25.99,
				'days'   => '2-3',
				'weight' => 30,
			),
			'overnight' => array(
				'min'    => 25.99,
				'max'    => 45.99,
				'days'   => '1',
				'weight' => 15,
			),
			'pickup'    => array(
				'min'    => 0,
				'max'    => 0,
				'days'   => '0',
				'weight' => 5,
			),
		);

		$method = $this->faker->randomElement(
			array_merge(
				...array_map(
					fn( $method, $details ) => array_fill( 0, $details['weight'], $method ),
					array_keys( $shipping_methods ),
					$shipping_methods
				)
			)
		);

		$method_details = $shipping_methods[ $method ];
		$cost           = $method_details['min'] === $method_details['max']
			? $method_details['min']
			: $this->faker->randomFloat( 2, $method_details['min'], $method_details['max'] );

		// Free shipping for orders over $100.
		if ( 100 < $subtotal && 'overnight' !== $method ) {
			$cost = 0;
		}

		return array(
			'method'             => $method,
			'cost'               => $cost,
			'estimated_days'     => $method_details['days'],
			'insurance'          => $this->faker->boolean( 20 ) ? $this->faker->randomFloat( 2, 2.99, 9.99 ) : 0,
			'signature_required' => $this->faker->boolean( 15 ),
		);
	}

	/**
	 * Generate tax details
	 *
	 * @since 1.0.0
	 *
	 * @param float $subtotal Order subtotal.
	 *
	 * @return array Tax details.
	 */
	private function generate_tax_details( float $subtotal ): array {
		$tax_rates = array(
			array(
				'name'   => 'State Tax',
				'rate'   => 0.08,
				'amount' => 0,
			),
			array(
				'name'   => 'City Tax',
				'rate'   => 0.025,
				'amount' => 0,
			),
		);

		$total_tax = 0;
		foreach ( $tax_rates as &$tax ) {
			$tax['amount'] = $subtotal * $tax['rate'];
			$total_tax    += $tax['amount'];
		}

		return array(
			'total'     => $total_tax,
			'breakdown' => $tax_rates,
			'exempt'    => $this->faker->boolean( 5 ), // 5% tax exempt
		);
	}

	/**
	 * Generate coupon details
	 *
	 * @since 1.0.0
	 *
	 * @param float $subtotal Order subtotal.
	 *
	 * @return array Coupon details.
	 */
	private function generate_coupon_details( float $subtotal ): array {
		// 25% chance of coupon being applied
		if ( ! $this->faker->boolean( 25 ) ) {
			return array(
				'applied'  => false,
				'discount' => 0,
				'coupons'  => array(),
			);
		}

		$coupons        = $this->get_random_coupons();
		$total_discount = 0;

		foreach ( $coupons as $coupon ) {
			if ( 'percentage' === $coupon['discount_type'] ) {
				$discount = $subtotal * ( $coupon['amount'] / 100 );
			} else {
				$discount = min( $coupon['amount'], $subtotal );
			}

			$coupon['discount_applied'] = $discount;
			$total_discount            += $discount;
		}

		return array(
			'applied'  => true,
			'discount' => $total_discount,
			'coupons'  => $coupons,
		);
	}

	/**
	 * Generate fulfillment status
	 *
	 * @since 1.0.0
	 *
	 * @return string Fulfillment status.
	 */
	private function generate_fulfillment_status(): string {
		$statuses = array(
			'unfulfilled'         => 30,
			'partially_fulfilled' => 10,
			'fulfilled'           => 25,
			'shipped'             => 20,
			'delivered'           => 10,
			'returned'            => 3,
			'cancelled'           => 2,
		);

		return $this->faker->randomElement(
			array_merge(
				...array_map(
					fn( $status, $weight ) => array_fill( 0, $weight, $status ),
					array_keys( $statuses ),
					$statuses
				)
			)
		);
	}

	/**
	 * Generate source information
	 *
	 * @since 1.0.0
	 *
	 * @return array Source information.
	 */
	private function generate_source_info(): array {
		$sources = array(
			'website'    => 60,
			'mobile_app' => 25,
			'phone'      => 10,
			'in_store'   => 5,
		);

		$source = $this->faker->randomElement(
			array_merge(
				...array_map(
					fn( $src, $weight ) => array_fill( 0, $weight, $src ),
					array_keys( $sources ),
					$sources
				)
			)
		);

		return array(
			'channel'      => $source,
			'user_agent'   => $this->faker->userAgent,
			'ip_address'   => $this->faker->ipv4,
			'referrer'     => $this->faker->optional( 0.4 )->url,
			'utm_source'   => $this->faker->optional( 0.3 )->randomElement(
				array(
					'google',
					'facebook',
					'email',
					'direct',
				)
			),
			'utm_medium'   => $this->faker->optional( 0.3 )->randomElement(
				array(
					'cpc',
					'social',
					'email',
					'organic',
				)
			),
			'utm_campaign' => $this->faker->optional( 0.2 )->words( 2, true ),
		);
	}

	/**
	 * Calculate total order amount including fees and discounts
	 *
	 * @since 1.0.0
	 *
	 * @param float $subtotal Order subtotal.
	 * @param array $order_meta Order metadata.
	 *
	 * @return float Total order amount.
	 */
	private function calculate_total( float $subtotal, array $order_meta ): float {
		$shipping = $order_meta['shipping_details']['cost'] + $order_meta['shipping_details']['insurance'];
		$tax      = $order_meta['tax_details']['total'];
		$discount = $order_meta['coupon_details']['discount'];

		return max( 0, $subtotal + $shipping + $tax - $discount );
	}

	/**
	 * Count total items in order
	 *
	 * @since 1.0.0
	 *
	 * @param array $order_items Order items array.
	 *
	 * @return int Total item count.
	 */
	private function count_order_items( array $order_items ): int {
		$count = 0;
		foreach ( $order_items as $product_id => $variations ) {
			foreach ( $variations as $price_id => $item ) {
				$count += $item['quantity'];
			}
		}

		return $count;
	}


	/**
	 * Generate fallback address if customer doesn't have one
	 *
	 * @since 1.0.0
	 *
	 * @param array $customer Customer user object.
	 *
	 * @return array Address data.
	 */
	private function generate_fallback_address( array $customer ): array {
		// Use Location model for realistic geographic data.
		$countries = Location::get_countries();
		if ( empty( $countries ) ) {
			// Fallback to static data if Location model data not available.
			return $this->generate_static_fallback_address( $customer );
		}

		// Weighted selection favoring common shipping countries.
		$weighted_countries = array(
			'US' => 60, // 60% US.
			'CA' => 15, // 15% Canada.
			'GB' => 10, // 10% UK.
			'AU' => 8,  // 8% Australia.
			'DE' => 4,  // 4% Germany.
			'FR' => 3,  // 3% France.
		);

		$country_code = $this->faker->randomElement(
			array_merge(
				...array_map(
					fn( $code, $weight ) => array_fill( 0, $weight, $code ),
					array_keys( $weighted_countries ),
					$weighted_countries
				)
			)
		);

		// Get states for the selected country.
		$states     = Location::get_states( $country_code );
		$state_data = $this->faker->randomElement( $states );
		$state_name = $state_data['name'] ?? $this->faker->state;
		$state_code = $state_data['state_code'] ?? $this->faker->stateAbbr;

		// Get cities for the selected state.
		$cities    = Location::get_cities( $country_code, $state_code );
		$city_data = $this->faker->randomElement( $cities );
		$city_name = $city_data['name'] ?? $this->faker->city;

		// Get country details.
		$country_data = array_filter( $countries, fn( $c ) => $c['iso2'] === $country_code );
		$country_info = reset( $country_data );
		$country_name = $country_info['name'] ?? $country_code;
		$phone_code   = $country_info['phone_code'] ?? '+1';

		return array(
			'first_name'   => get_user_meta( $customer['id'], 'first_name', true ) ?? $this->faker->firstName,
			'last_name'    => get_user_meta( $customer['id'], 'last_name', true ) ?? $this->faker->lastName,
			'email'        => $customer['email'],
			'phone'        => $phone_code . ' ' . $this->generate_phone_for_country( $country_code ),
			'company'      => $this->faker->optional( 0.3 )->company,
			'address_1'    => $this->faker->streetAddress,
			'address_2'    => $this->faker->optional( 0.3 )->secondaryAddress,
			'city'         => $city_name,
			'state'        => $state_name,
			'state_code'   => $state_code,
			'country'      => $country_name,
			'country_code' => $country_code,
			'postcode'     => $this->generate_postcode_for_country( $country_code ),
			'latitude'     => $city_data['latitude'] ?? null,
			'longitude'    => $city_data['longitude'] ?? null,
		);
	}

	/**
	 * Get random coupons for order using EasyCommerce models
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of coupon data.
	 */
	private function get_random_coupons(): array {
		// Get a larger pool of available coupons.
		$db           = new Database( 'coupons' );
		$coupons_data = $db->get_rows(
			array( 'active' => 1 ),
			20, // Get more coupons for better randomization.
			0,
			'RAND()'
		);

		if ( empty( $coupons_data ) ) {
			return array();
		}

		// Use faker to randomly select 1-2 coupons.
		$selected_count   = $this->faker->numberBetween( 1, 2 );
		$selected_coupons = $this->faker->randomElements(
			$coupons_data,
			min( $selected_count, count( $coupons_data ) )
		);

		return array_map(
			function ( $coupon ) {
				return (array) $coupon;
			},
			$selected_coupons
		);
	}

	/**
	 * Update customer statistics after order creation
	 *
	 * @since 1.0.0
	 *
	 * @param int   $customer_id Customer user ID.
	 * @param float $order_total Order total amount.
	 *
	 * @return void
	 */
	private function update_customer_stats( int $customer_id, float $order_total ): void {
		// Get current stats.
		$total_orders = (int) get_user_meta( $customer_id, 'total_orders', true );
		$total_spent  = (float) get_user_meta( $customer_id, 'total_spent', true );

		// Update stats.
		$new_total_orders        = $total_orders + 1;
		$new_total_spent         = $total_spent + $order_total;
		$new_average_order_value = $new_total_spent / $new_total_orders;

		update_user_meta( $customer_id, 'total_orders', $new_total_orders );
		update_user_meta( $customer_id, 'total_spent', number_format( $new_total_spent, 2, '.', '' ) );
		update_user_meta( $customer_id, 'average_order_value', number_format( $new_average_order_value, 2, '.', '' ) );
		update_user_meta( $customer_id, 'last_order_date', current_time( 'Y-m-d H:i:s' ) );

		// Update customer tier based on total spent.
		$tier = 'bronze';
		if ( $new_total_spent >= 5000 ) {
			$tier = 'platinum';
		} elseif ( $new_total_spent >= 2000 ) {
			$tier = 'gold';
		} elseif ( $new_total_spent >= 500 ) {
			$tier = 'silver';
		}
		update_user_meta( $customer_id, 'loyalty_tier', $tier );

		// Award loyalty points (1 point per dollar).
		$current_points = (int) get_user_meta( $customer_id, 'loyalty_points', true );
		$points_earned  = floor( $order_total );
		update_user_meta( $customer_id, 'loyalty_points', $current_points + $points_earned );
	}

	/**
	 * Generate comprehensive order item metadata
	 *
	 * @since 1.0.0
	 *
	 * @param Product_Variation $variation Product variation object.
	 * @param int               $quantity Item quantity.
	 * @param float             $rate Item rate.
	 * @param float             $subtotal Item subtotal.
	 *
	 * @return array Enhanced order item metadata.
	 */
	private function generate_order_item_meta( Product_Variation $variation, int $quantity, float $rate, float $subtotal ): array {
		$product = $variation->get_product();

		return array(
			// Core product information.
			'product_name'       => $product->get_title(),
			'variation_name'     => $variation->get_name( false ),

			// Pricing details.
			'regular_price'      => $variation->get_regular_price(),
			'sale_price'         => $variation->get_sale_price(),
			'price_formatted'    => easycommerce_price( $rate ),
			'subtotal_formatted' => easycommerce_price( $subtotal ),

			// Product attributes.
			'attributes'         => $variation->get_attributes(),
			'weight'             => $this->faker->randomFloat( 2, 0.1, 5.0 ),
			'dimensions'         => array(
				'length' => $this->faker->randomFloat( 2, 1, 20 ),
				'width'  => $this->faker->randomFloat( 2, 1, 20 ),
				'height' => $this->faker->randomFloat( 2, 1, 20 ),
			),

			// Fulfillment details.
			'requires_shipping'  => $this->faker->boolean( 85 ), // 85% require shipping.
			'is_virtual'         => $this->faker->boolean( 15 ), // 15% virtual products.
			'is_downloadable'    => $this->faker->boolean( 10 ), // 10% downloadable.
			'download_limit'     => $this->faker->optional( 0.3 )->numberBetween( 1, 10 ),
			'download_expiry'    => $this->faker->optional( 0.3 )->numberBetween( 1, 365 ),

			// Tax information.
			'tax_class'          => $variation->get_tax_class(),
			'tax_status'         => $this->faker->randomElement( array( 'taxable', 'shipping', 'none' ) ),

			// Inventory tracking.
			'stock_quantity'     => $variation->get_stock(),
			'low_stock_amount'   => $this->faker->numberBetween( 1, 10 ),
			'backorders'         => $this->faker->randomElement( array( 'no', 'notify', 'yes' ) ),

			// Additional metadata.
			'purchase_note'      => $this->faker->optional( 0.2 )->sentence(),
			'warranty_info'      => $this->faker->optional( 0.3 )->sentence(),
			'return_policy'      => $this->faker->optional( 0.2 )->sentence(),
		);
	}

	/**
	 * Generate item tax rate based on product type and location
	 *
	 * @since 1.0.0
	 *
	 * @return float Tax rate for the item.
	 */
	private function generate_item_tax_rate(): float {
		$tax_rates = array(
			'0.00'   => 10, // Tax-free (10%).
			'0.05'   => 15, // 5% tax (15%).
			'0.08'   => 35, // 8% tax (35%).
			'0.0825' => 25, // 8.25% tax (25%).
			'0.10'   => 10, // 10% tax (10%).
			'0.125'  => 5,  // 12.5% tax (5%).
		);

		return $this->faker->randomElement(
			array_merge(
				...array_map(
					static fn( $rate, $weight ) => array_fill( 0, $weight, $rate ),
					array_keys( $tax_rates ),
					$tax_rates
				)
			)
		);
	}

	/**
	 * Generate static fallback address when Location model is unavailable
	 *
	 * @since 1.0.0
	 *
	 * @param array $customer Customer user object.
	 *
	 * @return array Static address data.
	 */
	private function generate_static_fallback_address( array $customer ): array {
		return array(
			'first_name' => get_user_meta( $customer['id'], 'first_name', true ) ?? $this->faker->firstName,
			'last_name'  => get_user_meta( $customer['id'], 'last_name', true ) ?? $this->faker->lastName,
			'email'      => $customer['email'],
			'phone'      => $this->faker->phoneNumber,
			'company'    => $this->faker->optional( 0.3 )->company,
			'address_1'  => $this->faker->streetAddress,
			'address_2'  => $this->faker->optional( 0.3 )->secondaryAddress,
			'city'       => $this->faker->city,
			'state'      => $this->faker->stateAbbr,
			'country'    => 'US',
			'postcode'   => $this->faker->postcode,
		);
	}

	/**
	 * Generate phone number formatted for specific country
	 *
	 * @since 1.0.0
	 *
	 * @param string $country_code ISO2 country code.
	 *
	 * @return string Formatted phone number.
	 */
	private function generate_phone_for_country( string $country_code ): string {
		switch ( $country_code ) {
			case 'US':
			case 'CA':
				return $this->faker->regexify( '\([0-9]{3}\) [0-9]{3}-[0-9]{4}' );
			case 'GB':
				return $this->faker->regexify( '[0-9]{4} [0-9]{3} [0-9]{4}' );
			case 'AU':
				return $this->faker->regexify( '[0-9]{4} [0-9]{3} [0-9]{3}' );
			case 'DE':
				return $this->faker->regexify( '[0-9]{3} [0-9]{3} [0-9]{4}' );
			case 'FR':
				return $this->faker->regexify( '[0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2}' );
			default:
				return $this->faker->phoneNumber;
		}
	}

	/**
	 * Generate postcode formatted for specific country
	 *
	 * @since 1.0.0
	 *
	 * @param string $country_code ISO2 country code.
	 *
	 * @return string Formatted postcode.
	 */
	private function generate_postcode_for_country( string $country_code ): string {
		switch ( $country_code ) {
			case 'US':
				return $this->faker->regexify( '[0-9]{5}(-[0-9]{4})?' );
			case 'CA':
				return $this->faker->regexify( '[A-Z][0-9][A-Z] [0-9][A-Z][0-9]' );
			case 'GB':
				return $this->faker->regexify( '[A-Z]{1,2}[0-9]{1,2}[A-Z]? [0-9][A-Z]{2}' );
			case 'AU':
				return $this->faker->regexify( '[0-9]{4}' );
			case 'DE':
				return $this->faker->regexify( '[0-9]{5}' );
			case 'FR':
				return $this->faker->regexify( '[0-9]{5}' );
			default:
				return $this->faker->postcode;
		}
	}
}
