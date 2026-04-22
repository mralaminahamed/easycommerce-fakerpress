<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerce\Models\Attribute;
use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Generators\Attribute as AttributeGenerator;
use ReflectionClass;

/**
 * Test class for Attribute Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Attribute
 */
class AttributeGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var AttributeGenerator
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active.
		if ( ! class_exists( Attribute::class ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->generator = new AttributeGenerator();
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
		$this->assertInstanceOf( AttributeGenerator::class, $this->generator );
	}

	/**
	 * Test get_resource_type method
	 */
	public function test_get_resource_type(): void {
		$reflection = new ReflectionClass( $this->generator );
		$method     = $reflection->getMethod( 'get_resource_type' );
		$method->setAccessible( true );

		$this->assertEquals( 'attribute', $method->invoke( $this->generator ) );
	}

	/**
	 * Test controller instantiation
	 */
	public function test_controller_instantiation(): void {
		$controller = new \EasyCommerceFakerPress\Controllers\Attribute();
		$this->assertInstanceOf( \EasyCommerceFakerPress\Controllers\Attribute::class, $controller );
	}

	/**
	 * Test controller rest base
	 */
	public function test_controller_rest_base(): void {
		$controller = new \EasyCommerceFakerPress\Controllers\Attribute();
		$reflection = new ReflectionClass( $controller );
		$method     = $reflection->getMethod( 'get_rest_base' );
		$method->setAccessible( true );

		$this->assertEquals( 'attributes', $method->invoke( $controller ) );
	}
}
