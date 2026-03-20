<?php
/**
 * Abstract base class for all FakerPress MCP Ability execute callbacks.
 *
 * Each concrete Ability class maps one ability ID to one FakerPress REST
 * endpoint. The shared generate() method handles building the payload,
 * dispatching a WP_REST_Request internally (no HTTP round-trip), and
 * returning the response array to the Abilities API.
 *
 * Using WP_REST_Request internally keeps authentication trivial — the
 * current user is already verified by the permission_callback before
 * execute() is ever called.
 *
 * @package EasyCommerceFakerPress\MCP\Abilities
 * @since   2.1.0
 */

namespace EasyCommerceFakerPress\Abstracts;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use WP_REST_Request;

/**
 * Abstract_Ability
 *
 * @since 2.1.0
 */
abstract class Ability {

	/**
	 * The REST route base (e.g. "products", "customers", "orders").
	 * Concrete classes must define this constant.
	 *
	 * @since 2.1.0
	 */
	const REST_BASE = '';

	/**
	 * REST namespace shared by all FakerPress endpoints.
	 *
	 * @since 2.1.0
	 */
	const REST_NAMESPACE = 'easycommerce-fakerpress/v1';

	/**
	 * Entry point called by the Abilities API.
	 *
	 * @since 2.1.0
	 *
	 * @param array<string, mixed> $input Validated input from the MCP client.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function execute( array $input = array() ) {
		return static::dispatch( $input );
	}

	/**
	 * Dispatch an internal REST request and return the decoded response body.
	 *
	 * @since 2.1.0
	 *
	 * @param array<string, mixed> $params Parameters to forward as JSON body.
	 * @return array<string, mixed>|WP_Error
	 */
	protected static function dispatch( array $params ) {
		$route = '/' . static::REST_NAMESPACE . '/' . static::REST_BASE . '/generate';

		$request = new WP_REST_Request( 'POST', $route );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( (string) wp_json_encode( $params ) );

		$response = rest_do_request( $request );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$server = rest_get_server();
		$data   = $server->response_to_data( $response, false );

		if ( $response->is_error() ) {
			$status  = $response->get_status();
			$message = isset( $data['message'] ) ? (string) $data['message'] : __( 'Unknown REST error.', 'easycommerce-fakerpress' );
			return new WP_Error( 'ecfp_rest_error', $message, array( 'status' => $status ) );
		}

		return is_array( $data ) ? $data : array( 'message' => __( 'Unexpected response format.', 'easycommerce-fakerpress' ) );
	}

	/**
	 * Normalise the raw ability input, extracting the resource-specific
	 * parameters into the nested arrays the REST controller expects.
	 *
	 * Concrete classes override this when their endpoint expects nested params.
	 *
	 * @since 2.1.0
	 *
	 * @param array<string, mixed> $input Raw MCP input.
	 * @return array<string, mixed> Payload ready for the REST endpoint.
	 */
	protected static function build_payload( array $input ): array {
		return $input;
	}
}
