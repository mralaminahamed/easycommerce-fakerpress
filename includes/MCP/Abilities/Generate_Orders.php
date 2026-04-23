<?php
/**
 * MCP Ability: Generate Orders
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Orders
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/orders/generate
 *
 * @since 2.1.0
 */
class Generate_Orders extends Ability {

	const REST_BASE = 'orders';

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed> $input Validated input from the MCP client.
	 * @return array<string, mixed>|\WP_Error
	 */
	public static function execute( array $input = array() ) {
		return static::dispatch( static::build_payload( $input ) );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param array<string, mixed> $input Raw MCP input.
	 * @return array<string, mixed>
	 */
	protected static function build_payload( array $input ): array {
		$payload = array(
			'count'  => $input['count'] ?? 5,
			'locale' => $input['locale'] ?? 'en_US',
		);

		if ( isset( $input['seed'] ) ) {
			$payload['seed'] = (int) $input['seed'];
		}

		if ( isset( $input['order_status'] ) ) {
			$payload['order_status'] = $input['order_status'];
		}

		if ( isset( $input['customer_type'] ) ) {
			$payload['customer_type'] = $input['customer_type'];
		}

		if ( isset( $input['specific_customer_id'] ) ) {
			$payload['specific_customer_id'] = (int) $input['specific_customer_id'];
		}

		$payload['order_value'] = array(
			'min_total' => $input['min_total'] ?? 10,
			'max_total' => $input['max_total'] ?? 1000,
		);

		$payload['items_per_order'] = array(
			'min' => $input['min_items'] ?? 1,
			'max' => $input['max_items'] ?? 5,
		);

		if ( isset( $input['payment_methods'] ) ) {
			$payload['payment_methods'] = (array) $input['payment_methods'];
		}

		if ( isset( $input['customer_distribution'] ) ) {
			$payload['customer_distribution'] = $input['customer_distribution'];
		}

		if ( isset( $input['geographical_distribution'] ) ) {
			$payload['geographical_distribution'] = $input['geographical_distribution'];
		}

		return $payload;
	}
}
