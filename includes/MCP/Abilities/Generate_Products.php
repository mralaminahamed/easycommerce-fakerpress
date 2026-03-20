<?php
/**
 * MCP Ability: Generate Products
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Products
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/products/generate
 *
 * @since 2.1.0
 */
class Generate_Products extends Ability {

	const REST_BASE = 'products';

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

		if ( isset( $input['product_type'] ) ) {
			$payload['product_type'] = $input['product_type'];
		}

		// Nest price into the expected object shape.
		if ( isset( $input['price_min'] ) || isset( $input['price_max'] ) ) {
			$payload['price_range'] = array(
				'min' => $input['price_min'] ?? 10,
				'max' => $input['price_max'] ?? 500,
			);
		}

		// Nest attributes options.
		$payload['attributes'] = array(
			'include_attributes' => $input['include_attributes'] ?? true,
			'variation_count'    => $input['variation_count'] ?? 5,
		);

		// Nest inventory options.
		$payload['inventory'] = array(
			'manage_stock' => $input['manage_stock'] ?? true,
		);

		// Nest content options.
		$payload['content_options'] = array(
			'description_length' => $input['description_length'] ?? 'medium',
		);

		return $payload;
	}
}
