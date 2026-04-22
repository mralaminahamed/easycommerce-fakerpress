<?php
/**
 * MCP Ability: Generate Attributes
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Attributes
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/attributes/generate
 *
 * @since 2.1.0
 */
class Generate_Attributes extends Ability {

	const REST_BASE = 'attributes';

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

		if ( isset( $input['attribute_types'] ) ) {
			$payload['attribute_types'] = $input['attribute_types'];
		}

		return $payload;
	}
}
