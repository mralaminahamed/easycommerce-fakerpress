<?php
/**
 * MCP Ability: Generate Customers
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\MCP\Abilities;

use EasyCommerceFakerPress\Abstracts\Ability;

defined( 'ABSPATH' ) || exit;

/**
 * Generate_Customers
 *
 * Maps to REST endpoint: POST /easycommerce-fakerpress/v1/customers/generate
 *
 * @since 2.1.0
 */
class Generate_Customers extends Ability {

	const REST_BASE = 'customers';

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

		if ( isset( $input['customer_types'] ) ) {
			$payload['customer_types'] = (array) $input['customer_types'];
		}

		$payload['address_preferences'] = array(
			'include_billing'           => $input['include_billing'] ?? true,
			'include_shipping'          => $input['include_shipping'] ?? true,
			'different_addresses_ratio' => $input['different_addresses_ratio'] ?? 30,
		);

		$payload['purchase_history'] = array(
			'simulate_history' => $input['simulate_purchase_history'] ?? true,
			'loyalty_tiers'    => $input['simulate_purchase_history'] ?? true,
		);

		$payload['contact_preferences'] = array(
			'phone_numbers'          => true,
			'marketing_opt_in_ratio' => $input['marketing_opt_in_ratio'] ?? 65,
		);

		return $payload;
	}
}
