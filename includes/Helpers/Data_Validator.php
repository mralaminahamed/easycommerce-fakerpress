<?php
/**
 * Data Validation Helper for Cross-Generator Relationships
 *
 * @since   2.1.0
 * @package EasyCommerceFakerPress\Helpers
 */

namespace EasyCommerceFakerPress\Helpers;

use EasyCommerce\Models\Customer;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Location;
use EasyCommerce\Models\Database;

/**
 * Data Validator Class
 *
 * Provides validation and relationship checking across generators
 *
 * @since 2.1.0
 */
class Data_Validator {

	/**
	 * Validate customer exists and is accessible
	 *
	 * @since 2.1.0
	 *
	 * @param int $customer_id Customer ID to validate.
	 *
	 * @return bool True if customer exists and is valid.
	 */
	public static function validate_customer( int $customer_id ): bool {
		if ( $customer_id <= 0 ) {
			return false;
		}

		try {
			$customer = new Customer( $customer_id );

			return $customer->exists() && $customer->get_role() === 'customer';
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Validate product exists and can be used for orders/variations
	 *
	 * @since 2.1.0
	 *
	 * @param int $product_id Product ID to validate.
	 *
	 * @return bool True if product exists and is valid.
	 */
	public static function validate_product( int $product_id ): bool {
		if ( $product_id <= 0 ) {
			return false;
		}

		try {
			$product = new Product( $product_id );

			return $product->exists() && in_array(
				$product->get_status(),
				array( 'publish', 'private' ),
				true
			);
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Get available customers for generator use
	 *
	 * @since 2.1.0
	 *
	 * @param array $filters Optional filters array.
	 *
	 * @return array Array of customer data.
	 */
	public static function get_available_customers( array $filters = array() ): array {
		$defaults = array(
			'role'     => 'customer',
			'per_page' => 50,
			'status'   => 'active',
		);

		$query_params = array_merge( $defaults, $filters );

		try {
			$customer_data = Customer::list(
				$query_params['role'],
				null,
				1,
				$query_params['per_page']
			);

			return $customer_data['users'] ?? array();
		} catch ( \Exception $e ) {
			return array();
		}
	}

	/**
	 * Get available products for generator use
	 *
	 * @since 2.1.0
	 *
	 * @param array $filters Optional filters array.
	 *
	 * @return array Array of product data.
	 */
	public static function get_available_products( array $filters = array() ): array {
		$defaults = array(
			'per_page' => 50,
			'status'   => array( 'publish', 'private' ),
		);

		$query_params = array_merge( $defaults, $filters );

		try {
			$product_data = Product::list( $query_params );

			return $product_data['products'] ?? array();
		} catch ( \Exception $e ) {
			return array();
		}
	}

	/**
	 * Get available orders for generator use
	 *
	 * @since 2.1.0
	 *
	 * @param array $filters Optional filters array.
	 *
	 * @return array Array of order data.
	 */
	public static function get_available_orders( array $filters = array() ): array {
		$defaults = array(
			'per_page' => 100,
			'status'   => array( 'completed', 'processing' ),
		);

		$query_params = array_merge( $defaults, $filters );

		try {
			$order_data = Order::list( $query_params );

			return $order_data['orders'] ?? array();
		} catch ( \Exception $e ) {
			return array();
		}
	}

	/**
	 * Validate geographic location data
	 *
	 * @since 2.1.0
	 *
	 * @param string $country_code Country ISO code.
	 * @param string $state_code Optional state code.
	 * @param string $city Optional city name.
	 *
	 * @return bool True if location is valid.
	 */
	public static function validate_location( string $country_code, string $state_code = '', string $city = '' ): bool {
		// Basic country code validation.
		if ( empty( $country_code ) || strlen( $country_code ) !== 2 ) {
			return false;
		}

		// If we have location model, use it for validation.
		if ( class_exists( Location::class ) ) {
			try {
				// This would depend on Location model implementation.
				return true; // Placeholder - implement based on Location model API.
			} catch ( \Exception $e ) {
				return false;
			}
		}

		return true; // Basic validation passes.
	}

	/**
	 * Check if sufficient data exists for cross-generator relationships
	 *
	 * @since 2.1.0
	 *
	 * @param string $generator_type Generator type needing data.
	 *
	 * @return array Status array with recommendations.
	 */
	public static function check_data_availability( string $generator_type ): array {
		$status = array(
			'ready'           => true,
			'missing_data'    => array(),
			'recommendations' => array(),
			'counts'          => array(),
		);

		// Check based on generator type requirements.
		switch ( $generator_type ) {
			case 'order':
				// Orders need customers and products.
				$customers = self::get_available_customers();
				$products  = self::get_available_products();

				$status['counts']['customers'] = count( $customers );
				$status['counts']['products']  = count( $products );

				if ( empty( $customers ) ) {
					$status['ready']             = false;
					$status['missing_data'][]    = 'customers';
					$status['recommendations'][] = 'Generate customers first using the Customer Generator';
				}

				if ( empty( $products ) ) {
					$status['ready']             = false;
					$status['missing_data'][]    = 'products';
					$status['recommendations'][] = 'Generate products first using the Product Generator';
				}
				break;

			case 'transaction':
				// Transactions need orders.
				$orders = self::get_available_orders();

				$status['counts']['orders'] = count( $orders );

				if ( empty( $orders ) ) {
					$status['ready']             = false;
					$status['missing_data'][]    = 'orders';
					$status['recommendations'][] = 'Generate orders first using the Order Generator';
				}
				break;

			case 'cart_session':
				// Cart sessions need products, customers are optional.
				$products  = self::get_available_products();
				$customers = self::get_available_customers();

				$status['counts']['products']  = count( $products );
				$status['counts']['customers'] = count( $customers );

				if ( empty( $products ) ) {
					$status['ready']             = false;
					$status['missing_data'][]    = 'products';
					$status['recommendations'][] = 'Generate products first using the Product Generator';
				}

				if ( empty( $customers ) ) {
					$status['recommendations'][] = 'Consider generating customers for more realistic cart sessions';
				}
				break;

			case 'product_variation':
				// Product variations need products.
				$products = self::get_available_products();

				$status['counts']['products'] = count( $products );

				if ( empty( $products ) ) {
					$status['ready']             = false;
					$status['missing_data'][]    = 'products';
					$status['recommendations'][] = 'Generate products first using the Product Generator';
				}
				break;
		}

		return $status;
	}

	/**
	 * Sanitize and validate generation parameters
	 *
	 * @since 2.1.0
	 *
	 * @param array  $params Parameters to validate.
	 * @param string $context Context for validation (generator type).
	 *
	 * @return array Sanitized and validated parameters.
	 */
	public static function sanitize_generation_params( array $params, string $context = '' ): array {
		$sanitized = array();

		foreach ( $params as $key => $value ) {
			switch ( $key ) {
				case 'count':
					$sanitized[ $key ] = max( 1, min( 100, (int) $value ) );
					break;

				case 'specific_customer_id':
					$customer_id       = (int) $value;
					$sanitized[ $key ] = self::validate_customer( $customer_id ) ? $customer_id : null;
					break;

				case 'specific_product_id':
					$product_id        = (int) $value;
					$sanitized[ $key ] = self::validate_product( $product_id ) ? $product_id : null;
					break;

				case 'customer_type':
					$valid_types       = array( 'existing', 'new', 'mixed', 'specific', 'guest_only' );
					$sanitized[ $key ] = in_array( $value, $valid_types, true ) ? $value : 'mixed';
					break;

				case 'abandonment_rate':
				case 'guest_cart_ratio':
					$sanitized[ $key ] = max( 0, min( 100, (int) $value ) );
					break;

				case 'regions':
				case 'countries':
				case 'product_types':
					$sanitized[ $key ] = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
					break;

				case 'date_range':
					if ( is_array( $value ) ) {
						$sanitized[ $key ] = array(
							'start' => isset( $value['start'] ) ? sanitize_text_field( $value['start'] ) : '',
							'end'   => isset( $value['end'] ) ? sanitize_text_field( $value['end'] ) : '',
						);
					}
					break;

				default:
					// Generic sanitization for other parameters.
					if ( is_string( $value ) ) {
						$sanitized[ $key ] = sanitize_text_field( $value );
					} elseif ( is_array( $value ) ) {
						$sanitized[ $key ] = array_map( 'sanitize_text_field', $value );
					} else {
						$sanitized[ $key ] = $value;
					}
					break;
			}
		}

		return $sanitized;
	}

	/**
	 * Log validation issues for debugging
	 *
	 * @since 2.1.0
	 *
	 * @param string $message Log message.
	 * @param string $context Context (generator type).
	 * @param array  $data Additional data to log.
	 *
	 * @return void
	 */
	public static function log_validation_issue( string $message, string $context = '', array $data = array() ): void {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$log_entry = sprintf(
				'[EasyCommerce FakerPress][Data_Validator][%s] %s %s',
				$context,
				$message,
				! empty( $data ) ? '- Data: ' . wp_json_encode( $data ) : ''
			);

			error_log( $log_entry ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
