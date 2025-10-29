<?php
/**
 * Tax Generator
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

defined( 'ABSPATH' ) || exit;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Tax as TaxModel;
use WP_Error;

/**
 * Tax Generator Class
 *
 * Generates realistic tax classes and location-based tax rates.
 */
class Tax_Class extends Generator {

	/**
	 * Get the resource type name
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'tax_class';
	}

	/**
	 * Get supported data types for this generator.
	 *
	 * @return array Supported types
	 */
	public function get_supported_types(): array {
		return array(
			'tax_classes' => 'Tax Classes with Location-Based Rates',
		);
	}

	/**
	 * Get generator description.
	 *
	 * @return string Description
	 */
	public function get_description(): string {
		return 'Generates comprehensive tax classes with location-based rates, supporting multiple jurisdictions (country, state, city), different tax types (standard, reduced, luxury, digital), compound tax calculations, and priority-based tax application for testing ecommerce tax functionality.';
	}

	/**
	 * Generate a single tax class
	 *
	 * @return WP_Error|array|bool Single tax class data, error, or false on failure.
	 */
	protected function generate_single_item() {
		// Check if EasyCommerce Tax class exists.
		if ( ! class_exists( TaxModel::class ) ) {
			return new WP_Error( 'missing_model', __( 'EasyCommerce Tax model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' ) );
		}

		$tax_class_data = $this->generate_tax_class_data();
		$tax_class      = $this->create_tax_class( $tax_class_data );

		if ( ! $tax_class ) {
			return new WP_Error( 'tax-class-not-found', __( 'Tax class not found.', 'easycommerce-fakerpress' ) );
		}

		$result = array(
			'id'          => $tax_class['id'],
			'name'        => $tax_class['name'],
			'description' => $tax_class['description'],
			'status'      => $tax_class['status'],
			'rates'       => $tax_class['rates'],
			'regions'     => $this->get_tax_class_regions( $tax_class['rates'] ),
		);

		/**
		 * Filters the tax class generation result data.
		 *
		 * Allows developers to modify the returned tax class data after generation.
		 *
		 * @since 1.0.0
		 * @hook easycommerce_fakerpress_tax_class_generation_result
		 *
		 * @param array $result    The tax class generation result data.
		 * @param array $tax_class The created tax class data.
		 */
		return apply_filters( 'easycommerce_fakerpress_tax_class_generation_result', $result, $tax_class );
	}

	/**
	 * Generate multiple tax classes with rates.
	 *
	 * @param int   $count Number of tax classes to generate.
	 * @param array $args Additional arguments.
	 *
	 * @return array Generated tax class data
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
	 * Generate tax class data.
	 *
	 * @return array Tax class data
	 */
	private function generate_tax_class_data(): array {
		$tax_types = array(
			'standard' => array(
				'name'        => 'Standard Tax Rate',
				'description' => 'Standard sales tax applied to most products',
				'rates'       => $this->generate_standard_tax_rates(),
			),
			'reduced'  => array(
				'name'        => 'Reduced Tax Rate',
				'description' => 'Reduced tax rate for essential goods and services',
				'rates'       => $this->generate_reduced_tax_rates(),
			),
			'zero'     => array(
				'name'        => 'Zero Tax Rate',
				'description' => 'Tax-exempt items and services',
				'rates'       => $this->generate_zero_tax_rates(),
			),
			'luxury'   => array(
				'name'        => 'Luxury Tax Rate',
				'description' => 'Higher tax rate for luxury goods and services',
				'rates'       => $this->generate_luxury_tax_rates(),
			),
			'digital'  => array(
				'name'        => 'Digital Services Tax',
				'description' => 'Tax rate for digital products and services',
				'rates'       => $this->generate_digital_tax_rates(),
			),
		);

		$tax_type   = $this->get_faker()->randomElement( array_keys( $tax_types ) );
		$tax_config = $tax_types[ $tax_type ];

		return array(
			'name'        => $tax_config['name'] . ' - ' . $this->get_faker()->city,
			'description' => $tax_config['description'],
			'status'      => $this->get_faker()->boolean( 90 ), // 90% chance of being active
			'rates'       => $tax_config['rates'],
		);
	}

	/**
	 * Generate standard tax rates (5-15%).
	 *
	 * @return array Tax rates
	 */
	private function generate_standard_tax_rates(): array {
		$selected_locations = $this->get_faker()->randomElements(
			$this->get_global_tax_locations(),
			$this->get_faker()->numberBetween( 3, 8 )
		);

		$rates = array();
		foreach ( $selected_locations as $location ) {
			$base_rate = $this->get_faker()->randomFloat( 2, 5.0, 15.0 );
			$rates[]   = array(
				'country'  => $location['country'],
				'state'    => $location['state'],
				'city'     => $location['city'],
				'rate'     => $base_rate,
				'priority' => $this->get_faker()->numberBetween( 1, 10 ),
				'compound' => $this->get_faker()->boolean( 20 ), // 20% chance of compound tax
			);
		}

		return $rates;
	}

	/**
	 * Generate reduced tax rates (0-8%).
	 *
	 * @return array Tax rates
	 */
	private function generate_reduced_tax_rates(): array {
		$selected_locations = $this->get_faker()->randomElements(
			$this->get_global_tax_locations(),
			$this->get_faker()->numberBetween( 2, 6 )
		);

		$rates = array();
		foreach ( $selected_locations as $location ) {
			$base_rate = $this->get_faker()->randomFloat( 2, 0.0, 8.0 );
			$rates[]   = array(
				'country'  => $location['country'],
				'state'    => $location['state'],
				'city'     => $location['city'],
				'rate'     => $base_rate,
				'priority' => $this->get_faker()->numberBetween( 1, 5 ),
				'compound' => false, // Reduced rates typically aren't compound.
			);
		}

		return $rates;
	}

	/**
	 * Generate zero tax rates (0%).
	 *
	 * @return array Tax rates
	 */
	private function generate_zero_tax_rates(): array {
		$selected_locations = $this->get_faker()->randomElements(
			$this->get_global_tax_locations(),
			$this->get_faker()->numberBetween( 1, 4 )
		);

		$rates = array();
		foreach ( $selected_locations as $location ) {
			$rates[] = array(
				'country'  => $location['country'],
				'state'    => $location['state'],
				'city'     => $location['city'],
				'rate'     => 0.0,
				'priority' => 1,
				'compound' => false,
			);
		}

		return $rates;
	}

	/**
	 * Generate luxury tax rates (15-30%).
	 *
	 * @return array Tax rates
	 */
	private function generate_luxury_tax_rates(): array {
		$selected_locations = $this->get_faker()->randomElements(
			$this->get_global_tax_locations(),
			$this->get_faker()->numberBetween( 2, 5 )
		);

		$rates = array();
		foreach ( $selected_locations as $location ) {
			$base_rate = $this->get_faker()->randomFloat( 2, 15.0, 30.0 );
			$rates[]   = array(
				'country'  => $location['country'],
				'state'    => $location['state'],
				'city'     => $location['city'],
				'rate'     => $base_rate,
				'priority' => $this->get_faker()->numberBetween( 5, 15 ),
				'compound' => $this->get_faker()->boolean( 40 ), // 40% chance of compound tax
			);
		}

		return $rates;
	}

	/**
	 * Generate digital services tax rates (3-20%).
	 *
	 * @return array Tax rates
	 */
	private function generate_digital_tax_rates(): array {
		$digital_tax_countries = array(
			array(
				'country' => 'US',
				'state'   => 'CA',
				'city'    => '',
			),
			array(
				'country' => 'US',
				'state'   => 'NY',
				'city'    => '',
			),
			array(
				'country' => 'US',
				'state'   => 'TX',
				'city'    => '',
			),
			array(
				'country' => 'GB',
				'state'   => 'EN',
				'city'    => '',
			),
			array(
				'country' => 'DE',
				'state'   => 'BY',
				'city'    => '',
			),
			array(
				'country' => 'FR',
				'state'   => '75',
				'city'    => '',
			),
			array(
				'country' => 'AU',
				'state'   => 'NSW',
				'city'    => '',
			),
			array(
				'country' => 'CA',
				'state'   => 'ON',
				'city'    => '',
			),
		);

		$selected_locations = $this->get_faker()->randomElements(
			$digital_tax_countries,
			$this->get_faker()->numberBetween( 3, 6 )
		);

		$rates = array();
		foreach ( $selected_locations as $location ) {
			$base_rate = $this->get_faker()->randomFloat( 2, 3.0, 20.0 );
			$rates[]   = array(
				'country'  => $location['country'],
				'state'    => $location['state'],
				'city'     => $location['city'],
				'rate'     => $base_rate,
				'priority' => $this->get_faker()->numberBetween( 1, 8 ),
				'compound' => false, // Digital taxes typically aren't compound.
			);
		}

		return $rates;
	}



	/**
	 * Get global tax locations with realistic jurisdiction data.
	 *
	 * @return array Location data
	 */
	private function get_global_tax_locations(): array {
		return array(
			// United States.
			array(
				'country' => 'US',
				'state'   => 'CA',
				'city'    => 'Los Angeles',
			),
			array(
				'country' => 'US',
				'state'   => 'CA',
				'city'    => 'San Francisco',
			),
			array(
				'country' => 'US',
				'state'   => 'CA',
				'city'    => '',
			),
			array(
				'country' => 'US',
				'state'   => 'NY',
				'city'    => 'New York',
			),
			array(
				'country' => 'US',
				'state'   => 'NY',
				'city'    => '',
			),
			array(
				'country' => 'US',
				'state'   => 'TX',
				'city'    => 'Houston',
			),
			array(
				'country' => 'US',
				'state'   => 'TX',
				'city'    => 'Dallas',
			),
			array(
				'country' => 'US',
				'state'   => 'TX',
				'city'    => '',
			),
			array(
				'country' => 'US',
				'state'   => 'FL',
				'city'    => 'Miami',
			),
			array(
				'country' => 'US',
				'state'   => 'FL',
				'city'    => '',
			),
			array(
				'country' => 'US',
				'state'   => 'IL',
				'city'    => 'Chicago',
			),
			array(
				'country' => 'US',
				'state'   => 'IL',
				'city'    => '',
			),

			// Canada.
			array(
				'country' => 'CA',
				'state'   => 'ON',
				'city'    => 'Toronto',
			),
			array(
				'country' => 'CA',
				'state'   => 'ON',
				'city'    => '',
			),
			array(
				'country' => 'CA',
				'state'   => 'BC',
				'city'    => 'Vancouver',
			),
			array(
				'country' => 'CA',
				'state'   => 'BC',
				'city'    => '',
			),
			array(
				'country' => 'CA',
				'state'   => 'QC',
				'city'    => 'Montreal',
			),
			array(
				'country' => 'CA',
				'state'   => 'QC',
				'city'    => '',
			),

			// United Kingdom.
			array(
				'country' => 'GB',
				'state'   => 'EN',
				'city'    => 'London',
			),
			array(
				'country' => 'GB',
				'state'   => 'EN',
				'city'    => '',
			),
			array(
				'country' => 'GB',
				'state'   => 'SC',
				'city'    => 'Glasgow',
			),
			array(
				'country' => 'GB',
				'state'   => 'SC',
				'city'    => '',
			),

			// Germany.
			array(
				'country' => 'DE',
				'state'   => 'BY',
				'city'    => 'Munich',
			),
			array(
				'country' => 'DE',
				'state'   => 'BY',
				'city'    => '',
			),
			array(
				'country' => 'DE',
				'state'   => 'NW',
				'city'    => 'Cologne',
			),
			array(
				'country' => 'DE',
				'state'   => 'NW',
				'city'    => '',
			),

			// France.
			array(
				'country' => 'FR',
				'state'   => '75',
				'city'    => 'Paris',
			),
			array(
				'country' => 'FR',
				'state'   => '75',
				'city'    => '',
			),
			array(
				'country' => 'FR',
				'state'   => '13',
				'city'    => 'Marseille',
			),
			array(
				'country' => 'FR',
				'state'   => '13',
				'city'    => '',
			),

			// Australia.
			array(
				'country' => 'AU',
				'state'   => 'NSW',
				'city'    => 'Sydney',
			),
			array(
				'country' => 'AU',
				'state'   => 'NSW',
				'city'    => '',
			),
			array(
				'country' => 'AU',
				'state'   => 'VIC',
				'city'    => 'Melbourne',
			),
			array(
				'country' => 'AU',
				'state'   => 'VIC',
				'city'    => '',
			),

			// Japan.
			array(
				'country' => 'JP',
				'state'   => '13',
				'city'    => 'Tokyo',
			),
			array(
				'country' => 'JP',
				'state'   => '13',
				'city'    => '',
			),
			array(
				'country' => 'JP',
				'state'   => '27',
				'city'    => 'Osaka',
			),
			array(
				'country' => 'JP',
				'state'   => '27',
				'city'    => '',
			),

			// India.
			array(
				'country' => 'IN',
				'state'   => 'DL',
				'city'    => 'New Delhi',
			),
			array(
				'country' => 'IN',
				'state'   => 'DL',
				'city'    => '',
			),
			array(
				'country' => 'IN',
				'state'   => 'MH',
				'city'    => 'Mumbai',
			),
			array(
				'country' => 'IN',
				'state'   => 'MH',
				'city'    => '',
			),

			// Brazil.
			array(
				'country' => 'BR',
				'state'   => 'SP',
				'city'    => 'São Paulo',
			),
			array(
				'country' => 'BR',
				'state'   => 'SP',
				'city'    => '',
			),
			array(
				'country' => 'BR',
				'state'   => 'RJ',
				'city'    => 'Rio de Janeiro',
			),
			array(
				'country' => 'BR',
				'state'   => 'RJ',
				'city'    => '',
			),
		);
	}

	/**
	 * Create tax class in database.
	 *
	 * @param array $data Tax class data.
	 *
	 * @return array|null Created tax class data
	 */
	private function create_tax_class( array $data ): ?array {
		$tax = new TaxModel();

		$class_id = $tax->create_class( $data );

		if ( $class_id ) {
			return TaxModel::get( $class_id );
		}

		return null;
	}

	/**
	 * Get tax class regions from rates.
	 *
	 * @param array $rates Tax rates.
	 *
	 * @return string Comma-separated regions
	 */
	private function get_tax_class_regions( array $rates ): string {
		$regions = array();

		foreach ( $rates as $rate ) {
			$region_parts = array( $rate['country'] );

			if ( ! empty( $rate['state'] ) ) {
				$region_parts[] = $rate['state'];
			}

			if ( ! empty( $rate['city'] ) ) {
				$region_parts[] = $rate['city'];
			}

			$regions[] = implode( '-', $region_parts );
		}

		return implode( ', ', array_unique( $regions ) );
	}
}
