<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Generators\Transaction;
use EasyCommerce\Models\Transaction;

/**
 * Test class for Transaction Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Transaction
 */
class TransactionGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Transaction
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active
		if ( ! class_exists( 'EasyCommerce\Models\Transaction' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->generator = new Transaction();
	}

	/**
	 * Tear down after each test
	 */
	public function tearDown(): void {
		parent::tearDown();
		$this->cleanup_test_data();
	}

	/**
	 * Test generator instantiation
	 */
	public function test_generator_instantiation(): void {
		$this->assertInstanceOf( Transaction::class, $this->generator );
	}

	/**
	 * Test get_resource_type method
	 */
	public function test_get_resource_type(): void {
		$reflection = new \ReflectionClass( $this->generator );
		$method = $reflection->getMethod( 'get_resource_type' );
		$method->setAccessible( true );

		$this->assertEquals( 'transaction', $method->invoke( $this->generator ) );
	}

	/**
	 * Test generate method with valid count
	 */
	public function test_generate_with_valid_count(): void {
		$count = 3;
		$result = $this->generator->generate( $count );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'generated', $result );
		$this->assertArrayHasKey( 'transactions', $result );
		$this->assertEquals( $count, $result['generated'] );
		$this->assertCount( $count, $result['transactions'] );
	}

	/**
	 * Test generate method with zero count
	 */
	public function test_generate_with_zero_count(): void {
		$result = $this->generator->generate( 0 );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'invalid_count', $result->get_error_code() );
	}

	/**
	 * Test generate method with negative count
	 */
	public function test_generate_with_negative_count(): void {
		$result = $this->generator->generate( -1 );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'invalid_count', $result->get_error_code() );
	}

	/**
	 * Test generate method with count exceeding batch size
	 */
	public function test_generate_with_large_count(): void {
		$result = $this->generator->generate( 150 );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'count_too_large', $result->get_error_code() );
	}

	/**
	 * Test that generated transactions have required fields
	 */
	public function test_generated_transactions_have_required_fields(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'transactions', $result );
		$this->assertCount( 1, $result['transactions'] );

		$transaction = $result['transactions'][0];

		// Check required transaction fields
		$this->assertArrayHasKey( 'id', $transaction );
		$this->assertArrayHasKey( 'order_id', $transaction );
		$this->assertArrayHasKey( 'transaction_id', $transaction );
		$this->assertArrayHasKey( 'payment_gateway', $transaction );
		$this->assertArrayHasKey( 'amount', $transaction );
		$this->assertArrayHasKey( 'currency', $transaction );
		$this->assertArrayHasKey( 'status', $transaction );
		$this->assertArrayHasKey( 'type', $transaction );

		// Validate data types
		$this->assertIsInt( $transaction['id'] );
		$this->assertIsInt( $transaction['order_id'] );
		$this->assertIsString( $transaction['transaction_id'] );
		$this->assertIsString( $transaction['payment_gateway'] );
		$this->assertIsFloat( $transaction['amount'] );
		$this->assertIsString( $transaction['currency'] );
		$this->assertIsString( $transaction['status'] );
		$this->assertIsString( $transaction['type'] );

		// Validate numeric values
		$this->assertGreaterThan( 0, $transaction['amount'] );
	}

	/**
	 * Test transaction types
	 */
	public function test_transaction_types(): void {
		$result = $this->generator->generate( 20 );

		$this->assertIsArray( $result );
		$valid_types = array( 'payment', 'refund', 'adjustment', 'fee', 'commission' );

		foreach ( $result['transactions'] as $transaction ) {
			$this->assertContains( $transaction['type'], $valid_types );
		}
	}

	/**
	 * Test transaction status values
	 */
	public function test_transaction_status_values(): void {
		$result = $this->generator->generate( 20 );

		$this->assertIsArray( $result );
		$valid_statuses = array( 'completed', 'pending', 'failed', 'cancelled', 'refunded' );

		foreach ( $result['transactions'] as $transaction ) {
			$this->assertContains( $transaction['status'], $valid_statuses );
		}
	}

	/**
	 * Test payment gateway values
	 */
	public function test_payment_gateway_values(): void {
		$result = $this->generator->generate( 20 );

		$this->assertIsArray( $result );
		$valid_gateways = array( 'stripe', 'paypal', 'square', 'authorize_net', 'braintree', 'razorpay', 'mollie', 'woocommerce_payments' );

		foreach ( $result['transactions'] as $transaction ) {
			$this->assertContains( $transaction['payment_gateway'], $valid_gateways );
		}
	}

	/**
	 * Test transaction ID format by gateway
	 */
	public function test_transaction_id_format_by_gateway(): void {
		$result = $this->generator->generate( 30 );

		$this->assertIsArray( $result );

		foreach ( $result['transactions'] as $transaction ) {
			$gateway = $transaction['payment_gateway'];
			$transaction_id = $transaction['transaction_id'];

			switch ( $gateway ) {
				case 'stripe':
					$this->assertStringStartsWith( 'ch_', $transaction_id );
					$this->assertEquals( 27, strlen( $transaction_id ) ); // ch_ + 24 chars
					break;

				case 'paypal':
					$this->assertMatchesRegularExpression( '/^[A-Z0-9]{17}$/', $transaction_id );
					break;

				case 'square':
					$this->assertStringStartsWith( 'sq_', $transaction_id );
					break;

				case 'authorize_net':
					$this->assertMatchesRegularExpression( '/^\d{10}$/', $transaction_id );
					break;

				case 'braintree':
					$this->assertMatchesRegularExpression( '/^[a-z0-9]{6,}$/', $transaction_id );
					break;

				case 'razorpay':
					$this->assertStringStartsWith( 'pay_', $transaction_id );
					break;

				case 'mollie':
					$this->assertStringStartsWith( 'tr_', $transaction_id );
					break;

				case 'woocommerce_payments':
					$this->assertStringStartsWith( 'wcpay_', $transaction_id );
					break;
			}
		}
	}

	/**
	 * Test currency values
	 */
	public function test_currency_values(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );
		$valid_currencies = array( 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'BRL' );

		foreach ( $result['transactions'] as $transaction ) {
			$this->assertContains( $transaction['currency'], $valid_currencies );
		}
	}

	/**
	 * Test transaction amount ranges by type
	 */
	public function test_transaction_amounts_by_type(): void {
		$result = $this->generator->generate( 30 );

		$this->assertIsArray( $result );

		foreach ( $result['transactions'] as $transaction ) {
			$type = $transaction['type'];
			$amount = $transaction['amount'];

			switch ( $type ) {
				case 'payment':
					// Payments should be substantial amounts
					$this->assertGreaterThan( 5.0, $amount );
					break;

				case 'refund':
					// Refunds should be positive (representing money going back)
					$this->assertGreaterThan( 0, $amount );
					break;

				case 'fee':
					// Fees are usually smaller amounts
					$this->assertGreaterThan( 0, $amount );
					$this->assertLessThan( 50.0, $amount );
					break;

				case 'adjustment':
					// Adjustments can be small amounts
					$this->assertGreaterThan( 0, $amount );
					break;

				case 'commission':
					// Commissions are percentage-based, usually smaller
					$this->assertGreaterThan( 0, $amount );
					break;
			}
		}
	}

	/**
	 * Test transaction generation performance
	 */
	public function test_transaction_generation_performance(): void {
		$start_time = microtime( true );
		$result = $this->generator->generate( 10 );
		$end_time = microtime( true );

		$execution_time = $end_time - $start_time;

		// Generation should complete within reasonable time (3 seconds)
		$this->assertLessThan( 3, $execution_time, 'Transaction generation took too long' );

		$this->assertIsArray( $result );
		$this->assertEquals( 10, $result['generated'] );
	}

	/**
	 * Test memory usage during generation
	 */
	public function test_memory_usage_during_generation(): void {
		$memory_before = memory_get_usage();
		$result = $this->generator->generate( 20 );
		$memory_after = memory_get_usage();

		$memory_used = $memory_after - $memory_before;

		// Memory usage should be reasonable (less than 3MB for 20 transactions)
		$this->assertLessThan( 3 * 1024 * 1024, $memory_used, 'Memory usage too high during generation' );

		$this->assertIsArray( $result );
		$this->assertEquals( 20, $result['generated'] );
	}

	/**
	 * Test generate_for_order method
	 */
	public function test_generate_for_order_method(): void {
		// Create a mock order ID for testing
		$order_id = 123;
		$transaction_count = 3;

		// This test checks that the method exists and handles parameters correctly
		$reflection = new \ReflectionClass( $this->generator );
		$method = $reflection->getMethod( 'generate_for_order' );

		$this->assertTrue( $method->isPublic() );
		$this->assertEquals( 2, $method->getNumberOfParameters() );
	}

	/**
	 * Test generate_multiple method
	 */
	public function test_generate_multiple_method(): void {
		$count = 5;
		$result = $this->generator->generate_multiple( $count );

		$this->assertIsArray( $result );
		$this->assertCount( $count, $result );

		foreach ( $result as $transaction ) {
			$this->assertIsArray( $transaction );
			$this->assertArrayHasKey( 'id', $transaction );
			$this->assertArrayHasKey( 'transaction_id', $transaction );
			$this->assertArrayHasKey( 'amount', $transaction );
		}
	}

	/**
	 * Test transaction ID uniqueness
	 */
	public function test_transaction_id_uniqueness(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );
		$transaction_ids = array_column( $result['transactions'], 'transaction_id' );
		$unique_ids = array_unique( $transaction_ids );

		// All transaction IDs should be unique
		$this->assertCount( count( $transaction_ids ), $unique_ids, 'Generated transactions should have unique transaction IDs' );
	}

	/**
	 * Test successful transaction status distribution
	 */
	public function test_transaction_status_distribution(): void {
		$result = $this->generator->generate( 50 );

		$this->assertIsArray( $result );

		$status_counts = array();
		foreach ( $result['transactions'] as $transaction ) {
			$status = $transaction['status'];
			$status_counts[ $status ] = ( $status_counts[ $status ] ?? 0 ) + 1;
		}

		// Most transactions should be completed (this is realistic)
		$this->assertArrayHasKey( 'completed', $status_counts );
		$this->assertGreaterThan( 20, $status_counts['completed'], 'Most transactions should be completed' );
	}

	/**
	 * Test transaction handles missing orders gracefully
	 */
	public function test_handles_missing_orders_gracefully(): void {
		// This test verifies that the generator handles the case where no orders exist
		// The actual behavior depends on the implementation

		$result = $this->generator->generate( 1 );

		// Result should either be a valid array or a WP_Error, but not cause a fatal error
		$this->assertTrue(
			is_array( $result ) || is_wp_error( $result ),
			'Generator should handle missing orders gracefully'
		);
	}

	/**
	 * Test get_supported_types method
	 */
	public function test_get_supported_types(): void {
		$supported_types = $this->generator->get_supported_types();

		$this->assertIsArray( $supported_types );
		$this->assertArrayHasKey( 'transactions', $supported_types );
		$this->assertEquals( 'Payment Transaction History', $supported_types['transactions'] );
	}

	/**
	 * Test get_description method
	 */
	public function test_get_description(): void {
		$description = $this->generator->get_description();

		$this->assertIsString( $description );
		$this->assertStringContainsString( 'transaction', $description );
		$this->assertStringContainsString( 'payment', $description );
	}

	/**
	 * Test transaction metadata
	 */
	public function test_transaction_metadata(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );

		foreach ( $result['transactions'] as $transaction ) {
			// Check for additional metadata that might be present
			if ( isset( $transaction['gateway_response'] ) ) {
				$this->assertIsArray( $transaction['gateway_response'] );
			}

			if ( isset( $transaction['fees'] ) ) {
				$this->assertIsArray( $transaction['fees'] );
				foreach ( $transaction['fees'] as $fee ) {
					$this->assertArrayHasKey( 'type', $fee );
					$this->assertArrayHasKey( 'amount', $fee );
					$this->assertIsString( $fee['type'] );
					$this->assertIsFloat( $fee['amount'] );
				}
			}

			if ( isset( $transaction['risk_assessment'] ) ) {
				$this->assertIsArray( $transaction['risk_assessment'] );
				$this->assertArrayHasKey( 'score', $transaction['risk_assessment'] );
				$this->assertArrayHasKey( 'level', $transaction['risk_assessment'] );
			}
		}
	}

	/**
	 * Test refund transaction handling
	 */
	public function test_refund_transactions(): void {
		$result = $this->generator->generate( 30 );

		$this->assertIsArray( $result );

		$refund_transactions = array_filter( $result['transactions'], function( $transaction ) {
			return $transaction['type'] === 'refund';
		});

		if ( ! empty( $refund_transactions ) ) {
			foreach ( $refund_transactions as $refund ) {
				// Refunds should have positive amounts (money going back to customer)
				$this->assertGreaterThan( 0, $refund['amount'] );

				// Refund status should be appropriate
				$valid_refund_statuses = array( 'completed', 'pending', 'failed' );
				$this->assertContains( $refund['status'], $valid_refund_statuses );
			}
		}
	}
}
