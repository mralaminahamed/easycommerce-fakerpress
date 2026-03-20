<?php
/**
 * MCP Ability: Generate Locations
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Locations
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/locations/generate
 *
 * @since 2.1.0
 */
class Generate_Locations extends Ability {

	const REST_BASE = 'locations';

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

		if ( ! empty( $input['regions'] ) ) {
			$payload['regions'] = (array) $input['regions'];
		}

		if ( ! empty( $input['countries'] ) ) {
			$payload['countries'] = (array) $input['countries'];
		}

		if ( isset( $input['max_countries'] ) ) {
			$payload['max_countries'] = (int) $input['max_countries'];
		}

		$payload['include_states'] = $input['include_states'] ?? true;
		$payload['include_cities'] = $input['include_cities'] ?? true;

		return $payload;
	}
}
