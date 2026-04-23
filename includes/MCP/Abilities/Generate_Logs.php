<?php
/**
 * MCP Ability: Generate Logs
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Logs
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/logs/generate
 *
 * @since 2.1.0
 */
class Generate_Logs extends Ability {

	const REST_BASE = 'logs';

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
			'count'  => $input['count'] ?? 10,
			'locale' => $input['locale'] ?? 'en_US',
		);

		if ( isset( $input['seed'] ) ) {
			$payload['seed'] = (int) $input['seed'];
		}

		if ( isset( $input['log_types'] ) ) {
			$payload['log_types'] = $input['log_types'];
		}

		if ( isset( $input['objects'] ) ) {
			$payload['objects'] = $input['objects'];
		}

		return $payload;
	}
}
