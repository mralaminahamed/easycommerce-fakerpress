<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Attribute;
use ReflectionClass;

/**
 * Test class for Attribute REST Controller
 *
 * @covers \EasyCommerceFakerPress\Controllers\Attribute
 */
class AttributeRESTControllerTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Attribute
	 */
	private $controller;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		$this->controller = new Attribute();
	}

	/**
	 * Tear down after each test
	 */
	public function tearDown(): void {
		parent::tearDown();
		$this->cleanup_test_data();
	}

	/**
	 * Test controller instantiation
	 */
	public function test_controller_instantiation(): void {
		$this->assertInstanceOf( Attribute::class, $this->controller );
	}

	/**
	 * Test controller rest base
	 */
	public function test_controller_rest_base(): void {
		$reflection = new ReflectionClass( $this->controller );
		$method     = $reflection->getMethod( 'get_rest_base' );
		$method->setAccessible( true );

		$this->assertEquals( 'attributes', $method->invoke( $this->controller ) );
	}
}
