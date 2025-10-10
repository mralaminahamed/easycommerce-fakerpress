<?php
/**
 * Location Generator
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Location;
use WP_Error;
use Exception;

/**
 * Location Generator Class
 *
 * Generates fake location data (countries, states, cities) for EasyCommerce
 *
 * @since 1.0.0
 */
class Location_Generator extends Generator {

	/**
	 * Generation parameters from REST API
	 *
	 * @var array
	 */
	private array $generation_params = array();

	/**
	 * Set generation parameters
	 *
	 * @since 1.0.0
	 *
	 * @param array $params Generation parameters.
	 *
	 * @return void
	 */
	public function set_generation_params( array $params ): void {
		$this->generation_params = $params;
	}

	/**
	 * Get the resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'location';
	}

	/**
	 * Generate a single location data entry
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Single location data, error, or false on failure.
	 */
	protected function generate_single_item() {
		// Check if EasyCommerce Location class exists.
		if ( ! class_exists( Location::class ) ) {
			return new WP_Error( 'missing_model', __( 'EasyCommerce Location model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' ) );
		}

		// Create comprehensive location data structure.
		$location_data = $this->create_location_data_structure();

		// Save to EasyCommerce location system.
		$result = $this->save_location_data( $location_data );
		if ( ! $result ) {
			return new WP_Error( 'location_creation_failed', __( 'Failed to create location data using EasyCommerce model.', 'easycommerce-fakerpress' ) );
		}

		return array(
			'countries_created' => count( $location_data ),
			'total_states'      => $this->count_states( $location_data ),
			'total_cities'      => $this->count_cities( $location_data ),
			'data_file_path'    => $this->get_location_file_path(),
			'created_date'      => current_time( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Create comprehensive location data structure
	 *
	 * @since 1.0.0
	 *
	 * @return array Complete location hierarchy data.
	 */
	private function create_location_data_structure(): array {
		$countries = array();

		// Generate countries based on parameters.
		$country_configs = $this->get_country_configurations();

		foreach ( $country_configs as $config ) {
			$country           = $this->generate_country_data( $config );
			$country['states'] = $this->generate_states_for_country( $config );

			$countries[] = $country;
		}

		return $countries;
	}

	/**
	 * Get filtered country configurations based on parameters
	 *
	 * @since 1.0.0
	 *
	 * @return array Country configuration data.
	 */
	private function get_country_configurations(): array {
		// Get filtering parameters.
		$regions       = $this->generation_params['regions'] ?? array();
		$countries     = $this->generation_params['countries'] ?? array();
		$max_countries = $this->generation_params['max_countries'] ?? 10;

		// Full country configurations.
		$all_countries = array(
			array(
				'id'               => 1,
				'name'             => 'United States',
				'iso2'             => 'US',
				'iso3'             => 'USA',
				'phone_code'       => '+1',
				'capital'          => 'Washington D.C.',
				'currency'         => 'USD',
				'currency_name'    => 'US Dollar',
				'currency_symbol'  => '$',
				'region'           => 'Americas',
				'subregion'        => 'Northern America',
				'states_count'     => 50,
				'cities_per_state' => 15,
			),
			array(
				'id'               => 2,
				'name'             => 'Canada',
				'iso2'             => 'CA',
				'iso3'             => 'CAN',
				'phone_code'       => '+1',
				'capital'          => 'Ottawa',
				'currency'         => 'CAD',
				'currency_name'    => 'Canadian Dollar',
				'currency_symbol'  => 'C$',
				'region'           => 'Americas',
				'subregion'        => 'Northern America',
				'states_count'     => 13,
				'cities_per_state' => 12,
			),
			array(
				'id'               => 3,
				'name'             => 'United Kingdom',
				'iso2'             => 'GB',
				'iso3'             => 'GBR',
				'phone_code'       => '+44',
				'capital'          => 'London',
				'currency'         => 'GBP',
				'currency_name'    => 'British Pound',
				'currency_symbol'  => '£',
				'region'           => 'Europe',
				'subregion'        => 'Northern Europe',
				'states_count'     => 4,
				'cities_per_state' => 20,
			),
			array(
				'id'               => 4,
				'name'             => 'Australia',
				'iso2'             => 'AU',
				'iso3'             => 'AUS',
				'phone_code'       => '+61',
				'capital'          => 'Canberra',
				'currency'         => 'AUD',
				'currency_name'    => 'Australian Dollar',
				'currency_symbol'  => 'A$',
				'region'           => 'Oceania',
				'subregion'        => 'Australia and New Zealand',
				'states_count'     => 8,
				'cities_per_state' => 10,
			),
			array(
				'id'               => 5,
				'name'             => 'Germany',
				'iso2'             => 'DE',
				'iso3'             => 'DEU',
				'phone_code'       => '+49',
				'capital'          => 'Berlin',
				'currency'         => 'EUR',
				'currency_name'    => 'Euro',
				'currency_symbol'  => '€',
				'region'           => 'Europe',
				'subregion'        => 'Western Europe',
				'states_count'     => 16,
				'cities_per_state' => 8,
			),
			array(
				'id'               => 6,
				'name'             => 'France',
				'iso2'             => 'FR',
				'iso3'             => 'FRA',
				'phone_code'       => '+33',
				'capital'          => 'Paris',
				'currency'         => 'EUR',
				'currency_name'    => 'Euro',
				'currency_symbol'  => '€',
				'region'           => 'Europe',
				'subregion'        => 'Western Europe',
				'states_count'     => 18,
				'cities_per_state' => 6,
			),
		);

		// Apply filters based on parameters.
		$filtered_countries = $all_countries;

		// Filter by regions if specified.
		if ( ! empty( $regions ) ) {
			$filtered_countries = array_filter(
				$filtered_countries,
				static function ( $country ) use ( $regions ) {
					return in_array( $country['region'], $regions, true ) ||
							in_array( $country['subregion'], $regions, true );
				}
			);
		}

		// Filter by specific countries if specified.
		if ( ! empty( $countries ) ) {
			$filtered_countries = array_filter(
				$filtered_countries,
				static function ( $country ) use ( $countries ) {
					return in_array( $country['iso2'], $countries, true ) ||
							in_array( $country['iso3'], $countries, true ) ||
							in_array( $country['name'], $countries, true );
				}
			);
		}

		// Limit number of countries.
		if ( count( $filtered_countries ) > $max_countries ) {
			$filtered_countries = array_slice( $filtered_countries, 0, $max_countries );
		}

		return $filtered_countries;
	}

	/**
	 * Generate country data from configuration
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Country configuration.
	 *
	 * @return array Country data.
	 */
	private function generate_country_data( array $config ): array {
		return array(
			'id'              => $config['id'],
			'name'            => $config['name'],
			'iso2'            => $config['iso2'],
			'iso3'            => $config['iso3'],
			'phone_code'      => $config['phone_code'],
			'capital'         => $config['capital'],
			'currency'        => $config['currency'],
			'currency_name'   => $config['currency_name'],
			'currency_symbol' => $config['currency_symbol'],
			'region'          => $config['region'],
			'subregion'       => $config['subregion'],
			'latitude'        => $this->faker->latitude(),
			'longitude'       => $this->faker->longitude(),
			'timezones'       => $this->generate_timezones_for_country( $config['iso2'] ),
		);
	}

	/**
	 * Generate states for a country
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Country configuration.
	 *
	 * @return array States data.
	 */
	private function generate_states_for_country( array $config ): array {
		$states      = array();
		$state_names = $this->get_realistic_state_names( $config['iso2'] );
		$name_count  = count( $state_names );

		$max_count = min( $config['states_count'], $name_count );
		for ( $i = 1; $i <= $max_count; $i++ ) {
			$state_name = $state_names[ $i - 1 ];
			$state      = array(
				'id'         => $i,
				'name'       => $state_name,
				'state_code' => $this->generate_state_code( $state_name, $config['iso2'] ),
				'latitude'   => $this->faker->latitude(),
				'longitude'  => $this->faker->longitude(),
				'cities'     => $this->generate_cities_for_state( $i, $config['cities_per_state'] ),
			);

			$states[] = $state;
		}

		return $states;
	}

	/**
	 * Generate cities for a state
	 *
	 * @since 1.0.0
	 *
	 * @param int $state_id State ID.
	 * @param int $cities_count Number of cities to generate.
	 *
	 * @return array Cities data.
	 */
	private function generate_cities_for_state( int $state_id, int $cities_count ): array {
		$cities = array();

		for ( $i = 1; $i <= $cities_count; $i++ ) {
			$city_id  = ( $state_id * 1000 ) + $i;
			$cities[] = array(
				'id'        => $city_id,
				'name'      => $this->faker->city,
				'latitude'  => $this->faker->latitude(),
				'longitude' => $this->faker->longitude(),
			);
		}

		return $cities;
	}

	/**
	 * Get realistic state names for different countries
	 *
	 * @since 1.0.0
	 *
	 * @param string $country_code ISO2 country code.
	 *
	 * @return array State names.
	 */
	private function get_realistic_state_names( string $country_code ): array {
		$state_names = array(
			'US' => array(
				'California',
				'Texas',
				'Florida',
				'New York',
				'Pennsylvania',
				'Illinois',
				'Ohio',
				'Georgia',
				'North Carolina',
				'Michigan',
				'New Jersey',
				'Virginia',
				'Washington',
				'Arizona',
				'Massachusetts',
				'Tennessee',
				'Indiana',
				'Maryland',
				'Missouri',
				'Wisconsin',
				'Colorado',
				'Minnesota',
				'South Carolina',
				'Alabama',
				'Louisiana',
				'Kentucky',
				'Oregon',
				'Oklahoma',
				'Connecticut',
				'Utah',
				'Iowa',
				'Nevada',
				'Arkansas',
				'Mississippi',
				'Kansas',
				'New Mexico',
				'Nebraska',
				'West Virginia',
				'Idaho',
				'Hawaii',
				'New Hampshire',
				'Maine',
				'Montana',
				'Rhode Island',
				'Delaware',
				'South Dakota',
				'North Dakota',
				'Alaska',
				'Vermont',
				'Wyoming',
			),
			'CA' => array(
				'Ontario',
				'Quebec',
				'British Columbia',
				'Alberta',
				'Manitoba',
				'Saskatchewan',
				'Nova Scotia',
				'New Brunswick',
				'Newfoundland and Labrador',
				'Prince Edward Island',
				'Northwest Territories',
				'Yukon',
				'Nunavut',
			),
			'GB' => array( 'England', 'Scotland', 'Wales', 'Northern Ireland' ),
			'AU' => array(
				'New South Wales',
				'Victoria',
				'Queensland',
				'Western Australia',
				'South Australia',
				'Tasmania',
				'Australian Capital Territory',
				'Northern Territory',
			),
			'DE' => array(
				'Baden-Württemberg',
				'Bayern',
				'Berlin',
				'Brandenburg',
				'Bremen',
				'Hamburg',
				'Hessen',
				'Mecklenburg-Vorpommern',
				'Niedersachsen',
				'Nordrhein-Westfalen',
				'Rheinland-Pfalz',
				'Saarland',
				'Sachsen',
				'Sachsen-Anhalt',
				'Schleswig-Holstein',
				'Thüringen',
			),
			'FR' => array(
				'Île-de-France',
				'Auvergne-Rhône-Alpes',
				'Hauts-de-France',
				'Nouvelle-Aquitaine',
				'Occitanie',
				'Grand Est',
				'Provence-Alpes-Côte d\'Azur',
				'Pays de la Loire',
				'Bretagne',
				'Normandie',
				'Bourgogne-Franche-Comté',
				'Centre-Val de Loire',
				'Corse',
				'Guadeloupe',
				'Martinique',
				'Guyane',
				'La Réunion',
				'Mayotte',
			),
		);

		return $state_names[ $country_code ] ?? array( 'Default State' );
	}

	/**
	 * Generate state code from state name
	 *
	 * @since 1.0.0
	 *
	 * @param string $state_name State name.
	 * @param string $country_code ISO2 country code.
	 *
	 * @return string State code.
	 */
	private function generate_state_code( string $state_name, string $country_code ): string {
		// Generate appropriate state codes based on country.
		switch ( $country_code ) {
			case 'US':
				$state_codes = array(
					'California'   => 'CA',
					'Texas'        => 'TX',
					'Florida'      => 'FL',
					'New York'     => 'NY',
					'Pennsylvania' => 'PA',
					'Illinois'     => 'IL',
					'Ohio'         => 'OH',
					'Georgia'      => 'GA',
				);

				return $state_codes[ $state_name ] ?? strtoupper( substr( $state_name, 0, 2 ) );
			case 'CA':
				$state_codes = array(
					'Ontario'          => 'ON',
					'Quebec'           => 'QC',
					'British Columbia' => 'BC',
					'Alberta'          => 'AB',
				);

				return $state_codes[ $state_name ] ?? strtoupper( substr( $state_name, 0, 2 ) );
			default:
				return strtoupper( substr( $state_name, 0, 2 ) );
		}
	}

	/**
	 * Generate timezones for a country
	 *
	 * @since 1.0.0
	 *
	 * @param string $country_code ISO2 country code.
	 *
	 * @return array Timezone data.
	 */
	private function generate_timezones_for_country( string $country_code ): array {
		$timezone_configs = array(
			'US' => array(
				array(
					'zoneName'      => 'America/New_York',
					'gmtOffsetName' => 'UTC-05:00',
				),
				array(
					'zoneName'      => 'America/Chicago',
					'gmtOffsetName' => 'UTC-06:00',
				),
				array(
					'zoneName'      => 'America/Denver',
					'gmtOffsetName' => 'UTC-07:00',
				),
				array(
					'zoneName'      => 'America/Los_Angeles',
					'gmtOffsetName' => 'UTC-08:00',
				),
			),
			'CA' => array(
				array(
					'zoneName'      => 'America/Toronto',
					'gmtOffsetName' => 'UTC-05:00',
				),
				array(
					'zoneName'      => 'America/Vancouver',
					'gmtOffsetName' => 'UTC-08:00',
				),
			),
			'GB' => array(
				array(
					'zoneName'      => 'Europe/London',
					'gmtOffsetName' => 'UTC+00:00',
				),
			),
			'AU' => array(
				array(
					'zoneName'      => 'Australia/Sydney',
					'gmtOffsetName' => 'UTC+10:00',
				),
				array(
					'zoneName'      => 'Australia/Melbourne',
					'gmtOffsetName' => 'UTC+10:00',
				),
			),
			'DE' => array(
				array(
					'zoneName'      => 'Europe/Berlin',
					'gmtOffsetName' => 'UTC+01:00',
				),
			),
			'FR' => array(
				array(
					'zoneName'      => 'Europe/Paris',
					'gmtOffsetName' => 'UTC+01:00',
				),
			),
		);

		return $timezone_configs[ $country_code ] ?? array(
			array(
				'zoneName'      => 'UTC',
				'gmtOffsetName' => 'UTC+00:00',
			),
		);
	}

	/**
	 * Save location data to EasyCommerce system
	 *
	 * @since 1.0.0
	 *
	 * @param array $location_data Complete location data.
	 *
	 * @return bool True on success, false on failure.
	 */
	private function save_location_data( array $location_data ): bool {
		try {
			$upload_dir       = wp_upload_dir();
			$easycommerce_dir = $upload_dir['basedir'] . '/easycommerce';

			// Create directory if it doesn't exist.
			if ( ! file_exists( $easycommerce_dir ) ) {
				wp_mkdir_p( $easycommerce_dir );
			}

			$json_path = $easycommerce_dir . '/locations.json';
			$json_data = wp_json_encode( $location_data, JSON_PRETTY_PRINT );

			// Use WP_Filesystem instead of direct file operations.
			global $wp_filesystem;

			if ( ! $wp_filesystem ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}

			return $wp_filesystem->put_contents( $json_path, $json_data, FS_CHMOD_FILE );
		} catch ( Exception $e ) {
			$this->log( 'Failed to save location data: ' . $e->getMessage(), 'error' );

			return false;
		}
	}

	/**
	 * Count total number of states in location data
	 *
	 * @since 1.0.0
	 *
	 * @param array $location_data Location data array.
	 *
	 * @return int Total states count.
	 */
	private function count_states( array $location_data ): int {
		$count = 0;
		foreach ( $location_data as $country ) {
			$count += count( $country['states'] );
		}

		return $count;
	}

	/**
	 * Count total number of cities in location data
	 *
	 * @since 1.0.0
	 *
	 * @param array $location_data Location data array.
	 *
	 * @return int Total cities count.
	 */
	private function count_cities( array $location_data ): int {
		$count = 0;
		foreach ( $location_data as $country ) {
			foreach ( $country['states'] as $state ) {
				$count += count( $state['cities'] );
			}
		}

		return $count;
	}

	/**
	 * Get the location file path
	 *
	 * @since 1.0.0
	 *
	 * @return string Location file path.
	 */
	private function get_location_file_path(): string {
		$upload_dir = wp_upload_dir();

		return $upload_dir['basedir'] . '/easycommerce/locations.json';
	}
}
