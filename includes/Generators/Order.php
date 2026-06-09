<?php
/**
 * Order Generator.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Order as OrderModel;
use EasyCommerce\Models\Customer as CustomerModel;
use EasyCommerce\Models\Product_Variation;
use EasyCommerce\Models\Database;
use EasyCommerce\Models\Location;
use EasyCommerce\Models\Tax;
use WP_Error;

/**
 * Order Generator Class
 *
 * Generates fake order data for EasyCommerce
 *
 * @since 1.0.0
 */
class Order extends Generator {

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
	 * Load sample data for the current locale
	 *
	 * Loads locale-specific sample data for order generation including
	 * order statuses, payment methods, shipping methods, fulfillment statuses,
	 * sources, and weighted countries.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Sample data arrays for order generation.
	 */
	protected function load_sample_data(): array {
		return array(
			'order_statuses'       => $this->load_json_file( $this->get_sample_data_path( 'orders', 'order_statuses' ) ) ?? array(),
			'payment_methods'      => $this->load_json_file( $this->get_sample_data_path( 'orders', 'payment_methods' ) ) ?? array(),
			'shipping_methods'     => $this->load_json_file( $this->get_sample_data_path( 'orders', 'shipping_methods' ) ) ?? array(),
			'fulfillment_statuses' => $this->load_json_file( $this->get_sample_data_path( 'orders', 'fulfillment_statuses' ) ) ?? array(),
			'sources'              => $this->load_json_file( $this->get_sample_data_path( 'orders', 'sources' ) ) ?? array(),
			'weighted_countries'   => $this->load_json_file( $this->get_sample_data_path( 'orders', 'weighted_countries' ) ) ?? array(),
		);
	}

	/**
	 * Get supported data types for this generator.
	 *
	 * @return array Supported types
	 */
	public function get_supported_types(): array {
		return array(
			'orders' => __( 'Customer Orders with Items, Shipping, and Payment Details', 'easycommerce-fakerpress' ),
		);
	}

	/**
	 * Get generator description.
	 *
	 * @return string Description
	 */
	public function get_description(): string {
		return __( 'Generates realistic customer orders with multiple items, comprehensive metadata (addresses, payment methods, shipping, taxes), status tracking, and relationship management for testing ecommerce order processing systems.', 'easycommerce-fakerpress' );
	}

	/**
	 * Generate a single order
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Single order data, error, or false on failure.
	 */
	protected function generate_single_item() {
		// Check if EasyCommerce Order class exists.
		if ( ! class_exists( OrderModel::class ) ) {
			return new WP_Error( 'missing_model', __( 'EasyCommerce Order model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' ) );
		}

		$create_missing = isset( $this->generation_params['relationships']['create_missing'] )
			? (bool) $this->generation_params['relationships']['create_missing']
			: true;
		$include_meta   = isset( $this->generation_params['meta_options']['include_meta'] )
			? (bool) $this->generation_params['meta_options']['include_meta']
			: true;

		$customer   = $this->get_customer_for_order();
		$variations = $this->get_random_product_variations();

		if ( is_wp_error( $customer ) ) {
			return new WP_Error( 'no_customers', __( 'No customers found for order generation. Please create customers first.', 'easycommerce-fakerpress' ) );
		}

		if ( is_wp_error( $variations ) ) {
			return new WP_Error( 'no_variations', __( 'No product variations found for order generation. Please create products with variations first.', 'easycommerce-fakerpress' ) );
		}

		// Get the customer billing address early for tax calculation.
		$customer_model  = new CustomerModel( $customer['id'] );
		$billing_address = ! empty( $customer_model->get_billing_address() ) ? $customer_model->get_billing_address() : $this->generate_fallback_address( $customer );

		// Convert variations to order items format required by EasyCommerce.
		$order_items = $this->convert_variations_to_items( $variations, $billing_address );
		$subtotal    = $this->calculate_subtotal( $order_items );
		$order_meta  = $include_meta ? $this->generate_order_meta( $customer, $subtotal, $billing_address ) : array();
		$total       = $this->calculate_total( $subtotal, $order_meta );

		/**
		 * Filters the order data before creating the order.
		 *
		 * Allows developers to modify order data, items, customer, and metadata
		 * before the order is created in the database.
		 *
		 * @since 1.0.0
		 * @hook easycommerce_fakerpress_order_data_before_create
		 *
		 * @param array $order_data {
		 *     Order data array.
		 *
		 *     @type array  $customer    Customer data.
		 *     @type array  $variations  Product variations.
		 *     @type array  $order_items Order items.
		 *     @type float  $subtotal    Order subtotal.
		 *     @type array  $order_meta  Order metadata.
		 *     @type float  $total       Order total.
		 * }
		 */
		$order_data = apply_filters(
			'easycommerce_fakerpress_order_data_before_create',
			array(
				'customer'    => $customer,
				'variations'  => $variations,
				'order_items' => $order_items,
				'subtotal'    => $subtotal,
				'order_meta'  => $order_meta,
				'total'       => $total,
			)
		);

		// Extract filtered data.
		$customer    = $order_data['customer'];
		$variations  = $order_data['variations'];
		$order_items = $order_data['order_items'];
		$subtotal    = $order_data['subtotal'];
		$order_meta  = $order_data['order_meta'];
		$total       = $order_data['total'];

		// Prepare complete meta data including all order details.
		$complete_meta = array_merge(
			$order_meta,
			array(
				// Order amounts stored in meta.
				'subtotal'        => $subtotal,
				'tax_amount'      => isset( $order_meta['tax_details']['total'] ) ? $order_meta['tax_details']['total'] : 0,
				'shipping_amount' => ( isset( $order_meta['shipping_details']['cost'] ) ? $order_meta['shipping_details']['cost'] : 0 )
					+ ( isset( $order_meta['shipping_details']['insurance'] ) ? $order_meta['shipping_details']['insurance'] : 0 ),
				'discount_amount' => isset( $order_meta['coupon_details']['discount'] ) ? $order_meta['coupon_details']['discount'] : 0,
				'currency'        => 'USD', // Default currency, can be made configurable.

				// Order notes.
				'notes'           => ! empty( $order_meta['order_notes'] ) ? array(
					array(
						'note'       => $order_meta['order_notes'],
						'type'       => 'customer',
						'created_at' => current_time( 'Y-m-d H:i:s' ),
					),
				) : array(),

				// Applied coupons.
				'coupons'         => ! empty( $order_meta['coupon_details']['applied'] ) ? array_map(
					function ( $coupon ) {
						return array(
							'code'            => isset( $coupon['code'] ) ? $coupon['code'] : '',
							'discount_amount' => isset( $coupon['discount_applied'] ) ? $coupon['discount_applied'] : 0,
						);
					},
					$order_meta['coupon_details']['coupons']
				) : array(),
			)
		);

		// Use EasyCommerce Order model with correct data structure.
		$order   = new OrderModel();
		$created = $order->create(
			array(
				// Required fields for Order model.
				'customer_id'    => $customer['id'],
				'total'          => $total,
				'status'         => $this->generate_order_status(),
				'fulfill_status' => $this->generate_fulfillment_status(),
				'payment_method' => $this->generate_payment_method(),
				'items'          => $order_items,

				// All additional data goes in meta.
				'meta'           => $complete_meta,
			)
		);

		if ( ! $created ) {
			return new WP_Error( 'order_creation_failed', __( 'Failed to create order using EasyCommerce model.', 'easycommerce-fakerpress' ) );
		}

		// Update customer statistics.
		$this->update_customer_stats( $customer['id'], $total );

		$result = array(
			'id'               => $order->get_id(),
			'order_number'     => $order->get_order_number() ? $order->get_order_number() : 'ORD-' . $order->get_id(),
			'customer_id'      => $customer['id'],
			'status'           => $order->get_status(),
			'total'            => $total,
			'subtotal'         => $subtotal,
			'tax_amount'       => isset( $order_meta['tax_details']['total'] ) ? $order_meta['tax_details']['total'] : 0,
			'shipping_amount'  => ( isset( $order_meta['shipping_details']['cost'] ) ? $order_meta['shipping_details']['cost'] : 0 )
				+ ( isset( $order_meta['shipping_details']['insurance'] ) ? $order_meta['shipping_details']['insurance'] : 0 ),
			'discount_amount'  => isset( $order_meta['coupon_details']['discount'] ) ? $order_meta['coupon_details']['discount'] : 0,
			'currency'         => 'USD',
			'payment_method'   => isset( $order_meta['payment_details']['method'] ) ? $order_meta['payment_details']['method'] : '',
			'order_date'       => current_time( 'Y-m-d H:i:s' ),
			'items'            => $this->format_order_items_for_result( $order_items ),
			'billing_address'  => isset( $order_meta['addresses']['billing'] ) ? $order_meta['addresses']['billing'] : array(),
			'shipping_address' => isset( $order_meta['addresses']['shipping'] ) ? $order_meta['addresses']['shipping'] : array(),
			'notes'            => ! empty( $order_meta['order_notes'] ) ? array(
				array(
					'note'       => $order_meta['order_notes'],
					'type'       => 'customer',
					'created_at' => current_time( 'Y-m-d H:i:s' ),
				),
			) : array(),
			'coupons'          => ! empty( $order_meta['coupon_details']['applied'] ) ? array_map(
				function ( $coupon ) {
					return array(
						'code'            => isset( $coupon['code'] ) ? $coupon['code'] : '',
						'discount_amount' => isset( $coupon['discount_applied'] ) ? $coupon['discount_applied'] : 0,
					);
				},
				$order_meta['coupon_details']['coupons']
			) : array(),
		);

		/**
		 * Filters the order generation result data.
		 *
		 * Allows developers to modify the returned order data after generation.
		 *
		 * @since 1.0.0
		 * @hook easycommerce_fakerpress_order_generation_result
		 *
		 * @param array $result     The order generation result data.
		 * @param int   $order_id   The created order ID.
		 * @param array $order_data The original order data used for creation.
		 */
		$result = apply_filters( 'easycommerce_fakerpress_order_generation_result', $result, $order->get_id(), $order_data );

		/**
		 * Fires after an order has been successfully created.
		 *
		 * Allows developers to perform additional operations after order creation,
		 * such as adding custom metadata, triggering related processes, or logging.
		 *
		 * @since 1.0.0
		 * @hook easycommerce_fakerpress_after_order_created
		 *
		 * @param int   $order_id    The created order ID.
		 * @param array $result      The order generation result data.
		 * @param array $order_data  The original order data used for creation.
		 */
		do_action( 'easycommerce_fakerpress_after_order_created', $order->get_id(), $result, $order_data );

		return $result;
	}

	/**
	 * Return column definitions for the order preview table.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, array{key: string, label: string}>
	 */
	protected function get_preview_columns(): array {
		return array(
			array(
				'key'   => 'id',
				'label' => __( 'Order', 'easycommerce-fakerpress' ),
			),
			array(
				'key'   => 'cust',
				'label' => __( 'Customer', 'easycommerce-fakerpress' ),
			),
			array(
				'key'   => 'status',
				'label' => __( 'Status', 'easycommerce-fakerpress' ),
			),
			array(
				'key'   => 'items',
				'label' => __( 'Items', 'easycommerce-fakerpress' ),
			),
			array(
				'key'   => 'total',
				'label' => __( 'Total', 'easycommerce-fakerpress' ),
			),
			array(
				'key'   => 'geo',
				'label' => __( 'Country', 'easycommerce-fakerpress' ),
			),
		);
	}

	/**
	 * Build a single order preview row without any DB writes.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array{v: mixed, kind: string}>
	 */
	protected function build_preview_row(): array {
		$statuses  = array( 'pending', 'processing', 'completed', 'cancelled', 'on_hold', 'refunded' );
		$countries = array( 'US', 'CA', 'GB', 'AU', 'DE', 'FR', 'IT', 'ES', 'JP', 'IN', 'BR', 'MX' );

		$order_id = $this->get_faker()->numberBetween( 10000, 99999 );
		$total    = $this->get_faker()->randomFloat( 2, 10, 1500 );

		return array(
			'id'     => array(
				'v'    => '#' . $order_id,
				'kind' => 'mono',
			),
			'cust'   => array(
				'v'    => $this->get_faker()->name,
				'kind' => 'text',
			),
			'status' => array(
				'v'    => $this->get_faker()->randomElement( $statuses ),
				'kind' => 'status',
			),
			'items'  => array(
				'v'    => $this->get_faker()->numberBetween( 1, 8 ),
				'kind' => 'num',
			),
			'total'  => array(
				'v'    => '$' . number_format( $total, 2 ),
				'kind' => 'money',
			),
			'geo'    => array(
				'v'    => $this->get_faker()->randomElement( $countries ),
				'kind' => 'mono',
			),
		);
	}

	/**
	 * Get customer for order based on generation parameters
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|array Customer data or false if none found.
	 */
	private function get_customer_for_order() {
		$customer_type        = isset( $this->generation_params['customer_type'] ) ? $this->generation_params['customer_type'] : 'mixed';
		$specific_customer_id = isset( $this->generation_params['specific_customer_id'] ) ? $this->generation_params['specific_customer_id'] : null;
		$create_missing       = isset( $this->generation_params['relationships']['create_missing'] )
			? (bool) $this->generation_params['relationships']['create_missing']
			: true;

		switch ( $customer_type ) {
			case 'existing':
				return $this->get_random_customer();

			case 'new':
				if ( ! $create_missing ) {
					return $this->get_random_customer();
				}
				return $this->create_new_customer();

			case 'specific':
				if ( $specific_customer_id ) {
					return $this->get_specific_customer( $specific_customer_id );
				}

				// Fallback to random if no specific ID provided.
				return $this->get_random_customer();

			case 'mixed':
			default:
				if ( ! $create_missing ) {
					return $this->get_random_customer();
				}
				// 70% existing customers, 30% new customers for realistic distribution.
				return $this->get_faker()->boolean( 70 ) ? $this->get_random_customer() : $this->create_new_customer();
		}
	}

	/**
	 * Get a random customer for order generation using EasyCommerce Customer model
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|array Random customer user object or false if none found.
	 */
	private function get_random_customer() {
		// Use EasyCommerce Customer model's customer_list method to get customers with proper capabilities.
		$customer_data = CustomerModel::customer_list( null, 1, 50 );
		$customers     = $customer_data['users'] ?? array();

		if ( empty( $customers ) ) {
			return new WP_Error( 'no_customers', __( 'No customers found.', 'easycommerce-fakerpress' ) );
		}

		// Use faker to randomly select from available customers.
		return $this->get_faker()->randomElement( $customers );
	}

	/**
	 * Get a specific customer by ID
	 *
	 * @since 1.0.0
	 *
	 * @param int $customer_id Customer ID.
	 *
	 * @return WP_Error|array Customer data or false if not found.
	 */
	private function get_specific_customer( int $customer_id ) {
		$customer = new CustomerModel( $customer_id );
		if ( $customer->get_id() && $customer->get_id() > 0 ) {
			return array(
				'id'         => $customer->get_id(),
				'name'       => $customer->get_name(),
				'email'      => $customer->get_email(),
				'first_name' => $customer->get_first_name(),
				'last_name'  => $customer->get_last_name(),
				'role'       => $customer->get_role(),
			);
		}

		return new WP_Error( 'no_customers', __( 'No customers found.', 'easycommerce-fakerpress' ) );
	}

	/**
	 * Create a new customer for order generation
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|array New customer data or false on failure.
	 */
	private function create_new_customer() {
		// Use Customer_Generator to create a new customer.
		$customer_generator = new Customer();

		$customer_generator->set_locale( $this->get_faker_locale() );
		$customer_generator->set_faker();

		$customer = $customer_generator->generate_single_item();

		if ( is_wp_error( $customer ) ) {
			return $customer;
		}

		// Return customer in expected format.
		return array(
			'id'         => $customer['id'],
			'name'       => $customer['name'],
			'email'      => $customer['email'],
			'first_name' => $customer['first_name'] ?? '',
			'last_name'  => $customer['last_name'] ?? '',
			'role'       => 'customer',
		);
	}

	/**
	 * Get random product variations for order items using EasyCommerce models
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|array Array of Product_Variation objects.
	 */
	private function get_random_product_variations() {
		// Get a larger pool of available variations to choose from.
		$db              = new Database( 'product_variations' );
		$variations_data = $db->get_rows(
			array( 'status' => 'active' ),
			100, // Get more variations for better randomization.
			0,
			'RAND()'
		);

		if ( empty( $variations_data ) ) {
			return new WP_Error( 'no_variations', __( 'No variations found.', 'easycommerce-fakerpress' ) );
		}

		// Use faker to randomly select variations (1-5 items per order).
		$selected_count           = $this->get_faker()->numberBetween( 1, 5 );
		$selected_variations_data = $this->get_faker()->randomElements(
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
	 * @param array $billing_address Billing address for tax calculation.
	 *
	 * @return array Order items formatted for EasyCommerce Order model.
	 */
	private function convert_variations_to_items( array $variations, array $billing_address ): array {
		$order_items = array();

		foreach ( $variations as $variation ) {
			if ( ! $variation->exists() ) {
				continue;
			}

			$product_id   = $variation->get_product_id();
			$price_id     = $variation->get_price_id();
			$quantity     = $this->get_faker()->numberBetween( 1, 3 );
			$rate         = $variation->get_regular_price();
			$subtotal     = $rate * $quantity;
			$tax_class_id = $variation->get_tax_class();

			if ( ! isset( $order_items[ $product_id ] ) ) {
				$order_items[ $product_id ] = array();
			}

			// Enhanced metadata for order items.
			$item_meta = $this->generate_order_item_meta( $variation, $quantity, $rate, $subtotal );

			$order_items[ $product_id ][ $price_id ] = array(
				'quantity'     => $quantity,
				'rate'         => $rate,
				'price'        => $subtotal,
				'tax_class_id' => $tax_class_id,
				'tax_rate'     => $this->generate_item_tax_rate( $tax_class_id, $billing_address ),
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
	 * @param array $customer Customer user object.
	 * @param float $subtotal Order subtotal.
	 * @param array $billing_address Billing address (already retrieved).
	 *
	 * @return array Order metadata.
	 */
	private function generate_order_meta( array $customer, float $subtotal, array $billing_address ): array {
		$customer_model   = new CustomerModel( $customer['id'] );
		$shipping_address = ! empty( $customer_model->get_shipping_address() ) ? $customer_model->get_shipping_address() : $billing_address;

		return array(
			'addresses'        => array(
				'billing'  => $billing_address,
				'shipping' => $shipping_address,
			),
			'payment_details'  => $this->generate_payment_details(),
			'shipping_details' => $this->generate_shipping_details( $subtotal ),
			'tax_details'      => $this->generate_tax_details( $subtotal ),
			'coupon_details'   => $this->generate_coupon_details( $subtotal ),
			'order_notes'      => $this->get_faker()->optional( 0.3 )->paragraph( 2 ),
			'source_info'      => $this->generate_source_info(),
			'fulfillment'      => array(
				'status'             => $this->generate_fulfillment_status(),
				'tracking_number'    => $this->get_faker()->optional( 0.6 )->regexify( '[A-Z]{2}[0-9]{10}' ),
				'estimated_delivery' => $this->get_faker()->dateTimeBetween( 'now', '+2 weeks' )->format( 'Y-m-d' ),
				'carrier'            => $this->get_faker()->randomElement(
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
		$allowed = isset( $this->generation_params['order_status'] ) && is_array( $this->generation_params['order_status'] )
			? $this->generation_params['order_status']
			: array();

		if ( ! empty( $allowed ) ) {
			return $this->get_faker()->randomElement( $allowed );
		}

		$sample_data = $this->load_sample_data();
		$statuses    = $sample_data['order_statuses'] ? $sample_data['order_statuses'] : array(
			'pending'    => 25,
			'processing' => 35,
			'completed'  => 30,
			'cancelled'  => 5,
			'on_hold'    => 3,
			'refunded'   => 2,
		);

		return $this->get_faker()->randomElement(
			array_merge(
				...array_map(
					static function ( $status, $weight ) {
						return array_fill( 0, $weight, $status );
					},
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
		$allowed = isset( $this->generation_params['payment_methods'] ) && is_array( $this->generation_params['payment_methods'] )
			? $this->generation_params['payment_methods']
			: array();

		if ( ! empty( $allowed ) ) {
			return $this->get_faker()->randomElement( $allowed );
		}

		$sample_data = $this->load_sample_data();
		$methods     = $sample_data['payment_methods'] ? $sample_data['payment_methods'] : array(
			'stripe'           => 40,
			'paypal'           => 25,
			'bank_transfer'    => 15,
			'cash_on_delivery' => 10,
			'credit_card'      => 10,
		);

		return $this->get_faker()->randomElement(
			array_merge(
				...array_map(
					static function ( $method, $weight ) {
						return array_fill( 0, $weight, $method );
					},
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
			'status'           => $this->get_faker()->randomElement( array( 'completed', 'pending', 'failed', 'refunded' ) ),
			'transaction_id'   => $this->get_faker()->uuid,
			'payment_date'     => $this->get_faker()->dateTimeBetween( '-30 days', 'now' )->format( 'Y-m-d H:i:s' ),
			'gateway_response' => $this->get_faker()->sentence(),
		);

		// Add method-specific details.
		switch ( $payment_method ) {
			case 'stripe':
				$details['stripe_charge_id'] = 'ch_' . $this->get_faker()->regexify( '[a-zA-Z0-9]{24}' );
				$details['last4']            = $this->get_faker()->numerify( '####' );
				$details['brand']            = $this->get_faker()->randomElement(
					array(
						'visa',
						'mastercard',
						'amex',
						'discover',
					)
				);
				break;
			case 'paypal':
				$details['paypal_transaction_id'] = $this->get_faker()->regexify( '[A-Z0-9]{15}' );
				$details['payer_email']           = $this->get_faker()->email;
				break;
			case 'bank_transfer':
				$details['bank_reference'] = $this->get_faker()->regexify( '[A-Z0-9]{10}' );
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
		$sample_data      = $this->load_sample_data();
		$shipping_methods = $sample_data['shipping_methods'] ? $sample_data['shipping_methods'] : array(
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

		$method = $this->get_faker()->randomElement(
			array_merge(
				...array_map(
					static fn( $method, $details ) => array_fill( 0, $details['weight'], $method ),
					array_keys( $shipping_methods ),
					$shipping_methods
				)
			)
		);

		$method_details = $shipping_methods[ $method ];
		$cost           = $method_details['min'] === $method_details['max']
			? $method_details['min']
			: $this->get_faker()->randomFloat( 2, $method_details['min'], $method_details['max'] );

		// Free shipping for orders over $100.
		if ( 100 < $subtotal && 'overnight' !== $method ) {
			$cost = 0;
		}

		return array(
			'method'             => $method,
			'cost'               => $cost,
			'estimated_days'     => $method_details['days'],
			'insurance'          => $this->get_faker()->boolean( 20 ) ? $this->get_faker()->randomFloat( 2, 2.99, 9.99 ) : 0,
			'signature_required' => $this->get_faker()->boolean( 15 ),
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
			'exempt'    => $this->get_faker()->boolean( 5 ), // 5% tax exempt
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
		if ( ! $this->get_faker()->boolean( 25 ) ) {
			return array(
				'applied'  => false,
				'discount' => 0,
				'coupons'  => array(),
			);
		}

		$coupons        = $this->get_random_coupons();
		$total_discount = 0;

		foreach ( $coupons as $coupon ) {
			if ( 'percentage' === $coupon['type'] ) {
				$discount = $subtotal * ( $coupon['offer'] / 100 );
			} else {
				$discount = min( $coupon['offer'], $subtotal );
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
		$sample_data = $this->load_sample_data();
		$statuses    = $sample_data['fulfillment_statuses'] ? $sample_data['fulfillment_statuses'] : array(
			'unfulfilled'         => 30,
			'partially_fulfilled' => 10,
			'fulfilled'           => 25,
			'shipped'             => 20,
			'delivered'           => 10,
			'returned'            => 3,
			'cancelled'           => 2,
		);

		return $this->get_faker()->randomElement(
			array_merge(
				...array_map(
					static fn( $status, $weight ) => array_fill( 0, $weight, $status ),
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
		$sample_data = $this->load_sample_data();
		$sources     = $sample_data['sources'] ? $sample_data['sources'] : array(
			'website'    => 60,
			'mobile_app' => 25,
			'phone'      => 10,
			'in_store'   => 5,
		);

		$source = $this->get_faker()->randomElement(
			array_merge(
				...array_map(
					static fn( $src, $weight ) => array_fill( 0, $weight, $src ),
					array_keys( $sources ),
					$sources
				)
			)
		);

		return array(
			'channel'      => $source,
			'user_agent'   => $this->get_faker()->userAgent,
			'ip_address'   => $this->get_faker()->ipv4,
			'referrer'     => $this->get_faker()->optional( 0.4 )->url,
			'utm_source'   => $this->get_faker()->optional( 0.3 )->randomElement(
				array( 'google', 'facebook', 'email', 'direct' )
			),
			'utm_medium'   => $this->get_faker()->optional( 0.3 )->randomElement(
				array( 'cpc', 'social', 'email', 'organic' )
			),
			'utm_campaign' => $this->get_faker()->optional( 0.2 )->words( 2, true ),
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
		$shipping = ( isset( $order_meta['shipping_details']['cost'] ) ? $order_meta['shipping_details']['cost'] : 0 )
			+ ( isset( $order_meta['shipping_details']['insurance'] ) ? $order_meta['shipping_details']['insurance'] : 0 );
		$tax      = isset( $order_meta['tax_details']['total'] ) ? $order_meta['tax_details']['total'] : 0;
		$discount = isset( $order_meta['coupon_details']['discount'] ) ? $order_meta['coupon_details']['discount'] : 0;

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
		foreach ( $order_items as $variations ) {
			foreach ( $variations as $item ) {
				$count += $item['quantity'];
			}
		}

		return $count;
	}

	/**
	 * Format order items for result array
	 *
	 * @since 1.0.0
	 *
	 * @param array $order_items Order items array.
	 *
	 * @return array Formatted order items.
	 */
	private function format_order_items_for_result( array $order_items ): array {
		$formatted_items = array();

		foreach ( $order_items as $product_id => $variations ) {
			foreach ( $variations as $price_id => $item ) {
				$formatted_items[] = array(
					'product_id'   => $product_id,
					'variation_id' => $price_id,
					'quantity'     => $item['quantity'],
					'price'        => $item['rate'],
					'total'        => $item['price'],
				);
			}
		}

		return $formatted_items;
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
		$sample_data        = $this->load_sample_data();
		$weighted_countries = $sample_data['weighted_countries'] ? $sample_data['weighted_countries'] : array(
			'US' => 60, // 60% US.
			'CA' => 15, // 15% Canada.
			'GB' => 10, // 10% UK.
			'AU' => 8,  // 8% Australia.
			'DE' => 4,  // 4% Germany.
			'FR' => 3,  // 3% France.
		);

		$country_code = $this->get_faker()->randomElement(
			array_merge(
				...array_map(
					static fn( $code, $weight ) => array_fill( 0, $weight, $code ),
					array_keys( $weighted_countries ),
					$weighted_countries
				)
			)
		);

		// Get states for the selected country.
		$states     = Location::get_states( $country_code );
		$state_data = $this->get_faker()->randomElement( $states );
		$state_name = $state_data['name'] ?? $this->get_faker()->state;
		$state_code = $state_data['state_code'] ?? $this->get_faker()->stateAbbr;

		// Get cities for the selected state.
		$cities    = Location::get_cities( $country_code, $state_code );
		$city_data = $this->get_faker()->randomElement( $cities );
		$city_name = $city_data['name'] ?? $this->get_faker()->city;

		// Get country details.
		$country_data = array_filter( $countries, fn( $c ) => $c['iso2'] === $country_code );
		$country_info = reset( $country_data );
		$country_name = $country_info['name'] ?? $country_code;
		$phone_code   = $country_info['phone_code'] ?? '+1';

		return array(
			'first_name'   => get_user_meta( $customer['id'], 'first_name', true ) ?? $this->get_faker()->firstName,
			'last_name'    => get_user_meta( $customer['id'], 'last_name', true ) ?? $this->get_faker()->lastName,
			'email'        => $customer['email'],
			'phone'        => $phone_code . ' ' . $this->generate_phone_for_country( $country_code ),
			'company'      => $this->get_faker()->optional( 0.3 )->company,
			'address_1'    => $this->get_faker()->streetAddress,
			'address_2'    => $this->get_faker()->optional( 0.3 )->secondaryAddress,
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
		$selected_count   = $this->get_faker()->numberBetween( 1, 2 );
		$selected_coupons = $this->get_faker()->randomElements(
			$coupons_data,
			min( $selected_count, count( $coupons_data ) )
		);

		return array_map(
			static fn( $coupon ) => (array) $coupon,
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
			'weight'             => $this->get_faker()->randomFloat( 2, 0.1, 5.0 ),
			'dimensions'         => array(
				'length' => $this->get_faker()->randomFloat( 2, 1, 20 ),
				'width'  => $this->get_faker()->randomFloat( 2, 1, 20 ),
				'height' => $this->get_faker()->randomFloat( 2, 1, 20 ),
			),

			// Fulfillment details.
			'requires_shipping'  => $this->get_faker()->boolean( 85 ), // 85% require shipping.
			'is_virtual'         => $this->get_faker()->boolean( 15 ), // 15% virtual products.
			'is_downloadable'    => $this->get_faker()->boolean( 10 ), // 10% downloadable.
			'download_limit'     => $this->get_faker()->optional( 0.3 )->numberBetween( 1, 10 ),
			'download_expiry'    => $this->get_faker()->optional( 0.3 )->numberBetween( 1, 365 ),

			// Tax information.
			'tax_class'          => $variation->get_tax_class(),
			'tax_status'         => $this->get_faker()->randomElement( array( 'taxable', 'shipping', 'none' ) ),

			// Inventory tracking.
			'stock_quantity'     => $variation->get_stock(),
			'low_stock_amount'   => $this->get_faker()->numberBetween( 1, 10 ),
			'backorders'         => $this->get_faker()->randomElement( array( 'no', 'notify', 'yes' ) ),

			// Additional metadata.
			'purchase_note'      => $this->get_faker()->optional( 0.2 )->sentence(),
			'warranty_info'      => $this->get_faker()->optional( 0.3 )->sentence(),
			'return_policy'      => $this->get_faker()->optional( 0.2 )->sentence(),
		);
	}

	/**
	 * Generate tax rate for order item using location-based tax lookup
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $tax_class_id Tax class ID from product variation.
	 * @param array    $billing_address Billing address for location-based lookup.
	 *
	 * @return float Tax rate for the item.
	 */
	private function generate_item_tax_rate( $tax_class_id, array $billing_address ): float {
		// If no tax class ID, return 0 (tax-free).
		if ( ! $tax_class_id ) {
			return 0.00;
		}

		// Try to get real tax rate based on location.
		$tax_model = new Tax();
		$country   = $billing_address['country'] ?? '';
		$state     = $billing_address['state'] ?? '';
		$city      = $billing_address['city'] ?? '';

		if ( $country ) {
			$tax_rate = $tax_model->get_rate_by_location( $tax_class_id, $country, $state, $city );

			// If we found a rate, use it.
			if ( $tax_rate > 0 ) {
				return $tax_rate;
			}
		}

		// Fallback to realistic random tax rates if no location-based rate found.
		$fallback_tax_rates = array(
			0.00,  // Tax-free.
			5.00,  // Low tax.
			6.00,  // Average.
			7.00,  // Above average.
			7.25,  // CA base.
			8.00,  // Common.
			8.25,  // High.
			8.875, // NY.
			10.00, // Very high.
		);

		return $this->get_faker()->randomElement( $fallback_tax_rates );
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
			'first_name' => get_user_meta( $customer['id'], 'first_name', true ) ?? $this->get_faker()->firstName,
			'last_name'  => get_user_meta( $customer['id'], 'last_name', true ) ?? $this->get_faker()->lastName,
			'email'      => $customer['email'],
			'phone'      => $this->get_faker()->phoneNumber,
			'company'    => $this->get_faker()->optional( 0.3 )->company,
			'address_1'  => $this->get_faker()->streetAddress,
			'address_2'  => $this->get_faker()->optional( 0.3 )->secondaryAddress,
			'city'       => $this->get_faker()->city,
			'state'      => $this->get_faker()->stateAbbr,
			'country'    => 'US',
			'postcode'   => $this->get_faker()->postcode,
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
				return $this->get_faker()->regexify( '\([0-9]{3}\) [0-9]{3}-[0-9]{4}' );
			case 'GB':
				return $this->get_faker()->regexify( '[0-9]{4} [0-9]{3} [0-9]{4}' );
			case 'AU':
				return $this->get_faker()->regexify( '[0-9]{4} [0-9]{3} [0-9]{3}' );
			case 'DE':
				return $this->get_faker()->regexify( '[0-9]{3} [0-9]{3} [0-9]{4}' );
			case 'FR':
				return $this->get_faker()->regexify( '[0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2}' );
			default:
				return $this->get_faker()->phoneNumber;
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
				return $this->get_faker()->regexify( '[0-9]{5}(-[0-9]{4})?' );
			case 'CA':
				return $this->get_faker()->regexify( '[A-Z][0-9][A-Z] [0-9][A-Z][0-9]' );
			case 'GB':
				return $this->get_faker()->regexify( '[A-Z]{1,2}[0-9]{1,2}[A-Z]? [0-9][A-Z]{2}' );
			case 'AU':
				return $this->get_faker()->regexify( '[0-9]{4}' );
			case 'DE':
			case 'FR':
				return $this->get_faker()->regexify( '[0-9]{5}' );
			default:
				return $this->get_faker()->postcode;
		}
	}
}
