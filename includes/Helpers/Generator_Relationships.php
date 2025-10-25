<?php
/**
 * Generator Relationships Helper
 *
 * @since   2.1.0
 * @package EasyCommerceFakerPress\Helpers
 */

namespace EasyCommerceFakerPress\Helpers;

/**
 * Generator Relationships Class
 *
 * Manages relationships and dependencies between different generators
 *
 * @since 2.1.0
 */
class Generator_Relationships {

	/**
	 * Generator dependency map
	 *
	 * @var array<string, array<string>>
	 */
	private static array $dependencies = array(
		'customer'          => array(),
		'location'          => array(),
		'product'           => array( 'location' ),
		'product_variation' => array( 'product' ),
		'coupon'            => array(),
		'tax_class'         => array( 'location' ),
		'shipping_plan'     => array( 'location' ),
		'order'             => array( 'customer', 'product', 'product_variation' ),
		'transaction'       => array( 'order', 'customer' ),
		'cart_session'      => array( 'product' ),
	);

	/**
	 * Get dependencies for a generator
	 *
	 * @since 2.1.0
	 *
	 * @param string $generator_type Generator type.
	 *
	 * @return array Array of required dependencies.
	 */
	public static function get_dependencies( string $generator_type ): array {
		return self::$dependencies[ $generator_type ] ?? array();
	}

	/**
	 * Check if generator dependencies are met
	 *
	 * @since 2.1.0
	 *
	 * @param string $generator_type Generator type to check.
	 *
	 * @return array Status array with dependency information.
	 */
	public static function check_dependencies( string $generator_type ): array {
		$dependencies = self::get_dependencies( $generator_type );
		$status       = array(
			'ready'                => true,
			'missing_dependencies' => array(),
			'dependency_counts'    => array(),
			'recommendations'      => array(),
		);

		foreach ( $dependencies as $dependency ) {
			$count = self::count_available_items( $dependency );

			$status['dependency_counts'][ $dependency ] = $count;

			if ( $count === 0 ) {
				$status['ready']                  = false;
				$status['missing_dependencies'][] = $dependency;
				$status['recommendations'][]      = sprintf(
					'Generate %s first using the %s Generator',
					self::get_dependency_display_name( $dependency ),
					self::get_generator_display_name( $dependency )
				);
			}
		}

		return $status;
	}

	/**
	 * Get recommended generation order for multiple generators
	 *
	 * @since 2.1.0
	 *
	 * @param array $generator_types Array of generator types.
	 *
	 * @return array Ordered array of generators.
	 */
	public static function get_generation_order( array $generator_types ): array {
		// Topological sort of generators based on dependencies.
		$sorted       = array();
		$visited      = array();
		$temp_visited = array();

		foreach ( $generator_types as $generator ) {
			if ( ! isset( $visited[ $generator ] ) ) {
				self::topological_sort_visit( $generator, $visited, $temp_visited, $sorted );
			}
		}

		return array_reverse( $sorted );
	}

	/**
	 * Topological sort helper function
	 *
	 * @since 2.1.0
	 *
	 * @param string $generator Generator to visit.
	 * @param array  $visited Visited nodes.
	 * @param array  $temp_visited Temporarily visited nodes.
	 * @param array  $sorted Sorted result array.
	 *
	 * @return void
	 */
	private static function topological_sort_visit( string $generator, array &$visited, array &$temp_visited, array &$sorted ): void {
		if ( isset( $temp_visited[ $generator ] ) ) {
			// Circular dependency detected - handle gracefully.
			return;
		}

		if ( ! isset( $visited[ $generator ] ) ) {
			$temp_visited[ $generator ] = true;

			$dependencies = self::get_dependencies( $generator );
			foreach ( $dependencies as $dependency ) {
				self::topological_sort_visit( $dependency, $visited, $temp_visited, $sorted );
			}

			$visited[ $generator ] = true;
			unset( $temp_visited[ $generator ] );
			$sorted[] = $generator;
		}
	}

	/**
	 * Count available items for a dependency type
	 *
	 * @since 2.1.0
	 *
	 * @param string $dependency_type Dependency type.
	 *
	 * @return int Count of available items.
	 */
	private static function count_available_items( string $dependency_type ): int {
		switch ( $dependency_type ) {
			case 'customer':
				$customers = Data_Validator::get_available_customers();

				return count( $customers );

			case 'product':
				$products = Data_Validator::get_available_products();

				return count( $products );

			case 'product_variation':
				// Count variations using database query.
				global $wpdb;
				$count = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ec_product_variations WHERE status != 'trash'"
				);

				return (int) $count;

			case 'order':
				$orders = Data_Validator::get_available_orders();

				return count( $orders );

			case 'location':
				// Check if location data exists.
				global $wpdb;
				$count = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ec_locations WHERE type = 'country'"
				);

				return (int) $count;

			case 'tax_class':
				// Check tax classes.
				global $wpdb;
				$count = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ec_tax_classes"
				);

				return (int) $count;

			case 'shipping_plan':
				// Check shipping plans.
				global $wpdb;
				$count = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$wpdb->prefix}ec_shipping_plans WHERE active = 1"
				);

				return (int) $count;

			default:
				return 0;
		}
	}

	/**
	 * Get display name for dependency
	 *
	 * @since 2.1.0
	 *
	 * @param string $dependency_type Dependency type.
	 *
	 * @return string Display name.
	 */
	private static function get_dependency_display_name( string $dependency_type ): string {
		$names = array(
			'customer'          => 'customers',
			'product'           => 'products',
			'product_variation' => 'product variations',
			'order'             => 'orders',
			'location'          => 'location data',
			'tax_class'         => 'tax classes',
			'shipping_plan'     => 'shipping plans',
		);

		return $names[ $dependency_type ] ?? $dependency_type;
	}

	/**
	 * Get generator display name
	 *
	 * @since 2.1.0
	 *
	 * @param string $generator_type Generator type.
	 *
	 * @return string Display name.
	 */
	private static function get_generator_display_name( string $generator_type ): string {
		$names = array(
			'customer'          => 'Customer',
			'product'           => 'Product',
			'product_variation' => 'Product Variation',
			'order'             => 'Order',
			'location'          => 'Location',
			'tax_class'         => 'Tax',
			'shipping_plan'     => 'Shipping Plan',
		);

		return $names[ $generator_type ] ?? ucfirst( str_replace( '_', ' ', $generator_type ) );
	}

	/**
	 * Get suggested batch sizes for generators
	 *
	 * @since 2.1.0
	 *
	 * @param string $generator_type Generator type.
	 *
	 * @return array Suggested batch sizes.
	 */
	public static function get_suggested_batch_sizes( string $generator_type ): array {
		$suggestions = array(
			'customer'          => array(
				'small'  => 10,
				'medium' => 25,
				'large'  => 50,
			),
			'product'           => array(
				'small'  => 5,
				'medium' => 15,
				'large'  => 30,
			),
			'product_variation' => array(
				'small'  => 15,
				'medium' => 40,
				'large'  => 100,
			),
			'order'             => array(
				'small'  => 10,
				'medium' => 30,
				'large'  => 75,
			),
			'transaction'       => array(
				'small'  => 20,
				'medium' => 50,
				'large'  => 100,
			),
			'cart_session'      => array(
				'small'  => 15,
				'medium' => 40,
				'large'  => 80,
			),
			'location'          => array(
				'small'  => 1,
				'medium' => 1,
				'large'  => 1,
			),
			'tax_class'         => array(
				'small'  => 3,
				'medium' => 6,
				'large'  => 10,
			),
			'shipping_plan'     => array(
				'small'  => 3,
				'medium' => 6,
				'large'  => 12,
			),
			'coupon'            => array(
				'small'  => 5,
				'medium' => 15,
				'large'  => 30,
			),
		);

		return $suggestions[ $generator_type ] ?? array(
			'small'  => 5,
			'medium' => 15,
			'large'  => 30,
		);
	}

	/**
	 * Validate cross-generator parameters for consistency
	 *
	 * @since 2.1.0
	 *
	 * @param array $generators_config Array of generator configurations.
	 *
	 * @return array Validation results.
	 */
	public static function validate_cross_generator_params( array $generators_config ): array {
		$validation = array(
			'valid'       => true,
			'warnings'    => array(),
			'conflicts'   => array(),
			'suggestions' => array(),
		);

		// Check for parameter conflicts.
		foreach ( $generators_config as $generator_type => $config ) {
			$count        = $config['count'] ?? 10;
			$dependencies = self::get_dependencies( $generator_type );

			// Check if requested count is reasonable given dependencies.
			foreach ( $dependencies as $dependency ) {
				$available_count   = self::count_available_items( $dependency );
				$dependency_config = $generators_config[ $dependency ] ?? array();
				$dependency_count  = $dependency_config['count'] ?? $available_count;

				// Warn if requesting more child items than parents.
				if ( $generator_type === 'product_variation' && $dependency === 'product' ) {
					$max_variations_per_product = 5; // Reasonable default.
					$max_possible               = $dependency_count * $max_variations_per_product;

					if ( $count > $max_possible ) {
						$validation['warnings'][] = sprintf(
							'Requested %d product variations but only %d products available. Consider generating more products first.',
							$count,
							$dependency_count
						);
					}
				}

				if ( $generator_type === 'order' && $dependency === 'customer' ) {
					$max_orders_per_customer = 10; // Reasonable default.
					$max_possible            = $dependency_count * $max_orders_per_customer;

					if ( $count > $max_possible ) {
						$validation['warnings'][] = sprintf(
							'Requested %d orders but only %d customers available. Some customers will have many orders.',
							$count,
							$dependency_count
						);
					}
				}
			}
		}

		return $validation;
	}
}
