<?php
/**
 * MCP Ability: Generate Transactions
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Transactions
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/transactions/generate
 *
 * @since 2.1.0
 */
class Generate_Transactions extends Ability {

	const REST_BASE = 'transactions';

	/** {@inheritdoc} */
	public static function execute( array $input = array() ) {
		return static::dispatch( static::build_payload( $input ) );
	}

	/** {@inheritdoc} */
	protected static function build_payload( array $input ): array {
		$payload = array(
			'count'  => $input['count'] ?? 5,
			'locale' => $input['locale'] ?? 'en_US',
		);

		if ( isset( $input['seed'] ) ) {
			$payload['seed'] = (int) $input['seed'];
		}

		if ( isset( $input['customer_type'] ) ) {
			$payload['customer_type'] = $input['customer_type'];
		}

		if ( isset( $input['specific_customer_id'] ) ) {
			$payload['specific_customer_id'] = (int) $input['specific_customer_id'];
		}

		if ( isset( $input['transaction_types'] ) ) {
			$payload['transaction_types'] = (array) $input['transaction_types'];
		}

		if ( isset( $input['payment_gateways'] ) ) {
			$payload['payment_gateways'] = (array) $input['payment_gateways'];
		}

		return $payload;
	}
}
