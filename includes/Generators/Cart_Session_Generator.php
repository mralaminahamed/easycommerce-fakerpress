<?php
/**
 * Cart Session Generator Class
 *
 * @package EasyCommerceFakerPress\Generators
 * @since   1.0.0
 */

namespace EasyCommerceFakerPress\Generators;

defined( 'ABSPATH' ) || exit;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Customer;
use EasyCommerce\Models\Database;
use EasyCommerce\Helpers\Utility;
use Exception;
use RuntimeException;

/**
 * Cart Session Generator Class
 *
 * Generates realistic cart sessions for abandoned cart analysis and marketing.
 */
class Cart_Session_Generator extends Generator {

	/**
	 * Get the resource type name
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'cart_session';
	}

	/**
	 * Generate a single cart session
	 *
	 * @return array|bool Single cart session data, error, or false on failure.
	 * @throws RuntimeException If no products or customers are found.
	 */
	protected function generate_single_item() {
		try {
			// Get existing products and customers.
			$product_data  = Product::list( array(), 50 );
			$customer_data = Customer::list( 'customer', null, 1, 30 );

			$products  = $product_data['products'] ?? array();
			$customers = $customer_data['users'] ?? array();

			if ( empty( $products ) ) {
				throw new RuntimeException( 'No products found. Please generate products first.' );
			}

			$cart_data    = $this->generate_cart_session_data( $products, $customers );
			$cart_session = $this->create_cart_session( $cart_data );

			if ( $cart_session ) {
				return array(
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
			}

			return false;
		} catch ( Exception $e ) {
			$this->log( 'Failed to generate cart session: ' . $e->getMessage(), 'error' );
			return false;
		}
	}

	/**
	 * Generate multiple cart sessions.
	 *
	 * @param int   $count Number of cart sessions to generate.
	 * @param array $args Additional arguments.
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
	 * @param Product[] $products Available products.
	 * @param array     $customers Available customers.
	 * @return array Cart session data
	 */
	private function generate_cart_session_data( array $products, array $customers ): array {
		$cart_statuses = array(
			'pending'   => 60,    // 60% pending (active carts)
			'abandoned' => 30,  // 30% abandoned
			'completed' => 8,   // 8% completed
			'cancelled' => 2,   // 2% cancelled
		);

		$status = $this->faker->randomElement(
			array_merge(
				...array_map(
					static fn( $status, $weight ) => array_fill( 0, $weight, $status ),
					array_keys( $cart_statuses ),
					$cart_statuses
				)
			)
		);

		// Select customer (70% have customer accounts).
		$customer = $this->faker->boolean( 70 ) && ! empty( $customers )
			? $this->faker->randomElement( $customers )
			: null;

		// Generate cart items.
		$items        = $this->generate_cart_items( $products );
		$total_amount = $this->calculate_cart_total( $items );

		// Generate addresses if cart is advanced.
		$addresses = $this->faker->boolean( 40 ) ? $this->generate_cart_addresses() : array();

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
	 * @param Product[] $products Available products.
	 * @return array Cart items
	 */
	private function generate_cart_items( array $products ): array {
		$items      = array();
		$item_count = $this->faker->numberBetween( 1, 8 ); // 1-8 items per cart

		$selected_products = $this->faker->randomElements(
			$products,
			min( $item_count, count( $products ) )
		);

		foreach ( $selected_products as $product ) {
			// Get product variations.
			$variation_db = new Database( 'product_variations' );
			$variations   = $variation_db->get_rows( array( 'product_id' => $product->get_id() ) );

			if ( empty( $variations ) ) {
				continue;
			}

			$variation = $this->faker->randomElement( $variations );
			$quantity  = $this->faker->numberBetween( 1, 5 );
			$price     = $this->faker->randomFloat( 2, 5, 200 );

			$items[ $product->get_id() ][ $variation->price_id ] = array(
				'quantity' => $quantity,
				'rate'     => $price,
				'price'    => $quantity * $price,
			);
		}

		return $items;
	}

	/**
	 * Calculate cart total amount.
	 *
	 * @param array $items Cart items.
	 * @return float Total amount
	 */
	private function calculate_cart_total( array $items ): float {
		$total = 0;

		foreach ( $items as $product_id => $variations ) {
			foreach ( $variations as $price_id => $config ) {
				$total += $config['price'];
			}
		}

		// Add shipping and tax estimate.
		$shipping = $this->faker->randomFloat( 2, 0, 25 );
		$tax      = $total * $this->faker->randomFloat( 2, 0.05, 0.15 ); // 5-15% tax

		return $total + $shipping + $tax;
	}

	/**
	 * Generate cart addresses.
	 *
	 * @return array Cart addresses
	 */
	private function generate_cart_addresses(): array {
		$addresses = array(
			'billing' => array(
				'first_name' => $this->faker->firstName,
				'last_name'  => $this->faker->lastName,
				'email'      => $this->faker->email,
				'phone'      => $this->faker->phoneNumber,
				'address_1'  => $this->faker->streetAddress,
				'address_2'  => $this->faker->boolean( 30 ) ? $this->faker->secondaryAddress : '',
				'city'       => $this->faker->city,
				'state'      => $this->faker->stateAbbr,
				'postcode'   => $this->faker->postcode,
				'country'    => $this->faker->countryCode,
			),
		);

		// 60% chance of different shipping address
		if ( $this->faker->boolean( 60 ) ) {
			$addresses['shipping'] = array(
				'first_name' => $this->faker->firstName,
				'last_name'  => $this->faker->lastName,
				'address_1'  => $this->faker->streetAddress,
				'address_2'  => $this->faker->boolean( 30 ) ? $this->faker->secondaryAddress : '',
				'city'       => $this->faker->city,
				'state'      => $this->faker->stateAbbr,
				'postcode'   => $this->faker->postcode,
				'country'    => $this->faker->countryCode,
			);
		}

		return $addresses;
	}

	/**
	 * Generate cart timeline based on status.
	 *
	 * @param string $status Cart status.
	 * @return array Timeline data
	 */
	private function generate_cart_timeline( string $status ): array {
		$now = current_datetime()->getTimestamp();

		switch ( $status ) {
			case 'pending':
				// Active carts - recent activity.
				$created_hours_ago   = $this->faker->numberBetween( 1, 24 );
				$updated_minutes_ago = $this->faker->numberBetween( 1, 180 );
				break;

			case 'abandoned':
				// Abandoned carts - older with no recent activity.
				$created_hours_ago   = $this->faker->numberBetween( 24, 168 ); // 1-7 days ago
				$updated_hours_ago   = $this->faker->numberBetween( 2, 72 ); // 2-72 hours ago
				$updated_minutes_ago = $updated_hours_ago * 60;
				break;

			case 'completed':
				// Completed carts.
				$created_hours_ago   = $this->faker->numberBetween( 1, 72 );
				$updated_minutes_ago = $this->faker->numberBetween( 30, $created_hours_ago * 60 );
				break;

			case 'cancelled':
				// Cancelled carts.
				$created_hours_ago   = $this->faker->numberBetween( 1, 48 );
				$updated_minutes_ago = $this->faker->numberBetween( 10, $created_hours_ago * 60 );
				break;

			default:
				$created_hours_ago   = $this->faker->numberBetween( 1, 24 );
				$updated_minutes_ago = $this->faker->numberBetween( 1, 180 );
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
	 * @return int Reminder count
	 */
	private function generate_reminder_count( string $status ): int {
		switch ( $status ) {
			case 'abandoned':
				return $this->faker->numberBetween( 1, 5 ); // Abandoned carts get reminders.
			case 'pending':
				return $this->faker->boolean( 30 ) ? $this->faker->numberBetween( 0, 2 ) : 0;
			default:
				return 0;
		}
	}

	/**
	 * Create cart session in database.
	 *
	 * @param array $data Cart session data.
	 * @return array|null Created cart session data
	 */
	private function create_cart_session( array $data ): ?array {
		$cart_db = new Database( 'cart_sessions' );

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
		if ( $this->faker->boolean( 20 ) ) {
			$cart_data['coupons'] = array( $this->faker->regexify( '[A-Z]{4}[0-9]{2}' ) );
		}

		// Add shipping method randomly (30% chance).
		if ( $this->faker->boolean( 30 ) ) {
			$cart_data['shipping_method'] = $this->faker->numberBetween( 1, 5 );
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
				$customer = new Customer( $data['user_id'] );
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

		return null;
	}

	/**
	 * Generate abandoned cart scenarios specifically.
	 *
	 * @param int $count Number of abandoned carts to generate.
	 * @return array Generated abandoned cart data
	 */
	public function generate_abandoned_carts( int $count = 10 ): array {
		$results = array();

		// Get existing products and customers.
		$product_data  = Product::list( array(), 30 );
		$customer_data = Customer::list( 'customer', null, 1, 20 );

		$products  = $product_data['products'] ?? array();
		$customers = $customer_data['users'] ?? array();

		for ( $i = 0; $i < $count; $i++ ) {
			try {
				$now       = current_datetime()->getTimestamp();
				$cart_data = $this->generate_cart_session_data( $products, $customers );

				$cart_data['status']    = 'abandoned'; // Force abandoned status.
				$cart_data['reminders'] = $this->faker->numberBetween( 0, 3 );

				// Ensure older timeline for abandoned carts.
				$hours_ago = $this->faker->numberBetween( 48, 336 ); // 2-14 days ago

				$cart_data['created_at'] = wp_date( 'Y-m-d H:i:s', $now - ( $hours_ago * 3600 ) );
				$cart_data['updated_at'] = wp_date( 'Y-m-d H:i:s', $now - ( $this->faker->numberBetween( 24, $hours_ago ) * 3600 ) );

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

	/**
	 * Get supported data types for this generator.
	 *
	 * @return array Supported types
	 */
	public function get_supported_types(): array {
		return array(
			'cart_sessions' => 'Shopping Cart Sessions and Abandoned Carts',
		);
	}

	/**
	 * Get generator description.
	 *
	 * @return string Description
	 */
	public function get_description(): string {
		return 'Generates realistic shopping cart sessions with various statuses (pending, abandoned, completed, cancelled), customer information, multiple items, billing/shipping addresses, and timeline data for testing abandoned cart recovery, analytics, and marketing automation systems.';
	}
}
