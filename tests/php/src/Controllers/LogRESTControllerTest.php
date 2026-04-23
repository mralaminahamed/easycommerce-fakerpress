<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Log;
use ReflectionClass;

/**
 * Test class for Log REST Controller
 *
 * @covers \EasyCommerceFakerPress\Controllers\Log
 */
class LogRESTControllerTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Log
	 */
	private $controller;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		$this->controller = new Log();
	}

	/**
	 * Test controller instantiation
	 */
	public function test_controller_instantiation(): void {
		$this->assertInstanceOf( Log::class, $this->controller );
	}

	/**
	 * Test controller rest base
	 */
	public function test_controller_rest_base(): void {
		$r = new ReflectionClass( $this->controller );
		$m = $r->getMethod( 'get_rest_base' );
		$m->setAccessible( true );
		$this->assertEquals( 'logs', $m->invoke( $this->controller ) );
	}
}
