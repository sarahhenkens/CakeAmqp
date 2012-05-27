<?php

App::uses('CakeAmqp', 'CakeAmqp.Lib');

/**
 * CacheTest class
 *
 * @package       Cake.Test.Case.Cache
 */
class CakeAmqpTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		CakeAmqp::reset();
	}

/**
 * Tests the static getInstance method
 *
 * @return void
 */
	public function testGetInstance() {
		$instance = CakeAmqp::getInstance();
		$this->assertTrue($instance instanceof CakeAmqp);
		$this->assertFalse($instance->consumerMode());
	}

/**
 * Tests the static consumer method
 *
 * @return void
 */
	public function testConsumer() {
		$instance = CakeAmqp::consumer('test');
		$this->assertTrue($instance instanceof CakeAmqp);
		$this->assertTrue($instance->consumerMode());
	}

/**
 * Tests if the consumer method properly triggers an exception
 *
 * @expectedException CakeException
 * @return void
 */
	public function testConsumerException() {
		CakeAmqp::getInstance();
		CakeAmqp::consumer('test');
	}
}
