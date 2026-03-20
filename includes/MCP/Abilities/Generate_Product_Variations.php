<?php
/**
 * MCP Ability: Generate Product Variations
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Product_Variations
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/product-variations/generate
 *
 * @since 2.1.0
 */
class Generate_Product_Variations extends Ability {

	const REST_BASE = 'product-variations';

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

		if ( isset( $input['specific_product_id'] ) ) {
			$payload['specific_product_id'] = (int) $input['specific_product_id'];
		}

		if ( ! empty( $input['exclude_product_ids'] ) ) {
			$payload['exclude_products'] = array_map( 'intval', (array) $input['exclude_product_ids'] );
		}

		$payload['stock_settings'] = array(
			'manage_stock' => $input['manage_stock'] ?? true,
			'stock_range'  => array(
				'min' => $input['stock_min'] ?? 0,
				'max' => $input['stock_max'] ?? 100,
			),
		);

		return $payload;
	}
}
