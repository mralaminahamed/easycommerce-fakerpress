<?php
/**
 * Customer Generator REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Customer;

/**
 * Customer Generator REST Controller
 *
 * Handles REST API endpoints for customer generation
 *
 * @since 1.0.0
 */
class Customers extends Controller {

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'customer';
	}

	/**
	 * Get resource type label for customers
	 *
	 * @since 1.0.0
	 *
	 * @return string The translated label for customer resource type.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Customer', 'easycommerce-fakerpress' );
	}

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'customers';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Customer Generator instance.
	 */
	protected function get_generator_instance(): Customer {
		return new Customer();
	}

	/**
	 * Get resource-specific generation parameters
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific parameters.
	 */
	protected function get_resource_specific_params(): array {
		return array(
			'customer_types'      => array(
				'description'       => __( 'Types of customers to generate.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'regular', 'vip', 'wholesale', 'guest', 'returning' ),
				),
				'default'           => array( 'regular', 'returning' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'demographics'        => array(
				'description' => __( 'Demographic distribution.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'age_groups'          => array(
						'description'       => __( 'Age group distribution.', 'easycommerce-fakerpress' ),
						'type'              => 'array',
						'items'             => array(
							'type' => 'string',
							'enum' => array( '18-25', '26-35', '36-45', '46-55', '56-65', '65+' ),
						),
						'default'           => array( '26-35', '36-45', '46-55' ),
						'sanitize_callback' => array( $this, 'sanitize_array' ),
					),
					'gender_distribution' => array(
						'description' => __( 'Gender distribution weight.', 'easycommerce-fakerpress' ),
						'type'        => 'object',
						'properties'  => array(
							'male'   => array(
								'type'    => 'integer',
								'minimum' => 0,
								'maximum' => 100,
								'default' => 45,
							),
							'female' => array(
								'type'    => 'integer',
								'minimum' => 0,
								'maximum' => 100,
								'default' => 45,
							),
							'other'  => array(
								'type'    => 'integer',
								'minimum' => 0,
								'maximum' => 100,
								'default' => 10,
							),
						),
					),
				),
			),
			'address_preferences' => array(
				'description' => __( 'Address generation preferences.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'include_billing'           => array(
						'description' => __( 'Include billing addresses.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'include_shipping'          => array(
						'description' => __( 'Include shipping addresses.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'different_addresses_ratio' => array(
						'description' => __( 'Percentage with different billing/shipping (0-100).', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'maximum'     => 100,
						'default'     => 30,
					),
				),
			),
			'purchase_history'    => array(
				'description' => __( 'Purchase history simulation.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'simulate_history' => array(
						'description' => __( 'Generate purchase history metadata.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'loyalty_tiers'    => array(
						'description' => __( 'Include loyalty tier assignments.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
				),
			),
			'contact_preferences' => array(
				'description' => __( 'Contact and communication preferences.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'phone_numbers'          => array(
						'description' => __( 'Include phone numbers.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'marketing_opt_in_ratio' => array(
						'description' => __( 'Percentage opted in for marketing (0-100).', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'maximum'     => 100,
						'default'     => 65,
					),
				),
			),
		);
	}

	/**
	 * Get resource-specific schema properties
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific properties.
	 */
	protected function get_resource_specific_properties(): array {
		return array(
			'customers' => array(
				'description' => __( 'Generated customers data.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'type' => 'integer',
						),
						'username' => array(
							'type' => 'string',
						),
						'email'    => array(
							'type' => 'string',
						),
						'name'     => array(
							'type' => 'string',
						),
					),
				),
			),
		);
	}
}
