<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Generators\Log as LogGenerator;
use ReflectionClass;

/**
 * Test class for Log Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Log
 */
class LogGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var LogGenerator
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! class_exists( 'EasyCommerce\\Models\\Log' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->generator = new LogGenerator();
	}

	/**
	 * Test generator instantiation
	 */
	public function test_generator_instantiation(): void {
		$this->assertInstanceOf( LogGenerator::class, $this->generator );
	}

	/**
	 * Test get_resource_type method
	 */
	public function test_get_resource_type(): void {
		$r = new ReflectionClass( $this->generator );
		$m = $r->getMethod( 'get_resource_type' );
		$m->setAccessible( true );
		$this->assertEquals( 'log', $m->invoke( $this->generator ) );
	}

	/**
	 * Test get_supported_types returns expected log types
	 */
	public function test_get_supported_types(): void {
		$types = $this->generator->get_supported_types();
		$this->assertIsArray( $types );
		$this->assertArrayHasKey( 'info', $types );
		$this->assertArrayHasKey( 'error', $types );
	}
}
