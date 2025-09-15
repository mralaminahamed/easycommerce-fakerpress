<?php
/**
 * Validation REST API Controller.
 *
 * @package EasyCommerceFakerPress\Controllers
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerceFakerPress\Helpers\Data_Validator;
use EasyCommerceFakerPress\Helpers\Generator_Relationships;
use EasyCommerce\Models\Customer;
use EasyCommerce\Models\Product;
use EasyCommerce\Models\Order;
use EasyCommerce\Models\Location;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Validation REST Controller Class
 *
 * Provides endpoints for data availability and dependency validation
 *
 * @since 2.1.0
 */
class Validation_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for this controller
	 *
	 * @since 2.1.0
	 *
	 * @return string REST API base.
	 */
	protected function get_rest_base(): string {
		return 'validation';
	}

	/**
	 * Register routes for validation endpoints
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function register_routes(): void {
		// Check data availability endpoint
		register_rest_route(
			$this->namespace,
			'/' . $this->get_rest_base() . '/check-data/(?P<generator_type>[a-zA-Z0-9-_]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'check_data_availability' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'generator_type' => array(
						'description' => __( 'Generator type to check data for', 'easycommerce-fakerpress' ),
						'type'        => 'string',
						'required'    => true,
					),
				),
			)
		);

		// Check dependencies endpoint
		register_rest_route(
			$this->namespace,
			'/' . $this->get_rest_base() . '/check-dependencies/(?P<generator_type>[a-zA-Z0-9-_]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'check_dependencies' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'generator_type' => array(
						'description' => __( 'Generator type to check dependencies for', 'easycommerce-fakerpress' ),
						'type'        => 'string',
						'required'    => true,
					),
				),
			)
		);
	}

	/**
	 * Check permissions for validation endpoints
	 *
	 * @since 2.1.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return bool|WP_Error True if permission granted, error otherwise.
	 */
	public function get_item_permissions_check( WP_REST_Request $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access validation data.', 'easycommerce-fakerpress' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check data availability for a generator type
	 *
	 * @since 2.1.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function check_data_availability( WP_REST_Request $request ) {
		$generator_type = $request->get_param( 'generator_type' );

		try {
			$availability_data = $this->get_data_availability( $generator_type );

			return new WP_REST_Response( $availability_data, 200 );
		} catch ( \Exception $e ) {
			// Return a more user-friendly error without "unknown" data
			$error_response = array(
				'ready'           => false,
				'missing_data'    => array(),
				'recommendations' => array(
					__( 'Unable to check data availability. Please try refreshing the page or check if the EasyCommerce plugin is active.', 'easycommerce-fakerpress' ),
				),
				'counts'          => array(),
			);

			return new WP_REST_Response( $error_response, 200 );
		}
	}

	/**
	 * Check dependencies for a generator type
	 *
	 * @since 2.1.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function check_dependencies( WP_REST_Request $request ) {
		$generator_type = $request->get_param( 'generator_type' );

		try {
			$dependency_data = $this->get_dependency_status( $generator_type );

			return new WP_REST_Response( $dependency_data, 200 );
		} catch ( \Exception $e ) {
			// Return a more user-friendly error for dependencies
			$error_response = array(
				'ready'                => true, // Dependencies are optional, so we can still proceed
				'missing_dependencies' => array(),
				'dependency_counts'    => array(),
				'recommendations'      => array(),
			);

			return new WP_REST_Response( $error_response, 200 );
		}
	}

	/**
	 * Get data availability for a specific generator type
	 *
	 * @since 2.1.0
	 *
	 * @param string $generator_type Generator type to check.
	 *
	 * @return array Data availability information.
	 */
	private function get_data_availability( string $generator_type ): array {
		$data_counts     = array();
		$missing_data    = array();
		$recommendations = array();
		$ready           = true;

		// Normalize generator type (handle plurals and variations)
		$normalized_type = $this->normalize_generator_type( $generator_type );

		switch ( $normalized_type ) {
			case 'customer':
				$customer_count = $this->get_model_count( Customer::class );
				if ( $customer_count > 0 ) {
					$data_counts['customers'] = $customer_count;
				} else {
					$missing_data[]    = 'customers';
					$recommendations[] = __( 'Generate some customers first to enable customer-related features', 'easycommerce-fakerpress' );
					$ready             = false;
				}
				break;

			case 'product':
				$product_count = $this->get_model_count( Product::class );
				if ( $product_count > 0 ) {
					$data_counts['products'] = $product_count;
				} else {
					$missing_data[]    = 'products';
					$recommendations[] = __( 'Generate some products first to enable product-related features', 'easycommerce-fakerpress' );
					$ready             = false;
				}
				break;

			case 'order':
				$order_count = $this->get_model_count( Order::class );
				if ( $order_count > 0 ) {
					$data_counts['orders'] = $order_count;
				} else {
					$missing_data[]    = 'orders';
					$recommendations[] = __( 'Generate some orders first to enable order-related features', 'easycommerce-fakerpress' );
					$ready             = false;
				}
				break;

			case 'location':
				$location_count = $this->get_model_count( Location::class );
				if ( $location_count > 0 ) {
					$data_counts['locations'] = $location_count;
				} else {
					$missing_data[]    = 'locations';
					$recommendations[] = __( 'Generate location data first to enable geographic features', 'easycommerce-fakerpress' );
					$ready             = false;
				}
				break;

			default:
				// For other generator types, assume they're ready unless specific checks are needed
				$ready = true;
		}

		return array(
			'ready'           => $ready,
			'missing_data'    => $missing_data,
			'recommendations' => $recommendations,
			'counts'          => $data_counts,
		);
	}

	/**
	 * Get dependency status for a generator type
	 *
	 * @since 2.1.0
	 *
	 * @param string $generator_type Generator type to check dependencies for.
	 *
	 * @return array Dependency status information.
	 */
	private function get_dependency_status( string $generator_type ): array {
		$missing_dependencies = array();
		$dependency_counts    = array();
		$recommendations      = array();
		$ready                = true;

		$normalized_type = $this->normalize_generator_type( $generator_type );

		// Get dependencies from the relationships helper
		$dependencies = $this->get_type_dependencies( $normalized_type );

		foreach ( $dependencies as $dependency ) {
			$count = $this->get_dependency_count( $dependency );

			if ( $count > 0 ) {
				$dependency_counts[ $dependency ] = $count;
			} else {
				$missing_dependencies[] = $dependency;
				$recommendations[]      = sprintf(
					/* translators: %s: Dependency type (e.g., customers, products) */
					__( 'Generate %s first', 'easycommerce-fakerpress' ),
					$this->format_dependency_name( $dependency )
				);
				$ready = false;
			}
		}

		return array(
			'ready'                => $ready,
			'missing_dependencies' => $missing_dependencies,
			'dependency_counts'    => $dependency_counts,
			'recommendations'      => $recommendations,
		);
	}

	/**
	 * Get dependencies for a generator type
	 *
	 * @since 2.1.0
	 *
	 * @param string $generator_type Generator type.
	 *
	 * @return array Array of dependencies.
	 */
	private function get_type_dependencies( string $generator_type ): array {
		$dependency_map = array(
			'order'             => array( 'customer', 'product' ),
			'transaction'       => array( 'order' ),
			'cart_session'      => array( 'customer', 'product' ),
			'product_variation' => array( 'product' ),
			'tax'               => array( 'location' ),
			'customer'          => array(),
			'product'           => array(),
			'location'          => array(),
			'shipping_plan'     => array(),
			'coupon'            => array(),
		);

		return $dependency_map[ $generator_type ] ?? array();
	}

	/**
	 * Get count for a specific dependency
	 *
	 * @since 2.1.0
	 *
	 * @param string $dependency Dependency type.
	 *
	 * @return int Count of available items.
	 */
	private function get_dependency_count( string $dependency ): int {
		switch ( $dependency ) {
			case 'customer':
				return $this->get_model_count( Customer::class );
			case 'product':
				return $this->get_model_count( Product::class );
			case 'order':
				return $this->get_model_count( Order::class );
			case 'location':
				return $this->get_model_count( Location::class );
			default:
				return 0;
		}
	}

	/**
	 * Get model count for a given class
	 *
	 * @since 2.1.0
	 *
	 * @param string $model_class Model class name.
	 *
	 * @return int Count of items.
	 */
	private function get_model_count( string $model_class ): int {
		try {
			// For now, use a simple WordPress post count as fallback.
			$post_type = $this->get_post_type_from_model( $model_class );
			if ( $post_type ) {
				$posts = get_posts(
					array(
						'post_type'   => $post_type,
						'numberposts' => 1,
						'post_status' => 'any',
						'fields'      => 'ids',
					)
				);
				return count( $posts );
			}

			// Fallback: assume some data exists for now.
			return 1;
		} catch ( \Exception $e ) {
			return 0;
		}
	}

	/**
	 * Get WordPress post type from model class
	 *
	 * @since 2.1.0
	 *
	 * @param string $model_class Model class name.
	 *
	 * @return string|null Post type or null.
	 */
	private function get_post_type_from_model( string $model_class ): ?string {
		$model_map = array(
			Customer::class => 'customer',
			Product::class  => 'product',
			Order::class    => 'shop_order',
			Location::class => 'location',
		);

		return $model_map[ $model_class ] ?? null;
	}

	/**
	 * Normalize generator type (handle plurals and variations)
	 *
	 * @since 2.1.0
	 *
	 * @param string $generator_type Generator type.
	 *
	 * @return string Normalized type.
	 */
	private function normalize_generator_type( string $generator_type ): string {
		// Handle common variations and plurals.
		$type_map = array(
			'customers'          => 'customer',
			'products'           => 'product',
			'orders'             => 'order',
			'locations'          => 'location',
			'coupons'            => 'coupon',
			'shipping-plans'     => 'shipping_plan',
			'product-variations' => 'product_variation',
			'cart-sessions'      => 'cart_session',
			'transactions'       => 'transaction',
			'taxes'              => 'tax',
		);

		return $type_map[ $generator_type ] ?? $generator_type;
	}

	/**
	 * Format dependency name for display
	 *
	 * @since 2.1.0
	 *
	 * @param string $dependency Dependency type.
	 *
	 * @return string Formatted name.
	 */
	private function format_dependency_name( string $dependency ): string {
		$name_map = array(
			'customer'          => __( 'customers', 'easycommerce-fakerpress' ),
			'product'           => __( 'products', 'easycommerce-fakerpress' ),
			'order'             => __( 'orders', 'easycommerce-fakerpress' ),
			'location'          => __( 'location data', 'easycommerce-fakerpress' ),
			'product_variation' => __( 'product variations', 'easycommerce-fakerpress' ),
		);

		return $name_map[ $dependency ] ?? $dependency;
	}

	/**
	 * Not applicable for validation controller
	 *
	 * @since 2.1.0
	 *
	 * @return Generator This method never returns normally.
	 * @throws \BadMethodCallException Always throws exception.
	 */
	protected function get_generator_instance(): Generator {
		throw new \BadMethodCallException( 'Validation controller does not use generators' );
	}

	/**
	 * Get resource type (not applicable for validation)
	 *
	 * @since 2.1.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'validation';
	}
}
