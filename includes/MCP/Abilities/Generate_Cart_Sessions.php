<?php
/**
 * MCP Ability: Generate Cart Sessions
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Cart_Sessions
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/cart-sessions/generate
 *
 * @since 2.1.0
 */
class Generate_Cart_Sessions extends Ability {

	const REST_BASE = 'cart-sessions';

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

		if ( isset( $input['guest_cart_ratio'] ) ) {
			$payload['guest_cart_ratio'] = (int) $input['guest_cart_ratio'];
		}

		if ( isset( $input['abandonment_rate'] ) ) {
			$payload['abandonment_rate'] = (int) $input['abandonment_rate'];
		}

		$payload['cart_value_range'] = array(
			'min' => $input['cart_value_min'] ?? 5,
			'max' => $input['cart_value_max'] ?? 500,
		);

		return $payload;
	}
}
