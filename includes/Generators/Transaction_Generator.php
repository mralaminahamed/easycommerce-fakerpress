<?php
/**
 * Transaction Generator Class for EasyCommerce FakerPress Plugin
 *
 * @since 2.0.0
 * @subpackage Generators
 * @package EasyCommerceFakerPress
 */

namespace EasyCommerceFakerPress\Generators;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Models\Database;
use EasyCommerce\Models\Order;
use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Transaction;
use Exception;
use RuntimeException;
use WP_Error;

/**
 * Transaction Generator Class
 *
 * Generates realistic payment transaction history with various transaction types and statuses.
 */
class Transaction_Generator extends Generator {

	/**
	 * Generation parameters from REST API
	 *
	 * @var array
	 */
	private array $generation_params = array();

	/**
	 * Set generation parameters
	 *
	 * @since 2.0.0
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
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'transaction';
	}

	/**
	 * Generate a single transaction
	 *
	 * @return WP_Error|array|bool Single transaction data, error, or false on failure.
	 * @throws RuntimeException When no orders are found.
	 */
	protected function generate_single_item() {
		// Check if EasyCommerce Transaction class exists.
		if ( ! class_exists( Transaction::class ) ) {
			return new WP_Error( 'missing_model', __( 'EasyCommerce Transaction model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' ) );
		}

		// Get orders based on customer parameters.
		$orders = $this->get_orders_for_transactions();

		if ( empty( $orders ) ) {
			return new WP_Error( 'no_orders', __( 'No orders found. Please generate orders first.', 'easycommerce-fakerpress' ) );
		}

		$order            = $this->get_faker()->randomElement( $orders );
		$transaction_data = $this->generate_transaction_data( $order );
		$transaction_id   = $this->create_transaction( $transaction_data );

		if ( ! $transaction_id ) {
			return new WP_Error( 'transaction_creation_failed', __( 'Failed to create transaction.', 'easycommerce-fakerpress' ) );
		}

		return array(
			'id'              => $transaction_id,
			'order_id'        => $transaction_data['order_id'],
			'customer_id'     => $transaction_data['customer_id'],
			'transaction_id'  => $transaction_data['transaction_id'],
			'payment_gateway' => $transaction_data['payment_gateway'],
			'amount'          => $transaction_data['amount'],
			'currency'        => $transaction_data['currency'],
			'status'          => $transaction_data['status'],
			'type'            => $transaction_data['type'],
		);
	}

	/**
	 * Get orders for transaction generation based on customer parameters
	 *
	 * @since 2.0.0
	 *
	 * @return array Array of orders.
	 */
	private function get_orders_for_transactions(): array {
		$customer_type        = $this->generation_params['customer_type'] ?? 'all';
		$specific_customer_id = $this->generation_params['specific_customer_id'] ?? null;
		$order_status_filter  = $this->generation_params['order_status_filter'] ?? array();

		$query_params = array( 'per_page' => 100 );

		// Add status filter if specified.
		if ( ! empty( $order_status_filter ) ) {
			$query_params['status'] = $order_status_filter;
		}

		// Add customer filter based on type.
		switch ( $customer_type ) {
			case 'specific':
				if ( $specific_customer_id ) {
					$query_params['customer_id'] = $specific_customer_id;
				}
				break;

			case 'existing_customers_only':
				// Only orders from customers who have more than 1 order.
				$query_params['customer_type'] = 'returning';
				break;

			case 'new_customers_only':
				// Only orders from first-time customers.
				$query_params['customer_type'] = 'new';
				break;

			case 'all':
			default:
				// No additional customer filtering.
				break;
		}

		$orders_data = Order::list( $query_params );

		return $orders_data['orders'] ?? array();
	}

	/**
	 * Generate multiple transactions.
	 *
	 * @param int   $count Number of transactions to generate.
	 * @param array $args Additional arguments.
	 *
	 * @return array Generated transaction data
	 */
	public function generate_multiple( int $count = 20, array $args = array() ): array {
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
	 * Generate transaction data.
	 *
	 * @param array $order Order data.
	 *
	 * @return array Transaction data
	 */
	private function generate_transaction_data( $order ): array {
		$transaction_types = array( 'payment', 'refund', 'adjustment', 'fee', 'commission' );
		$transaction_type  = $this->get_faker()->randomElement( $transaction_types );

		$payment_gateways = array(
			'stripe'        => 'Stripe',
			'paypal'        => 'PayPal',
			'square'        => 'Square',
			'authorize_net' => 'Authorize.Net',
			'braintree'     => 'Braintree',
			'razorpay'      => 'Razorpay',
			'mollie'        => 'Mollie',
			'wepay'         => 'WePay',
			'2checkout'     => '2Checkout',
			'payu'          => 'PayU',
		);

		$gateway_key     = $this->get_faker()->randomElement( array_keys( $payment_gateways ) );
		$payment_gateway = $payment_gateways[ $gateway_key ];

		// Generate transaction amount based on type.
		$amount = $this->generate_transaction_amount( $order['total'], $transaction_type );

		return array(
			'order_id'        => $order['id'],
			'customer_id'     => $order['customer'],
			'transaction_id'  => $this->generate_transaction_id( $gateway_key ),
			'payment_gateway' => $payment_gateway,
			'amount'          => $amount,
			'currency'        => $this->get_faker()->randomElement( array( 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'INR' ) ),
			'status'          => $this->generate_transaction_status( $transaction_type ),
			'type'            => $transaction_type,
		);
	}

	/**
	 * Generate transaction amount based on type.
	 *
	 * @param float  $order_total Order total amount.
	 * @param string $type Transaction type.
	 *
	 * @return float Transaction amount
	 */
	private function generate_transaction_amount( float $order_total, string $type ): float {
		switch ( $type ) {
			case 'payment':
				// Full payment or partial payment.
				return $this->get_faker()->boolean( 80 )
					? $order_total
					: $this->get_faker()->randomFloat( 2, $order_total * 0.1, $order_total * 0.9 );

			case 'refund':
				// Partial or full refund.
				return $this->get_faker()->boolean( 60 )
					? $order_total * $this->get_faker()->randomFloat( 2, 0.1, 0.5 )
					: $order_total;

			case 'adjustment':
				// Small positive or negative adjustment.
				return $this->get_faker()->randomFloat( 2, - 50, 50 );

			case 'fee':
				// Processing or transaction fees.
				return $this->get_faker()->randomFloat( 2, 0.99, $order_total * 0.05 );

			case 'commission':
				// Commission amounts.
				return $this->get_faker()->randomFloat( 2, $order_total * 0.02, $order_total * 0.15 );

			default:
				return $order_total;
		}
	}

	/**
	 * Generate transaction status based on type.
	 *
	 * @param string $type Transaction type.
	 *
	 * @return string Transaction status
	 */
	private function generate_transaction_status( string $type ): string {
		$status_weights = array(
			'payment'    => array(
				'completed' => 70,
				'pending'   => 15,
				'failed'    => 10,
				'cancelled' => 5,
			),
			'refund'     => array(
				'completed' => 80,
				'pending'   => 15,
				'failed'    => 5,
			),
			'adjustment' => array(
				'completed' => 90,
				'pending'   => 10,
			),
			'fee'        => array(
				'completed' => 95,
				'pending'   => 5,
			),
			'commission' => array(
				'completed' => 85,
				'pending'   => 15,
			),
		);

		$statuses = $status_weights[ $type ] ?? array( 'completed' => 100 );

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
	 * Generate realistic transaction ID based on gateway.
	 *
	 * @param string $gateway Payment gateway key.
	 *
	 * @return string Transaction ID
	 */
	private function generate_transaction_id( string $gateway ): string {
		switch ( $gateway ) {
			case 'stripe':
				return 'ch_' . $this->get_faker()->regexify( '[a-zA-Z0-9]{24}' );

			case 'paypal':
				return $this->faker->regexify( '[A-Z0-9]{17}' );

			case 'square':
				return $this->faker->regexify( '[a-zA-Z0-9\-]{22}' );

			case 'wepay':
			case '2checkout':
			case 'authorize_net':
				return $this->faker->numerify( '##########' );

			case 'braintree':
				return $this->faker->regexify( '[a-z0-9]{8}' );

			case 'razorpay':
				return 'pay_' . $this->faker->regexify( '[a-zA-Z0-9]{14}' );

			case 'mollie':
				return 'tr_' . $this->faker->regexify( '[a-zA-Z0-9]{10}' );

			case 'payu':
				return $this->faker->regexify( '[A-Z0-9]{15}' );

			default:
				return $this->faker->regexify( '[A-Z0-9]{12}' );
		}
	}

	/**
	 * Create transaction in database.
	 *
	 * @param array $data Transaction data.
	 *
	 * @return int|null Created transaction ID
	 */
	private function create_transaction( array $data ): ?int {
		$transaction = new Transaction();

		// Enable order status updates for completed payment transactions.
		$should_update_status = ( 'payment' === $data['type'] && 'completed' === $data['status'] );

		$transaction_id = $transaction->add( $data['order_id'], $data, $should_update_status );

		return $transaction_id ?? null;
	}

	/**
	 * Generate transaction history for specific order.
	 *
	 * @param int $order_id Order ID.
	 * @param int $transaction_count Number of transactions to generate.
	 *
	 * @return WP_Error|array Generated transactions.
	 * @throws RuntimeException When order is not found.
	 */
	public function generate_for_order( int $order_id, int $transaction_count = 3 ): array {
		$order_db   = new Database( 'orders' );
		$order_data = $order_db->get_by_id( $order_id );

		if ( ! $order_data ) {
			return new WP_Error( 'order_not_found', __( 'Order not found.', 'easycommerce-fakerpress' ) );
		}

		$results          = array();
		$remaining_amount = $order_data->total;

		for ( $i = 0; $i < $transaction_count; $i++ ) {
			try {
				// First transaction is usually a payment.
				$transaction_type = ( 0 === $i ) ? 'payment' : $this->faker->randomElement(
					array(
						'payment',
						'refund',
						'adjustment',
						'fee',
					)
				);

				$transaction_data         = $this->generate_transaction_data( (array) $order_data );
				$transaction_data['type'] = $transaction_type;

				// Adjust amount for subsequent transactions.
				if ( $i > 0 && 'payment' === $transaction_type ) {
					$transaction_data['amount'] = min( $remaining_amount, $transaction_data['amount'] );
				}

				$transaction_id = $this->create_transaction( $transaction_data );

				if ( $transaction_id ) {
					$results[] = array(
						'id'              => $transaction_id,
						'order_id'        => $transaction_data['order_id'],
						'transaction_id'  => $transaction_data['transaction_id'],
						'amount'          => $transaction_data['amount'],
						'type'            => $transaction_data['type'],
						'status'          => $transaction_data['status'],
						'payment_gateway' => $transaction_data['payment_gateway'],
					);

					$remaining_amount -= $transaction_data['amount'];
				}
			} catch ( Exception $e ) {
				$this->log( "Failed to generate transaction {$i} for order {$order_id}: " . $e->getMessage(), 'error' );
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
			'transactions' => 'Payment Transaction History',
		);
	}

	/**
	 * Get generator description.
	 *
	 * @return string Description
	 */
	public function get_description(): string {
		return 'Generates comprehensive payment transaction history with realistic transaction IDs, multiple payment gateways, various transaction types (payment, refund, adjustment, fee, commission), and appropriate status distributions for testing ecommerce payment functionality.';
	}
}
