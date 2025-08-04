<?php
/**
 * Order Generator
 *
 * @package EasyCommerceFakerPress\Generators
 * @since   1.0.0
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
use WP_Error;

/**
 * Order Generator Class
 *
 * Generates fake order data for EasyCommerce
 *
 * @since 1.0.0
 */
class Order_Generator extends Generator {

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
	 * @return array|WP_Error|false Single order data, error, or false on failure.
	 */
	protected function generate_single_item() {
		$customer   = $this->getRandomCustomer();
		$variations = $this->getRandomProductVariations();

		if ( ! $customer || empty( $variations ) ) {
			return false;
		}

		// Create order in EasyCommerce orders table
		$order_data = [
			'customer_id'    => $customer->ID,
			'total'          => 0, // Will be calculated after adding items
			'status'         => $this->faker->randomElement( [ 'pending', 'processing', 'completed', 'cancelled', 'on_hold', 'refunded' ] ),
			'fulfill_status' => $this->faker->randomElement( [ 'unfulfilled', 'fulfilled', 'partially_fulfilled', 'shipped', 'delivered', 'returned' ] ),
			'payment_method' => $this->faker->randomElement( [ 'stripe', 'paypal', 'bank_transfer', 'cash_on_delivery', 'credit_card' ] ),
			'created_at'     => $this->faker->dateTimeBetween( '-1 year', 'now' )->format( 'Y-m-d H:i:s' ),
			'updated_at'     => date( 'Y-m-d H:i:s' ),
		];

		$orders_table = $this->wpdb->prefix . 'orders';
		$this->wpdb->insert( $orders_table, $order_data );
		$order_id = $this->wpdb->insert_id;

		if ( ! $order_id ) {
			return false;
		}

		try {
			// Add order items and calculate total
			$total = $this->addOrderItems( $order_id, $variations );

			// Update order total
			$this->wpdb->update(
				$orders_table,
				[ 'total' => number_format( $total, 2, '.', '' ) ],
				[ 'id' => $order_id ]
			);

			// Add order metadata
			$this->addOrderMeta( $order_id, $customer, $total );

			// Update customer statistics
			$this->updateCustomerStats( $customer->ID, $total );

			return [
				'id'       => $order_id,
				'customer' => $customer->display_name,
				'total'    => number_format( $total, 2 ),
				'status'   => $order_data['status']
			];
		} catch ( \Exception $e ) {
			// Clean up the created order if processing fails
			$this->wpdb->delete( $orders_table, [ 'id' => $order_id ] );
			return new WP_Error( 'order_creation_failed', $e->getMessage() );
		}
	}

	private function getRandomCustomer() {
		$customers = get_users( [ 'role' => 'customer', 'number' => 1, 'orderby' => 'rand' ] );

		return ! empty( $customers ) ? $customers[0] : null;
	}

	private function getRandomProductVariations() {
		$variations_table = $this->wpdb->prefix . 'product_variations';
		$variations       = $this->wpdb->get_results( $this->wpdb->prepare(
			"SELECT * FROM {$variations_table} WHERE status = 'in_stock' ORDER BY RAND() LIMIT %d",
			$this->faker->numberBetween( 1, 5 )
		),
			ARRAY_A );

		return $variations;
	}

	private function addOrderItems( $order_id, $variations ): float {
		$order_items_table = $this->wpdb->prefix . 'order_items';
		$subtotal          = 0;

		foreach ( $variations as $variation ) {
			$quantity = $this->faker->numberBetween( 1, 3 );
			$rate     = (float) $variation['price'];
			$price    = $rate * $quantity;

			// Get tax information
			$tax_class_id = $this->getRandomTaxClass();
			$tax_rate     = $this->getTaxRate( $tax_class_id );

			$item_data = [
				'order_id'     => $order_id,
				'product_id'   => $variation['product_id'],
				'variation_id' => $variation['id'],
				'quantity'     => $quantity,
				'rate'         => number_format( $rate, 2, '.', '' ),
				'price'        => number_format( $price, 2, '.', '' ),
				'tax_class_id' => $tax_class_id,
				'tax_rate'     => number_format( $tax_rate, 4, '.', '' ),
				'subtotal'     => number_format( $price, 2, '.', '' ),
			];

			$this->wpdb->insert( $order_items_table, $item_data );
			$item_id = $this->wpdb->insert_id;

			// Add order item metadata
			$this->addOrderItemMeta( $item_id, $variation );

			$subtotal += $price;
		}

		// Calculate tax and shipping
		$tax_amount    = $subtotal * 0.08; // 8% tax rate
		$shipping_cost = $this->faker->randomFloat( 2, 0, 25 );
		$total         = $subtotal + $tax_amount + $shipping_cost;

		return $total;
	}

	private function addOrderItemMeta( $item_id, $variation ): void {
		$order_item_meta_table = $this->wpdb->prefix . 'order_item_meta';

		$meta_entries = [
			[ 'order_item_id' => $item_id, 'meta_key' => 'product_name', 'meta_value' => $variation['name'] ],
			[ 'order_item_id' => $item_id, 'meta_key' => 'product_sku', 'meta_value' => $variation['sku'] ],
			[ 'order_item_id' => $item_id, 'meta_key' => 'product_type', 'meta_value' => $variation['type'] ],
			[ 'order_item_id' => $item_id, 'meta_key' => 'discount_amount', 'meta_value' => $this->faker->optional( 0.3 )->randomFloat( 2, 0, 50 ) ?: 0 ],
		];

		foreach ( $meta_entries as $meta ) {
			$this->wpdb->insert( $order_item_meta_table, $meta );
		}
	}

	private function addOrderMeta( $order_id, $customer, $total ): void {
		$order_meta_table = $this->wpdb->prefix . 'order_meta';

		// Get customer addresses
		$billing_address  = get_user_meta( $customer->ID, 'billing_address', true );
		$shipping_address = get_user_meta( $customer->ID, 'shipping_address', true );

		if ( ! $billing_address ) {
			$billing_address = [
				'first_name' => $customer->first_name,
				'last_name'  => $customer->last_name,
				'email'      => $customer->user_email,
				'phone'      => $this->faker->phoneNumber,
				'address_1'  => $this->faker->streetAddress,
				'city'       => $this->faker->city,
				'state'      => $this->faker->stateAbbr,
				'postcode'   => $this->faker->postcode,
				'country'    => 'US',
			];
		}

		if ( ! $shipping_address ) {
			$shipping_address = $billing_address;
		}

		// Generate applied coupons (optional)
		$coupons = [];
		if ( $this->faker->boolean( 30 ) ) {
			$coupon_ids = $this->getRandomCoupons();
			foreach ( $coupon_ids as $coupon_id ) {
				$coupons[] = [
					'coupon_id'       => $coupon_id,
					'discount_amount' => $this->faker->randomFloat( 2, 5, 50 ),
				];
			}
		}

		// Payment details
		$payment_details = [
			'transaction_id'   => $this->faker->uuid,
			'payment_status'   => $this->faker->randomElement( [ 'completed', 'pending', 'failed', 'refunded' ] ),
			'payment_date'     => $this->faker->dateTimeBetween( '-1 year', 'now' )->format( 'Y-m-d H:i:s' ),
			'gateway_response' => $this->faker->sentence(),
		];

		$meta_entries = [
			[ 'order_id' => $order_id, 'meta_key' => 'billing_address', 'meta_value' => serialize( $billing_address ) ],
			[ 'order_id' => $order_id, 'meta_key' => 'shipping_address', 'meta_value' => serialize( $shipping_address ) ],
			[ 'order_id' => $order_id, 'meta_key' => 'coupons', 'meta_value' => serialize( $coupons ) ],
			[ 'order_id' => $order_id, 'meta_key' => 'payment_details', 'meta_value' => serialize( $payment_details ) ],
			[ 'order_id' => $order_id, 'meta_key' => 'order_notes', 'meta_value' => $this->faker->optional( 0.4 )->sentence() ],
			[ 'order_id' => $order_id, 'meta_key' => 'shipping_method', 'meta_value' => $this->faker->randomElement( [ 'standard', 'express', 'overnight', 'pickup' ] ) ],
			[ 'order_id' => $order_id, 'meta_key' => 'tracking_number', 'meta_value' => $this->faker->optional( 0.6 )->regexify( '[A-Z]{2}[0-9]{10}' ) ],
			[ 'order_id' => $order_id, 'meta_key' => 'estimated_delivery', 'meta_value' => $this->faker->dateTimeBetween( 'now', '+2 weeks' )->format( 'Y-m-d' ) ],
			[ 'order_id' => $order_id, 'meta_key' => 'source', 'meta_value' => $this->faker->randomElement( [ 'website', 'mobile_app', 'phone', 'in_store' ] ) ],
			[ 'order_id' => $order_id, 'meta_key' => 'currency', 'meta_value' => 'USD' ],
			[ 'order_id' => $order_id, 'meta_key' => 'subtotal', 'meta_value' => number_format( $total * 0.85, 2, '.', '' ) ], // Approximate subtotal
			[ 'order_id' => $order_id, 'meta_key' => 'tax_amount', 'meta_value' => number_format( $total * 0.08, 2, '.', '' ) ],
			[ 'order_id' => $order_id, 'meta_key' => 'shipping_amount', 'meta_value' => number_format( $total * 0.07, 2, '.', '' ) ],
			[ 'order_id' => $order_id, 'meta_key' => 'discount_amount', 'meta_value' => ! empty( $coupons ) ? array_sum( array_column( $coupons, 'discount_amount' ) ) : 0 ],
		];

		foreach ( $meta_entries as $meta ) {
			$this->wpdb->insert( $order_meta_table, $meta );
		}
	}

	private function getRandomTaxClass() {
		$tax_classes_table = $this->wpdb->prefix . 'tax_classes';
		$tax_class         = $this->wpdb->get_var( "SELECT id FROM {$tax_classes_table} ORDER BY RAND() LIMIT 1" );

		if ( ! $tax_class ) {
			// Create a default tax class if none exist
			$this->wpdb->insert( $tax_classes_table, [
				'name'     => 'Standard',
				'rate'     => 8.00,
				'country'  => 'US',
				'state'    => '',
				'city'     => '',
				'postcode' => '',
			] );
			$tax_class = $this->wpdb->insert_id;
		}

		return $tax_class;
	}

	private function getTaxRate( $tax_class_id ): float {
		$tax_rates_table = $this->wpdb->prefix . 'tax_rates';
		$tax_rate        = $this->wpdb->get_var( $this->wpdb->prepare(
			"SELECT rate FROM {$tax_rates_table} WHERE tax_class_id = %d LIMIT 1",
			$tax_class_id
		) );

		return $tax_rate ? (float) $tax_rate / 100 : 0.08; // Default 8%
	}

	private function getRandomCoupons(): array {
		$coupons_table = $this->wpdb->prefix . 'coupons';
		$coupon_ids    = $this->wpdb->get_col( $this->wpdb->prepare(
			"SELECT id FROM {$coupons_table} WHERE status = 1 ORDER BY RAND() LIMIT %d",
			$this->faker->numberBetween( 1, 2 )
		) );

		return $coupon_ids;
	}

	private function updateCustomerStats( $customer_id, $order_total ): void {
		// Get current stats
		$total_orders = (int) get_user_meta( $customer_id, 'total_orders', true );
		$total_spent  = (float) get_user_meta( $customer_id, 'total_spent', true );

		// Update stats
		$new_total_orders        = $total_orders + 1;
		$new_total_spent         = $total_spent + $order_total;
		$new_average_order_value = $new_total_spent / $new_total_orders;

		update_user_meta( $customer_id, 'total_orders', $new_total_orders );
		update_user_meta( $customer_id, 'total_spent', number_format( $new_total_spent, 2, '.', '' ) );
		update_user_meta( $customer_id, 'average_order_value', number_format( $new_average_order_value, 2, '.', '' ) );
		update_user_meta( $customer_id, 'last_order_date', date( 'Y-m-d H:i:s' ) );

		// Update customer tier based on total spent
		$tier = 'bronze';
		if ( $new_total_spent >= 5000 ) {
			$tier = 'platinum';
		} elseif ( $new_total_spent >= 2000 ) {
			$tier = 'gold';
		} elseif ( $new_total_spent >= 500 ) {
			$tier = 'silver';
		}
		update_user_meta( $customer_id, 'loyalty_tier', $tier );

		// Award loyalty points (1 point per dollar)
		$current_points = (int) get_user_meta( $customer_id, 'loyalty_points', true );
		$points_earned  = floor( $order_total );
		update_user_meta( $customer_id, 'loyalty_points', $current_points + $points_earned );
	}
}
