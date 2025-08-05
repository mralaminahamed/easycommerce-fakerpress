<?php
namespace EasyCommerceFakerPress\Generators;

defined( 'ABSPATH' ) || exit;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Shipping_Plan;
use EasyCommerce\Models\Database;

/**
 * Shipping Plan Generator Class
 *
 * Generates realistic shipping plans with methods and regional coverage.
 */
class Shipping_Plan_Generator extends Generator {

	/**
	 * Get the resource type name
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'shipping_plan';
	}

	/**
	 * Generate a single shipping plan
	 *
	 * @return array|WP_Error Single shipping plan data, error, or false on failure.
	 */
	protected function generate_single_item() {
		try {
			$shipping_plan_data = $this->generate_shipping_plan_data();
			$shipping_plan      = $this->create_shipping_plan( $shipping_plan_data );

			if ( $shipping_plan ) {
				return array(
					'id'               => $shipping_plan->get_id(),
					'name'             => $shipping_plan->get_name(),
					'description'      => $shipping_plan->get_description(),
					'active'           => $shipping_plan->is_active(),
					'taxable'          => $shipping_plan->is_taxable(),
					'calculation_base' => $shipping_plan->get_calculation_base(),
					'methods'          => $shipping_plan->get_methods(),
					'regions'          => $shipping_plan->get_regions(),
				);
			}

			return false;
		} catch ( \Exception $e ) {
			$this->log( 'Failed to generate shipping plan: ' . $e->getMessage(), 'error' );
			return false;
		}
	}

	/**
	 * Generate multiple shipping plans.
	 *
	 * @param int   $count Number of shipping plans to generate.
	 * @param array $args Additional arguments.
	 * @return array Generated shipping plan data
	 */
	public function generate_multiple( int $count = 5, array $args = array() ): array {
		$results = array();

		for ( $i = 0; $i < $count; $i++ ) {
			$item_result = $this->generate_single_item();

			if ( $item_result && ! is_wp_error( $item_result ) ) {
				$results[] = $item_result;
			}
		}

		return $results;
	}

	/**
	 * Generate shipping plan data.
	 *
	 * @return array Shipping plan data
	 */
	private function generate_shipping_plan_data(): array {
		$plan_types = array(
			'standard'  => array(
				'name'             => 'Standard Shipping',
				'description'      => 'Regular delivery within 5-7 business days',
				'calculation_base' => 'price',
				'methods'          => $this->generate_price_based_methods(),
			),
			'express'   => array(
				'name'             => 'Express Shipping',
				'description'      => 'Fast delivery within 1-3 business days',
				'calculation_base' => 'price',
				'methods'          => $this->generate_express_methods(),
			),
			'weight'    => array(
				'name'             => 'Weight-Based Shipping',
				'description'      => 'Shipping cost calculated by weight',
				'calculation_base' => 'weight',
				'methods'          => $this->generate_weight_based_methods(),
			),
			'free'      => array(
				'name'             => 'Free Shipping',
				'description'      => 'Free shipping for orders above minimum amount',
				'calculation_base' => 'price',
				'methods'          => $this->generate_free_shipping_methods(),
			),
			'overnight' => array(
				'name'             => 'Overnight Delivery',
				'description'      => 'Next business day delivery',
				'calculation_base' => 'price',
				'methods'          => $this->generate_overnight_methods(),
			),
		);

		$plan_type   = $this->faker->randomElement( array_keys( $plan_types ) );
		$plan_config = $plan_types[ $plan_type ];

		return array(
			'name'             => $plan_config['name'] . ' - ' . $this->faker->city,
			'description'      => $plan_config['description'],
			'active'           => $this->faker->boolean( 85 ), // 85% chance of being active
			'taxable'          => $this->faker->boolean( 60 ), // 60% chance of being taxable
			'calculation_base' => $plan_config['calculation_base'],
			'methods'          => $plan_config['methods'],
			'regions'          => $this->generate_regional_coverage(),
		);
	}

	/**
	 * Generate price-based shipping methods.
	 *
	 * @return array Shipping methods
	 */
	private function generate_price_based_methods(): array {
		return array(
			array(
				'name' => 'Under $50',
				'min'  => 0.00,
				'max'  => 49.99,
				'cost' => $this->faker->randomFloat( 2, 5.99, 12.99 ),
			),
			array(
				'name' => '$50 to $100',
				'min'  => 50.00,
				'max'  => 99.99,
				'cost' => $this->faker->randomFloat( 2, 3.99, 8.99 ),
			),
			array(
				'name' => '$100 to $200',
				'min'  => 100.00,
				'max'  => 199.99,
				'cost' => $this->faker->randomFloat( 2, 1.99, 5.99 ),
			),
			array(
				'name' => 'Over $200',
				'min'  => 200.00,
				'max'  => 999999.99,
				'cost' => 0.00, // Free shipping
			),
		);
	}

	/**
	 * Generate express shipping methods.
	 *
	 * @return array Express shipping methods
	 */
	private function generate_express_methods(): array {
		return array(
			array(
				'name' => 'Express Under $25',
				'min'  => 0.00,
				'max'  => 24.99,
				'cost' => $this->faker->randomFloat( 2, 15.99, 25.99 ),
			),
			array(
				'name' => 'Express $25 to $75',
				'min'  => 25.00,
				'max'  => 74.99,
				'cost' => $this->faker->randomFloat( 2, 12.99, 19.99 ),
			),
			array(
				'name' => 'Express $75 to $150',
				'min'  => 75.00,
				'max'  => 149.99,
				'cost' => $this->faker->randomFloat( 2, 8.99, 14.99 ),
			),
			array(
				'name' => 'Express Over $150',
				'min'  => 150.00,
				'max'  => 999999.99,
				'cost' => $this->faker->randomFloat( 2, 4.99, 9.99 ),
			),
		);
	}

	/**
	 * Generate weight-based shipping methods.
	 *
	 * @return array Weight-based shipping methods
	 */
	private function generate_weight_based_methods(): array {
		return array(
			array(
				'name' => 'Up to 1 kg',
				'min'  => 0.0,
				'max'  => 1.0,
				'cost' => $this->faker->randomFloat( 2, 3.99, 7.99 ),
			),
			array(
				'name' => '1 kg to 5 kg',
				'min'  => 1.01,
				'max'  => 5.0,
				'cost' => $this->faker->randomFloat( 2, 7.99, 15.99 ),
			),
			array(
				'name' => '5 kg to 10 kg',
				'min'  => 5.01,
				'max'  => 10.0,
				'cost' => $this->faker->randomFloat( 2, 15.99, 25.99 ),
			),
			array(
				'name' => 'Over 10 kg',
				'min'  => 10.01,
				'max'  => 999.99,
				'cost' => $this->faker->randomFloat( 2, 25.99, 49.99 ),
			),
		);
	}

	/**
	 * Generate free shipping methods.
	 *
	 * @return array Free shipping methods
	 */
	private function generate_free_shipping_methods(): array {
		$minimum_order = $this->faker->randomElement( array( 50, 75, 100, 150, 200 ) );

		return array(
			array(
				'name' => 'Standard Shipping',
				'min'  => 0.00,
				'max'  => $minimum_order - 0.01,
				'cost' => $this->faker->randomFloat( 2, 4.99, 9.99 ),
			),
			array(
				'name' => "Free Shipping (Orders over ${minimum_order})",
				'min'  => $minimum_order,
				'max'  => 999999.99,
				'cost' => 0.00,
			),
		);
	}

	/**
	 * Generate overnight shipping methods.
	 *
	 * @return array Overnight shipping methods
	 */
	private function generate_overnight_methods(): array {
		return array(
			array(
				'name' => 'Overnight Under $50',
				'min'  => 0.00,
				'max'  => 49.99,
				'cost' => $this->faker->randomFloat( 2, 25.99, 39.99 ),
			),
			array(
				'name' => 'Overnight $50 to $100',
				'min'  => 50.00,
				'max'  => 99.99,
				'cost' => $this->faker->randomFloat( 2, 19.99, 29.99 ),
			),
			array(
				'name' => 'Overnight Over $100',
				'min'  => 100.00,
				'max'  => 999999.99,
				'cost' => $this->faker->randomFloat( 2, 12.99, 19.99 ),
			),
		);
	}

	/**
	 * Generate regional coverage.
	 *
	 * @return array Region codes
	 */
	private function generate_regional_coverage(): array {
		$regions = array(
			// North America.
			'US-CA',
			'US-NY',
			'US-TX',
			'US-FL',
			'US-IL',
			'CA-ON',
			'CA-BC',
			'CA-AB',
			'CA-QC',
			'MX-DF',
			'MX-NL',
			'MX-JA',

			// Europe.
			'GB-EN',
			'GB-SC',
			'GB-WA',
			'GB-NI',
			'DE-BY',
			'DE-NW',
			'DE-BW',
			'DE-HE',
			'FR-11',
			'FR-84',
			'FR-93',
			'FR-75',
			'IT-LZ',
			'IT-LM',
			'IT-PM',
			'IT-VE',
			'ES-MD',
			'ES-CT',
			'ES-AN',
			'ES-VC',

			// Asia Pacific.
			'AU-NSW',
			'AU-VIC',
			'AU-QLD',
			'AU-WA',
			'JP-13',
			'JP-27',
			'JP-23',
			'JP-14',
			'CN-11',
			'CN-31',
			'CN-44',
			'CN-32',
			'IN-DL',
			'IN-MH',
			'IN-KA',
			'IN-TN',

			// Others.
			'BR-SP',
			'BR-RJ',
			'BR-MG',
			'BR-RS',
			'ZA-WC',
			'ZA-GT',
			'ZA-KZ',
			'ZA-EC',
			'NG-LA',
			'NG-AB',
			'NG-KA',
			'NG-RI',
		);

		// Select random regions (2-8 regions per shipping plan).
		$selected_regions = $this->faker->randomElements(
			$regions,
			$this->faker->numberBetween( 2, 8 )
		);

		return $selected_regions;
	}

	/**
	 * Create shipping plan in database.
	 *
	 * @param array $data Shipping plan data.
	 * @return Shipping_Plan|null Created shipping plan instance
	 */
	private function create_shipping_plan( array $data ): ?Shipping_Plan {
		$shipping_plan = new Shipping_Plan();

		$plan_id = $shipping_plan->create( $data );

		if ( $plan_id ) {
			return new Shipping_Plan( $plan_id );
		}

		return null;
	}

	/**
	 * Get supported data types for this generator.
	 *
	 * @return array Supported types
	 */
	public function get_supported_types(): array {
		return array(
			'shipping_plans' => 'Shipping Plans with Methods and Regions',
		);
	}

	/**
	 * Get generator description.
	 *
	 * @return string Description
	 */
	public function get_description(): string {
		return 'Generates comprehensive shipping plans with multiple calculation methods (price, weight, quantity), regional coverage, and realistic pricing tiers for testing ecommerce shipping functionality.';
	}
}
