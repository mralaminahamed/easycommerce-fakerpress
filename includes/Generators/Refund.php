<?php
/**
 * Refund Generator.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerce\Models\Order as OrderModel;
use EasyCommerce\Models\Refund as RefundModel;
use EasyCommerceFakerPress\Abstracts\Generator;
use WP_Error;

/**
 * Refund Generator Class
 *
 * Generates realistic fake refund data for EasyCommerce
 *
 * @since 1.0.0
 */
class Refund extends Generator {

	/**
	 * Predefined refund reasons.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	private const REASONS = array(
		'Item not as described',
		'Duplicate order placed',
		'Item arrived damaged',
		'Wrong item received',
		'Item never arrived',
		'Changed mind after purchase',
		'Found cheaper price elsewhere',
		'Accidental purchase',
		'Quality not as expected',
		'Billing error',
	);

	/**
	 * Supported payment gateways.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	private const GATEWAYS = array(
		'stripe',
		'paypal',
		'square',
		'bank_transfer',
		'authorize_net',
	);

	/**
	 * Get the resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'refund';
	}

	/**
	 * Get supported refund types.
	 *
	 * @since 1.0.0
	 *
	 * @return array Supported types.
	 */
	public function get_supported_types(): array {
		return array( 'full', 'partial' );
	}

	/**
	 * Get generator description.
	 *
	 * @since 1.0.0
	 *
	 * @return string Description.
	 */
	public function get_description(): string {
		return 'Generates realistic fake refund records linked to existing EasyCommerce orders, supporting full and partial refund scenarios across multiple payment gateways.';
	}

	/**
	 * Generate a single refund
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Single refund data, or WP_Error on failure.
	 */
	protected function generate_single_item() {
		if ( ! class_exists( RefundModel::class ) ) {
			return new WP_Error(
				'missing_model',
				__( 'EasyCommerce Refund model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' )
			);
		}

		$order = $this->get_eligible_order();
		if ( is_wp_error( $order ) ) {
			return $order;
		}

		$order_id = $order->get_id();
		$total    = (float) $order->get_total();

		// Determine refund type: 50/50 full vs partial.
		if ( $this->get_faker()->boolean( 50 ) ) {
			$type   = 'full';
			$amount = $total;
		} else {
			$type   = 'partial';
			$amount = round( $total * $this->get_faker()->randomFloat( 2, 0.1, 0.9 ), 2 );
		}

		$gateway         = $this->get_faker()->randomElement(
			$this->generation_params['payment_gateways'] ?? self::GATEWAYS
		);
		$status          = $this->get_faker()->randomElement(
			array( 'completed', 'completed', 'pending', 'failed' )
		);
		$transaction_id  = $this->generate_transaction_id( $gateway );
		$reason          = $this->get_faker()->randomElement( self::REASONS );
		$notes           = $this->get_faker()->optional( 0.4 )->sentence( 10 );
		$current_user_id = get_current_user_id();
		$refunded_by     = $current_user_id ? $current_user_id : 1;

		$refund_model = new RefundModel();
		$refund_id    = $refund_model->create(
			array(
				'order_id'        => $order_id,
				'amount'          => $amount,
				'currency'        => 'USD',
				'reason'          => $reason,
				'status'          => $status,
				'transaction_id'  => $transaction_id,
				'payment_gateway' => $gateway,
				'notes'           => $notes,
				'refunded_by'     => $refunded_by,
			)
		);

		if ( ! $refund_id ) {
			return new WP_Error(
				'refund_creation_failed',
				__( 'Failed to create refund using EasyCommerce model.', 'easycommerce-fakerpress' )
			);
		}

		return array(
			'id'              => $refund_id,
			'order_id'        => $order_id,
			'amount'          => $amount,
			'currency'        => 'USD',
			'reason'          => $reason,
			'status'          => $status,
			'type'            => $type,
			'payment_gateway' => $gateway,
			'transaction_id'  => $transaction_id,
		);
	}

	/**
	 * Get an eligible order for refund generation
	 *
	 * Queries the database for a random completed or processing order
	 * to use as the basis for a generated refund.
	 *
	 * @since 1.0.0
	 *
	 * @return OrderModel|WP_Error Order model instance, or WP_Error on failure.
	 */
	private function get_eligible_order() {
		global $wpdb;

		$table    = $wpdb->prefix . 'easycommerce_orders';
		$statuses = array( 'completed', 'processing' );

		$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE status IN ({$placeholders}) ORDER BY RAND() LIMIT 1",
				...$statuses
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

		if ( ! $row || ! isset( $row->id ) ) {
			return new WP_Error(
				'no_eligible_order',
				__( 'No eligible orders found for refund generation. Please generate some completed or processing orders first.', 'easycommerce-fakerpress' )
			);
		}

		return new OrderModel( (int) $row->id );
	}

	/**
	 * Generate a transaction ID based on payment gateway
	 *
	 * Returns a gateway-specific transaction ID format for the given gateway slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $gateway Payment gateway slug.
	 *
	 * @return string Transaction ID string.
	 */
	private function generate_transaction_id( string $gateway ): string {
		if ( 'stripe' === $gateway ) {
			return 're_' . $this->get_faker()->regexify( '[a-zA-Z0-9]{24}' );
		} elseif ( 'paypal' === $gateway ) {
			return $this->get_faker()->regexify( '[A-Z0-9]{17}' );
		} elseif ( 'square' === $gateway ) {
			return $this->get_faker()->regexify( '[a-zA-Z0-9]{22}' );
		} elseif ( 'authorize_net' === $gateway ) {
			return $this->get_faker()->numerify( '##########' );
		} else {
			return $this->get_faker()->uuid();
		}
	}
}
