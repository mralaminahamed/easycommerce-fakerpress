<?php
/**
 * MCP Ability: Generate Shipping Plans
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Shipping_Plans
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/shipping-plans/generate
 *
 * @since 2.1.0
 */
class Generate_Shipping_Plans extends Ability {

	const REST_BASE = 'shipping-plans';

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

		if ( isset( $input['shipping_types'] ) ) {
			$payload['shipping_types'] = (array) $input['shipping_types'];
		}

		$payload['cost_range'] = array(
			'min' => $input['cost_min'] ?? 0,
			'max' => $input['cost_max'] ?? 50,
		);

		if ( isset( $input['coverage_areas'] ) ) {
			$payload['coverage_areas'] = (array) $input['coverage_areas'];
		}

		return $payload;
	}
}
