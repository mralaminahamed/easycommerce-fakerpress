<?php
/**
 * MCP Ability: Generate Tax Classes
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Tax_Classes
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/tax_classes/generate
 *
 * @since 2.1.0
 */
class Generate_Tax_Classes extends Ability {

	const REST_BASE = 'tax_classes';

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

		if ( isset( $input['tax_types'] ) ) {
			$payload['tax_types'] = (array) $input['tax_types'];
		}

		$payload['location_coverage'] = array(
			'countries'        => $input['countries'] ?? array( 'US', 'CA', 'GB', 'AU', 'DE' ),
			'include_compound' => $input['include_compound'] ?? true,
		);

		return $payload;
	}
}
