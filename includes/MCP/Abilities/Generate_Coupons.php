<?php
/**
 * MCP Ability: Generate Coupons
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Coupons
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/coupons/generate
 *
 * @since 2.1.0
 */
class Generate_Coupons extends Ability {

	const REST_BASE = 'coupons';

	/**
	 * {@inheritdoc}
	 */
	public static function execute( array $input = array() ) {
		return static::dispatch( static::build_payload( $input ) );
	}

	/**
	 * {@inheritdoc}
	 */
	protected static function build_payload( array $input ): array {
		$payload = array(
			'count'  => $input['count'] ?? 5,
			'locale' => $input['locale'] ?? 'en_US',
		);

		if ( isset( $input['seed'] ) ) {
			$payload['seed'] = (int) $input['seed'];
		}

		if ( isset( $input['discount_types'] ) ) {
			$payload['discount_types'] = (array) $input['discount_types'];
		}

		$payload['discount_range'] = array(
			'min_percentage' => $input['min_percentage'] ?? 10,
			'max_percentage' => $input['max_percentage'] ?? 50,
			'min_fixed'      => $input['min_fixed'] ?? 5,
			'max_fixed'      => $input['max_fixed'] ?? 100,
		);

		$payload['usage_limits'] = array(
			'set_usage_limits' => $input['set_usage_limits'] ?? true,
			'max_uses'         => $input['max_uses'] ?? 100,
		);

		$payload['validity_period'] = array(
			'min_days' => $input['validity_min_days'] ?? 7,
			'max_days' => $input['validity_max_days'] ?? 90,
		);

		return $payload;
	}
}
