<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerce\Models\Refund;
use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Generators\Refund as RefundGenerator;
use ReflectionClass;

/**
 * Test class for Refund Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Refund
 */
class RefundGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var RefundGenerator
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active.
		if ( ! class_exists( Refund::class ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->generator = new RefundGenerator();
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
		$this->assertInstanceOf( RefundGenerator::class, $this->generator );
	}

	/**
	 * Test get_resource_type method
	 */
	public function test_get_resource_type(): void {
		$reflection = new ReflectionClass( $this->generator );
		$method     = $reflection->getMethod( 'get_resource_type' );
		$method->setAccessible( true );

		$this->assertEquals( 'refund', $method->invoke( $this->generator ) );
	}

	/**
	 * Test get_supported_types returns expected refund types
	 */
	public function test_get_supported_types(): void {
		$types = $this->generator->get_supported_types();

		$this->assertIsArray( $types );
		$this->assertContains( 'full', $types );
		$this->assertContains( 'partial', $types );
	}
}
