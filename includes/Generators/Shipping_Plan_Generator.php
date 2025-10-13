<?php
/**
 * Shipping Plan Generator.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

defined( 'ABSPATH' ) || exit;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Shipping_Plan;
use Exception;
use WP_Error;

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
	 * @return WP_Error|array Single shipping plan data, error, or false on failure.
	 */
	protected function generate_single_item() {
		// Check if EasyCommerce Shipping_Plan class exists.
		if ( ! class_exists( Shipping_Plan::class ) ) {
			return new WP_Error( 'missing_model', __( 'EasyCommerce Shipping_Plan model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' ) );
		}

		$shipping_plan_data = $this->generate_shipping_plan_data();
		$shipping_plan      = $this->create_shipping_plan( $shipping_plan_data );

		if ( ! $shipping_plan ) {
			return new WP_Error( 'shipping_plan_creation_failed', __( 'Failed to create shipping plan.', 'easycommerce-fakerpress' ) );
		}

		// Test location lookup to verify plan can be found by its regions.
		$location_test = $this->test_location_lookup( $shipping_plan, $shipping_plan_data['regions'] );

		return array(
			'id'               => $shipping_plan->get_id(),
			'name'             => $shipping_plan->get_name(),
			'description'      => $shipping_plan->get_description(),
			'active'           => $shipping_plan->is_active(),
			'taxable'          => $shipping_plan->is_taxable(),
			'calculation_base' => $shipping_plan->get_calculation_base(),
			'methods'          => $shipping_plan->get_methods(),
			'regions'          => $shipping_plan->get_regions(),
			'location_test'    => $location_test,
		);
	}

	/**
	 * Generate multiple shipping plans.
	 *
	 * @param int   $count Number of shipping plans to generate.
	 * @param array $args Additional arguments.
	 *
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

		$plan_type   = $this->get_faker()->randomElement( array_keys( $plan_types ) );
		$plan_config = $plan_types[ $plan_type ];

		return array(
			'name'             => $plan_config['name'] . ' - ' . $this->get_faker()->city,
			'description'      => $plan_config['description'],
			'active'           => $this->get_faker()->boolean( 85 ), // 85% chance of being active
			'taxable'          => $this->get_faker()->boolean( 60 ), // 60% chance of being taxable
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
				'cost' => $this->get_faker()->randomFloat( 2, 5.99, 12.99 ),
			),
			array(
				'name' => '$50 to $100',
				'min'  => 50.00,
				'max'  => 99.99,
				'cost' => $this->get_faker()->randomFloat( 2, 3.99, 8.99 ),
			),
			array(
				'name' => '$100 to $200',
				'min'  => 100.00,
				'max'  => 199.99,
				'cost' => $this->get_faker()->randomFloat( 2, 1.99, 5.99 ),
			),
			array(
				'name' => 'Over $200',
				'min'  => 200.00,
				'max'  => 999999.99,
				'cost' => 0.00, // Free shipping.
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
				'cost' => $this->get_faker()->randomFloat( 2, 15.99, 25.99 ),
			),
			array(
				'name' => 'Express $25 to $75',
				'min'  => 25.00,
				'max'  => 74.99,
				'cost' => $this->get_faker()->randomFloat( 2, 12.99, 19.99 ),
			),
			array(
				'name' => 'Express $75 to $150',
				'min'  => 75.00,
				'max'  => 149.99,
				'cost' => $this->get_faker()->randomFloat( 2, 8.99, 14.99 ),
			),
			array(
				'name' => 'Express Over $150',
				'min'  => 150.00,
				'max'  => 999999.99,
				'cost' => $this->get_faker()->randomFloat( 2, 4.99, 9.99 ),
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
				'cost' => $this->get_faker()->randomFloat( 2, 3.99, 7.99 ),
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
				'name' => "Free Shipping (Orders over {$minimum_order})",
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
	 * @return array Region data with structured location arrays
	 */
	private function generate_regional_coverage(): array {
		$regions = array(
			// North America.
			array(
				'country'  => 'US',
				'state'    => 'CA',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'US',
				'state'    => 'NY',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'US',
				'state'    => 'TX',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'US',
				'state'    => 'FL',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'US',
				'state'    => 'IL',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'CA',
				'state'    => 'ON',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'CA',
				'state'    => 'BC',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'CA',
				'state'    => 'AB',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'CA',
				'state'    => 'QC',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'MX',
				'state'    => 'DF',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'MX',
				'state'    => 'NL',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'MX',
				'state'    => 'JA',
				'city'     => '',
				'zip_code' => '',
			),

			// Europe.
			array(
				'country'  => 'GB',
				'state'    => 'EN',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'GB',
				'state'    => 'SC',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'GB',
				'state'    => 'WA',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'GB',
				'state'    => 'NI',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'DE',
				'state'    => 'BY',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'DE',
				'state'    => 'NW',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'DE',
				'state'    => 'BW',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'DE',
				'state'    => 'HE',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'FR',
				'state'    => '11',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'FR',
				'state'    => '84',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'FR',
				'state'    => '93',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'FR',
				'state'    => '75',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'IT',
				'state'    => 'LZ',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'IT',
				'state'    => 'LM',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'IT',
				'state'    => 'PM',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'IT',
				'state'    => 'VE',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'ES',
				'state'    => 'MD',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'ES',
				'state'    => 'CT',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'ES',
				'state'    => 'AN',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'ES',
				'state'    => 'VC',
				'city'     => '',
				'zip_code' => '',
			),

			// Asia Pacific.
			array(
				'country'  => 'AU',
				'state'    => 'NSW',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'AU',
				'state'    => 'VIC',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'AU',
				'state'    => 'QLD',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'AU',
				'state'    => 'WA',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'JP',
				'state'    => '13',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'JP',
				'state'    => '27',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'JP',
				'state'    => '23',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'JP',
				'state'    => '14',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'CN',
				'state'    => '11',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'CN',
				'state'    => '31',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'CN',
				'state'    => '44',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'CN',
				'state'    => '32',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'IN',
				'state'    => 'DL',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'IN',
				'state'    => 'MH',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'IN',
				'state'    => 'KA',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'IN',
				'state'    => 'TN',
				'city'     => '',
				'zip_code' => '',
			),

			// Others.
			array(
				'country'  => 'BR',
				'state'    => 'SP',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'BR',
				'state'    => 'RJ',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'BR',
				'state'    => 'MG',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'BR',
				'state'    => 'RS',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'ZA',
				'state'    => 'WC',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'ZA',
				'state'    => 'GT',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'ZA',
				'state'    => 'KZ',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'ZA',
				'state'    => 'EC',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'NG',
				'state'    => 'LA',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'NG',
				'state'    => 'AB',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'NG',
				'state'    => 'KA',
				'city'     => '',
				'zip_code' => '',
			),
			array(
				'country'  => 'NG',
				'state'    => 'RI',
				'city'     => '',
				'zip_code' => '',
			),
		);

		// Select random regions (2-8 regions per shipping plan).
		return $this->faker->randomElements(
			$regions,
			$this->faker->numberBetween( 2, 8 )
		);
	}

	/**
	 * Create shipping plan in database.
	 *
	 * @param array $data Shipping plan data.
	 *
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

	/**
	 * Test location lookup for shipping plan
	 *
	 * Verifies that the shipping plan can be found using get_by_location()
	 *
	 * @param Shipping_Plan $shipping_plan Created shipping plan instance.
	 * @param array         $regions Array of region data.
	 *
	 * @return WP_Error|array Test result with lookup status.
	 */
	private function test_location_lookup( Shipping_Plan $shipping_plan, array $regions ): array {
		if ( empty( $regions ) ) {
			return new WP_Error( 'missing_regions', __( 'No regions found.', 'easycommerce-fakerpress' ) );
		}

		// Pick a random region to test.
		$test_region = $this->faker->randomElement( $regions );

		// Build region code in format: COUNTRY-STATE or COUNTRY-STATE-CITY.
		$region_code_parts = array( $test_region['country'] );

		if ( ! empty( $test_region['state'] ) ) {
			$region_code_parts[] = $test_region['state'];
		}

		if ( ! empty( $test_region['city'] ) ) {
			$region_code_parts[] = $test_region['city'];
		}

		$region_code = implode( '-', $region_code_parts );

		// Use Shipping_Plan::get_by_location() to find plans for this region.
		$found_plans = Shipping_Plan::get_by_location( $region_code, 10 );

		// Check if our plan is in the results.
		$plan_found = false;
		foreach ( $found_plans as $plan ) {
			if ( $plan['id'] === $shipping_plan->get_id() ) {
				$plan_found = true;
				break;
			}
		}

		return array(
			'tested'      => true,
			'plan_found'  => $plan_found,
			'region_code' => $region_code,
			'total_plans' => count( $found_plans ),
			'message'     => $plan_found ? __( 'Plan successfully found by location', 'easycommerce-fakerpress' ) : __( 'Plan not found in location lookup', 'easycommerce-fakerpress' ),
		);
	}
}
