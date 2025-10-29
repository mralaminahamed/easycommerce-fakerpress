<?php
/**
 * Cart Session Generator Class
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Cart;
use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Product as ProductModel;
use EasyCommerce\Models\Customer as CustomerModel;
use EasyCommerce\Models\Database as DatabaseModel;
use EasyCommerce\Helpers\Utility;
use Exception;
use WP_Error;

/**
 * Cart Session Generator Class
 *
 * Generates realistic cart sessions for abandoned cart analysis and marketing.
 */
class Cart_Session extends Generator {

	/**
	 * Get the resource type name
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'cart_session';
	}

	/**
	 * Get supported data types for this generator.
	 *
	 * @return array Supported types
	 */
	public function get_supported_types(): array {
		return array(
			'cart_sessions' => __( 'Shopping Cart Sessions and Abandoned Carts', 'easycommerce-fakerpress' ),
		);
	}

	/**
	 * Get generator description.
	 *
	 * @return string Description
	 */
	public function get_description(): string {
		return __( 'Generates realistic shopping cart sessions with various statuses (pending, abandoned, completed, cancelled), customer information, multiple items, billing/shipping addresses, and timeline data for testing abandoned cart recovery, analytics, and marketing automation systems.', 'easycommerce-fakerpress' );
	}

	/**
	 * Generate a single cart session
	 *
	 * @return WP_Error|array Single cart session data, error, or false on failure.
	 */
	protected function generate_single_item() {
		// Check if EasyCommerce Cart class exists.
		if ( ! class_exists( Cart::class ) ) {
			return new WP_Error( 'missing_model', __( 'EasyCommerce Cart model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' ) );
		}

		// Get existing products.
		$product_data = ProductModel::list( array(), 50 );
		$products     = $product_data['products'] ?? array();

		if ( empty( $products ) ) {
			return new WP_Error( 'no-products', __( 'No products found', 'easycommerce-fakerpress' ) );
		}

		// Get customer for cart session.
		$customer = $this->get_customer_for_cart();

		if ( is_wp_error( $customer ) ) {
			return $customer;
		}

		$cart_data    = $this->generate_cart_session_data( $products, $customer );
		$cart_session = $this->create_cart_session( $cart_data );

		if ( is_wp_error( $cart_session ) ) {
			return $cart_session;
		}

		$result = array(
			'hash'           => $cart_session['hash'],
			'user_id'        => $cart_session['user_id'],
			'status'         => $cart_session['status'],
			'items_count'    => count( $cart_session['items'] ),
			'total_amount'   => $cart_session['total_amount'],
			'customer_email' => $cart_session['customer_email'],
			'customer_name'  => $cart_session['customer_name'],
			'reminders'      => $cart_session['reminders'],
			'created_at'     => $cart_session['created_at'],
			'updated_at'     => $cart_session['updated_at'],
			'items'          => $cart_session['items'],
			'addresses'      => $cart_session['addresses'],
		);

		/**
		 * Filters the cart session generation result data.
		 *
		 * Allows developers to modify the returned cart session data after generation.
		 *
		 * @since 1.0.0
		 * @hook easycommerce_fakerpress_cart_session_generation_result
		 *
		 * @param array $result         The cart session generation result data.
		 * @param array $cart_session   The created cart session data.
		 */
		return apply_filters( 'easycommerce_fakerpress_cart_session_generation_result', $result, $cart_session );
	}

	/**
	 * Get customer for cart session based on parameters
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Customer data, empty array for guest, or error.
	 */
	private function get_customer_for_cart() {
		$customer_type        = $this->generation_params['customer_type'] ?? 'mixed';
		$specific_customer_id = $this->generation_params['specific_customer_id'] ?? null;
		$guest_ratio          = $this->generation_params['guest_cart_ratio'] ?? 40;

		switch ( $customer_type ) {
			case 'existing':
				return $this->get_random_existing_customer();

			case 'new':
				return $this->create_new_customer_for_cart();

			case 'specific':
				if ( $specific_customer_id ) {
					return $this->get_specific_customer_for_cart( $specific_customer_id );
				}

				// Fallback to random.
				return $this->get_random_existing_customer();

			case 'guest_only':
				return array(); // Guest cart.

			case 'mixed':
			default:
				// Use guest ratio parameter.
				if ( $this->get_faker()->boolean( $guest_ratio ) ) {
					return array(); // Guest cart.
				}

				// 50/50 between existing and new customers.
				if ( $this->get_faker()->boolean( 50 ) ) {
					return $this->get_random_existing_customer();
				}

				return $this->create_new_customer_for_cart();
		}
	}

	/**
	 * Get random existing customer
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|array Customer data or null if none found.
	 */
	private function get_random_existing_customer() {
		$customer_data = CustomerModel::list( 'customer', null, 1, 30 );
		$customers     = $customer_data['users'] ?? array();

		if ( empty( $customers ) ) {
			return new WP_Error( 'no-customers', __( 'No customers found.', 'easycommerce-fakerpress' ) );
		}

		$customer = $this->get_faker()->randomElement( $customers );

		return array(
			'id'         => $customer['id'],
			'name'       => $customer['name'],
			'email'      => $customer['email'],
			'first_name' => $customer['first_name'] ?? '',
			'last_name'  => $customer['last_name'] ?? '',
		);
	}

	/**
	 * Get specific customer for cart
	 *
	 * @since 1.0.0
	 *
	 * @param int $customer_id Customer ID.
	 *
	 * @return WP_Error|array Customer data or null if not found.
	 */
	private function get_specific_customer_for_cart( int $customer_id ) {
		$customer = new CustomerModel( $customer_id );
		if ( $customer->get_id() && $customer->get_id() > 0 ) {
			return array(
				'id'         => $customer->get_id(),
				'name'       => $customer->get_name(),
				'email'      => $customer->get_email(),
				'first_name' => $customer->get_first_name(),
				'last_name'  => $customer->get_last_name(),
			);
		}

		return new WP_Error( 'no-customers', __( 'No customers found.', 'easycommerce-fakerpress' ) );
	}

	/**
	 * Create new customer for cart session
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|array New customer data or null on failure.
	 */
	private function create_new_customer_for_cart() {
		$customer_generator = new Customer();

		$customer_generator->set_locale( $this->get_faker_locale() );
		$customer_generator->set_faker();

		$customer = $customer_generator->generate_single_item();

		if ( is_wp_error( $customer ) ) {
			return $customer;
		}

		return array(
			'id'         => $customer['id'],
			'name'       => $customer['name'],
			'email'      => $customer['email'],
			'first_name' => $customer['first_name'] ?? '',
			'last_name'  => $customer['last_name'] ?? '',
		);
	}

	/**
	 * Generate multiple cart sessions.
	 *
	 * @param int   $count Number of cart sessions to generate.
	 * @param array $args Additional arguments.
	 *
	 * @return array Generated cart session data
	 */
	public function generate_multiple( int $count = 15, array $args = array() ): array {
		$results = array();

		for ( $i = 0; $i < $count; $i++ ) {
			$item_result = $this->generate_single_item();

			if ( $item_result && ! is_wp_error( $item_result ) ) {
				$results[] = $item_result;
			}
		}

		return $results;
	}

	/**
	 * Generate cart session data.
	 *
	 * @param array $products Available products.
	 * @param array $customer Selected customer or null for guest.
	 *
	 * @return array Cart session data
	 */
	private function generate_cart_session_data( array $products, $customer ): array {
		// Get abandonment settings from parameters.
		$abandonment_rate    = $this->generation_params['abandonment_rate'] ?? 30;
		$status_distribution = $this->generation_params['status_distribution'] ?? array();

		// Default status distribution.
		$default_statuses = array(
			'pending'   => 60 - $abandonment_rate,
			'abandoned' => $abandonment_rate,
			'completed' => 8,
			'cancelled' => 2,
		);

		$cart_statuses = ! empty( $status_distribution ) ? array_map( 'intval', $status_distribution ) : $default_statuses;

		$weighted_statuses = array();
		foreach ( $cart_statuses as $status_name => $weight ) {
			$weight_count = max( 0, $weight );
			for ( $i = 0; $i < $weight_count; $i++ ) {
				$weighted_statuses[] = $status_name;
			}
		}

		$status = $this->get_faker()->randomElement( $weighted_statuses );

		// Customer is already selected by get_customer_for_cart() method.

		// Generate cart items.
		$items        = $this->generate_cart_items( $products );
		$total_amount = $this->calculate_cart_total( $items );

		// Generate addresses if cart is advanced.
		$addresses = $this->get_faker()->boolean( 40 ) ? $this->generate_cart_addresses() : array();

		// Generate timeline based on status.
		$timeline = $this->generate_cart_timeline( $status );

		return array(
			'user_id'      => $customer['id'] ?? 0,
			'status'       => $status,
			'items'        => $items,
			'addresses'    => $addresses,
			'total_amount' => $total_amount,
			'reminders'    => $this->generate_reminder_count( $status ),
			'created_at'   => $timeline['created_at'],
			'updated_at'   => $timeline['updated_at'],
		);
	}

	/**
	 * Generate cart items.
	 *
	 * @param ProductModel[] $products Available products.
	 *
	 * @return array Cart items
	 */
	private function generate_cart_items( array $products ): array {
		$items      = array();
		$item_count = $this->get_faker()->numberBetween( 1, 8 ); // 1-8 items per cart

		$selected_products = $this->get_faker()->randomElements(
			$products,
			min( $item_count, count( $products ) )
		);

		foreach ( $selected_products as $product ) {
			// Get product variations.
			$variation_db = new DatabaseModel( 'product_variations' );
			$variations   = $variation_db->get_rows( array( 'product_id' => $product->get_id() ) );

			if ( empty( $variations ) ) {
				continue;
			}

			$variation = $this->get_faker()->randomElement( $variations );
			$quantity  = $this->get_faker()->numberBetween( 1, 5 );
			$price     = $this->get_faker()->randomFloat( 2, 5, 200 );

			$items[ $product->get_id() ][ $variation->price_id ] = array(
				'quantity' => $quantity,
				'rate'     => $price,
				'price'    => $quantity * $price,
			);
		}

		return $items;
	}

	/**
	 * Calculate cart total amount with realistic shipping and tax.
	 *
	 * @param array $items Cart items.
	 *
	 * @return float Total amount
	 */
	private function calculate_cart_total( array $items ): float {
		$subtotal = 0;

		// Calculate subtotal from items.
		foreach ( $items as $variations ) {
			foreach ( $variations as $config ) {
				$subtotal += $config['price'];
			}
		}

		// Calculate realistic shipping based on subtotal.
		$shipping = $this->calculate_realistic_shipping( $subtotal );

		// Calculate realistic tax based on subtotal (using typical tax rates).
		$tax = $this->calculate_realistic_tax( $subtotal );

		return $subtotal + $shipping + $tax;
	}

	/**
	 * Calculate realistic shipping cost based on cart subtotal.
	 *
	 * @param float $subtotal Cart subtotal.
	 *
	 * @return float Shipping cost
	 */
	private function calculate_realistic_shipping( float $subtotal ): float {
		// Free shipping for orders over $100 (common threshold).
		if ( $subtotal >= 100 ) {
			return $this->get_faker()->boolean( 80 ) ? 0 : $this->get_faker()->randomFloat( 2, 5, 10 );
		}

		// Tiered shipping based on subtotal.
		if ( $subtotal >= 50 ) {
			return $this->get_faker()->randomFloat( 2, 5, 12 );
		}

		if ( $subtotal >= 25 ) {
			return $this->get_faker()->randomFloat( 2, 8, 15 );
		}

		// Small orders have higher shipping.
		return $this->get_faker()->randomFloat( 2, 10, 20 );
	}

	/**
	 * Calculate realistic tax based on cart subtotal.
	 *
	 * Uses common tax rates from major jurisdictions.
	 *
	 * @param float $subtotal Cart subtotal.
	 *
	 * @return float Tax amount
	 */
	private function calculate_realistic_tax( float $subtotal ): float {
		$common_tax_rates = array(
			0.00,  // Tax-free states (DE, MT, NH, OR).
			5.00,  // Low tax states.
			6.00,  // Average tax states.
			7.00,  // Above average.
			7.25,  // CA base rate.
			8.25,  // High tax states.
			8.875, // NY rate.
			9.50,  // Combined state + local.
			10.00, // High combined rates.
		);

		$tax_rate = $this->get_faker()->randomElement( $common_tax_rates );

		return $subtotal * ( $tax_rate / 100 );
	}

	/**
	 * Generate cart addresses.
	 *
	 * @return array Cart addresses
	 */
	private function generate_cart_addresses(): array {
		$addresses = array(
			'billing' => array(
				'first_name' => $this->get_faker()->firstName,
				'last_name'  => $this->get_faker()->lastName,
				'email'      => $this->get_faker()->email,
				'phone'      => $this->get_faker()->phoneNumber,
				'address_1'  => $this->get_faker()->streetAddress,
				'address_2'  => $this->get_faker()->boolean( 30 ) ? $this->get_faker()->secondaryAddress : '',
				'city'       => $this->get_faker()->city,
				'state'      => $this->get_faker()->stateAbbr,
				'postcode'   => $this->get_faker()->postcode,
				'country'    => $this->get_faker()->countryCode,
			),
		);

		// 60% chance of different shipping address
		if ( $this->get_faker()->boolean( 60 ) ) {
			$addresses['shipping'] = array(
				'first_name' => $this->get_faker()->firstName,
				'last_name'  => $this->get_faker()->lastName,
				'address_1'  => $this->get_faker()->streetAddress,
				'address_2'  => $this->get_faker()->boolean( 30 ) ? $this->get_faker()->secondaryAddress : '',
				'city'       => $this->get_faker()->city,
				'state'      => $this->get_faker()->stateAbbr,
				'postcode'   => $this->get_faker()->postcode,
				'country'    => $this->get_faker()->countryCode,
			);
		}

		return $addresses;
	}

	/**
	 * Generate cart timeline based on status.
	 *
	 * @param string $status Cart status.
	 *
	 * @return array Timeline data
	 */
	private function generate_cart_timeline( string $status ): array {
		$now = current_datetime()->getTimestamp();

		switch ( $status ) {
			case 'pending':
				// Active carts - recent activity.
				$created_hours_ago   = $this->get_faker()->numberBetween( 1, 24 );
				$updated_minutes_ago = $this->get_faker()->numberBetween( 1, 180 );
				break;

			case 'abandoned':
				// Abandoned carts - older with no recent activity.
				$created_hours_ago   = $this->get_faker()->numberBetween( 24, 168 ); // 1-7 days ago
				$updated_hours_ago   = $this->get_faker()->numberBetween( 2, 72 ); // 2-72 hours ago
				$updated_minutes_ago = $updated_hours_ago * 60;
				break;

			case 'completed':
				// Completed carts.
				$created_hours_ago   = $this->get_faker()->numberBetween( 1, 72 );
				$updated_minutes_ago = $this->get_faker()->numberBetween( 30, $created_hours_ago * 60 );
				break;

			case 'cancelled':
				// Cancelled carts.
				$created_hours_ago   = $this->get_faker()->numberBetween( 1, 48 );
				$updated_minutes_ago = $this->get_faker()->numberBetween( 10, $created_hours_ago * 60 );
				break;

			default:
				$created_hours_ago   = $this->get_faker()->numberBetween( 1, 24 );
				$updated_minutes_ago = $this->get_faker()->numberBetween( 1, 180 );
		}

		$created_at = wp_date( 'Y-m-d H:i:s', $now - ( $created_hours_ago * 3600 ) );
		$updated_at = wp_date( 'Y-m-d H:i:s', $now - ( $updated_minutes_ago * 60 ) );

		return array(
			'created_at' => $created_at,
			'updated_at' => $updated_at,
		);
	}

	/**
	 * Generate reminder count based on status.
	 *
	 * @param string $status Cart status.
	 *
	 * @return int Reminder count
	 */
	private function generate_reminder_count( string $status ): int {
		switch ( $status ) {
			case 'abandoned':
				return $this->get_faker()->numberBetween( 1, 5 ); // Abandoned carts get reminders.
			case 'pending':
				return $this->get_faker()->boolean( 30 ) ? $this->get_faker()->numberBetween( 0, 2 ) : 0;
			default:
				return 0;
		}
	}

	/**
	 * Create cart session in database.
	 *
	 * @param array $data Cart session data.
	 *
	 * @return WP_Error|array Created cart session data
	 */
	private function create_cart_session( array $data ) {
		$cart_db = new DatabaseModel( 'cart_sessions' );

		// Generate unique hash.
		$hash = Utility::generate_hash();

		// Prepare cart data for database.
		$cart_data = array(
			'items' => $data['items'],
		);

		// Add addresses if present.
		if ( ! empty( $data['addresses'] ) ) {
			$cart_data['address'] = $data['addresses'];
		}

		// Add coupons randomly (20% chance).
		if ( $this->get_faker()->boolean( 20 ) ) {
			$cart_data['coupons'] = array( $this->get_faker()->regexify( '[A-Z]{4}[0-9]{2}' ) );
		}

		// Add shipping method randomly (30% chance).
		if ( $this->get_faker()->boolean( 30 ) ) {
			$cart_data['shipping_method'] = $this->get_faker()->numberBetween( 1, 5 );
		}

		$db_data = array(
			'user_id'    => $data['user_id'],
			'hash'       => $hash,
			'data'       => maybe_serialize( $cart_data ),
			'status'     => $data['status'],
			'reminders'  => $data['reminders'],
			'created_at' => $data['created_at'],
			'updated_at' => $data['updated_at'],
		);

		$cart_id = $cart_db->insert_row( $db_data );

		if ( $cart_id ) {
			// Get customer details.
			$customer_email = '';
			$customer_name  = '';

			if ( $data['user_id'] > 0 ) {
				$customer = new CustomerModel( $data['user_id'] );
				if ( $customer->get_id() ) {
					$customer_email = $customer->get_email();
					$customer_name  = $customer->get_name();
				}
			} elseif ( ! empty( $data['addresses']['billing'] ) ) {
				$billing        = $data['addresses']['billing'];
				$customer_email = $billing['email'] ?? '';
				$customer_name  = ( $billing['first_name'] ?? '' ) . ' ' . ( $billing['last_name'] ?? '' );
			}

			return array(
				'id'             => $cart_id,
				'hash'           => $hash,
				'user_id'        => $data['user_id'],
				'status'         => $data['status'],
				'items'          => $data['items'],
				'addresses'      => $data['addresses'],
				'total_amount'   => $data['total_amount'],
				'customer_email' => trim( $customer_email ),
				'customer_name'  => trim( $customer_name ),
				'reminders'      => $data['reminders'],
				'created_at'     => $data['created_at'],
				'updated_at'     => $data['updated_at'],
			);
		}

		return new WP_Error( 'cart-creation-failed', __( 'There was an error creating the cart.', 'easycommerce-fakerpress' ) );
	}

	/**
	 * Generate abandoned cart scenarios specifically.
	 *
	 * @param int $count Number of abandoned carts to generate.
	 *
	 * @return array Generated abandoned cart data
	 */
	public function generate_abandoned_carts( int $count = 10 ): array {
		$results = array();

		// Get existing products and customers.
		$product_data  = ProductModel::list( array(), 30 );
		$customer_data = CustomerModel::list( 'customer', null, 1, 20 );

		$products  = $product_data['products'] ?? array();
		$customers = $customer_data['users'] ?? array();

		for ( $i = 0; $i < $count; $i++ ) {
			try {
				$now       = current_datetime()->getTimestamp();
				$cart_data = $this->generate_cart_session_data( $products, $customers );

				$cart_data['status']    = 'abandoned'; // Force abandoned status.
				$cart_data['reminders'] = $this->get_faker()->numberBetween( 0, 3 );

				// Ensure older timeline for abandoned carts.
				$hours_ago = $this->get_faker()->numberBetween( 48, 336 ); // 2-14 days ago

				$cart_data['created_at'] = wp_date( 'Y-m-d H:i:s', $now - ( $hours_ago * 3600 ) );
				$cart_data['updated_at'] = wp_date( 'Y-m-d H:i:s', $now - ( $this->get_faker()->numberBetween( 24, $hours_ago ) * 3600 ) );

				$cart_session = $this->create_cart_session( $cart_data );

				if ( $cart_session ) {
					$results[] = $cart_session;
				}
			} catch ( Exception $e ) {
				$this->log( "Failed to generate abandoned cart {$i}: " . $e->getMessage(), 'error' );
				continue;
			}
		}

		return $results;
	}
}
