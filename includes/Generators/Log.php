<?php
/**
 * Log Generator.
 *
 * @since   2.1.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
use EasyCommerce\Models\Log as LogModel;
use EasyCommerceFakerPress\Abstracts\Generator;
use WP_Error;

/**
 * Log Generator Class
 *
 * Generates realistic activity log entries for EasyCommerce.
 *
 * @since 2.1.0
 */
class Log extends Generator {

	/**
	 * Object/action matrix — which actions are valid per object type.
	 *
	 * @since 2.1.0
	 * @var array<string, string[]>
	 */
	private const OBJECT_ACTIONS = array(
		'order'       => array( 'created', 'updated', 'deleted', 'viewed', 'payment', 'refunded' ),
		'product'     => array( 'created', 'updated', 'deleted', 'viewed' ),
		'customer'    => array( 'created', 'updated', 'deleted', 'login', 'logout' ),
		'coupon'      => array( 'created', 'updated', 'deleted', 'viewed' ),
		'refund'      => array( 'created', 'updated', 'failed' ),
		'cart'        => array( 'created', 'updated', 'deleted', 'checkout' ),
		'transaction' => array( 'created', 'updated', 'failed', 'payment' ),
		'system'      => array( 'created', 'updated', 'failed', 'login', 'logout' ),
	);

	/**
	 * Note templates per action.
	 *
	 * @since 2.1.0
	 * @var array<string, string[]>
	 */
	private const ACTION_NOTES = array(
		'created'  => array( 'New %s record created.', '%s has been added to the system.', 'Created %s successfully.' ),
		'updated'  => array( '%s details updated.', 'Changes saved for %s.', 'Updated %s record.' ),
		'deleted'  => array( '%s has been removed.', 'Deleted %s from the system.', '%s record deleted.' ),
		'viewed'   => array( '%s was accessed.', 'Viewed %s details.', '%s record opened.' ),
		'checkout' => array( 'Checkout initiated for %s.', '%s checkout in progress.', 'Cart checkout started.' ),
		'payment'  => array( 'Payment processed for %s.', '%s payment completed.', 'Payment confirmed for %s.' ),
		'refunded' => array( 'Refund issued for %s.', '%s refunded successfully.', 'Processed refund on %s.' ),
		'login'    => array( 'User logged in via %s.', '%s session started.', 'Login event recorded.' ),
		'logout'   => array( 'User logged out of %s.', '%s session ended.', 'Logout recorded.' ),
		'failed'   => array( '%s operation failed.', 'Error encountered in %s.', 'Failed to process %s.' ),
	);

	/**
	 * Get the resource type name.
	 *
	 * @since 2.1.0
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'log';
	}

	/**
	 * Get supported log severity types.
	 *
	 * @since 2.1.0
	 *
	 * @return array Supported types.
	 */
	public function get_supported_types(): array {
		return array(
			'info'    => __( 'Info', 'easycommerce-fakerpress' ),
			'warning' => __( 'Warning', 'easycommerce-fakerpress' ),
			'error'   => __( 'Error', 'easycommerce-fakerpress' ),
			'success' => __( 'Success', 'easycommerce-fakerpress' ),
		);
	}

	/**
	 * Get generator description.
	 *
	 * @since 2.1.0
	 *
	 * @return string Description.
	 */
	public function get_description(): string {
		return 'Generates realistic activity log entries covering orders, products, customers, coupons, refunds, and system events with contextually appropriate actions and notes.';
	}

	/**
	 * Generate a single log entry.
	 *
	 * @since 2.1.0
	 *
	 * @return array|WP_Error Single log data, or WP_Error on failure.
	 */
	protected function generate_single_item() {
		if ( ! class_exists( LogModel::class ) ) {
			return new WP_Error(
				'missing_model',
				__( 'EasyCommerce Log model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' )
			);
		}

		$allowed_objects = isset( $this->generation_params['objects'] )
			? $this->generation_params['objects']
			: array_keys( self::OBJECT_ACTIONS );

		$object  = $this->get_faker()->randomElement( $allowed_objects );
		$actions = isset( self::OBJECT_ACTIONS[ $object ] ) ? self::OBJECT_ACTIONS[ $object ] : array( 'created', 'updated' );

		// When type param is supplied, bias action accordingly.
		$type_pool = isset( $this->generation_params['log_types'] )
			? $this->generation_params['log_types']
			: array( 'info', 'info', 'info', 'success', 'warning', 'error' );

		$log_type = $this->get_faker()->randomElement( $type_pool );

		// Error/warning types favour failure actions.
		if ( in_array( $log_type, array( 'error', 'warning' ), true ) && in_array( 'failed', $actions, true ) ) {
			$action = $this->get_faker()->boolean( 60 ) ? 'failed' : $this->get_faker()->randomElement( $actions );
		} else {
			$action = $this->get_faker()->randomElement( $actions );
		}

		$templates = isset( self::ACTION_NOTES[ $action ] ) ? self::ACTION_NOTES[ $action ] : array( 'Event on %s.' );
		$note      = sprintf( $this->get_faker()->randomElement( $templates ), $object );

		$log    = new LogModel();
		$log_id = $log->add(
			array(
				'object'     => $object,
				'action'     => $action,
				'object_id'  => $this->get_faker()->optional( 0.8 )->numberBetween( 1, 9999 ),
				'user_id'    => $this->get_faker()->optional( 0.7 )->numberBetween( 1, 100 ),
				'note'       => $note,
				'ip_address' => $this->get_faker()->ipv4(),
				'seen'       => $this->get_faker()->boolean( 40 ) ? 1 : 0,
				'type'       => $log_type,
				'meta'       => null,
				'is_public'  => $this->get_faker()->boolean( 80 ) ? 1 : 0,
			)
		);

		if ( ! $log_id ) {
			return new WP_Error(
				'log_creation_failed',
				__( 'Failed to create log entry.', 'easycommerce-fakerpress' )
			);
		}

		return array(
			'id'        => $log_id,
			'object'    => $object,
			'action'    => $action,
			'object_id' => $log->get_object_id(),
			'user_id'   => $log->get_user_id(),
			'type'      => $log_type,
			'note'      => $note,
			'is_public' => $log->get_is_public(),
		);
	}
}
