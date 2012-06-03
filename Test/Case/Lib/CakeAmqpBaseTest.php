<?php

App::uses('CakeAmqpBase', 'CakeAmqp.Lib');

class MockAMQPConnection {
	public function isConnected() {}
	public function connect() {}
	public function pconnect() {}
	public function disconnect() {}
	public function reconnect() {}
}

class TestCakeAmqpBase extends CakeAmqpBase {
	public function __construct() {

	}

	public function setProtectedProperty($name, $value) {
		$this->{$name} = $value;
	}
}

/**
 * CakeAmqpTest class
 *
 * @package       CakeAmqp.Test.Case
 */
class CakeAmqpBaseTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		$this->cakeAmqpBase = new TestCakeAmqpBase();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->cakeAmqpBase);
	}

/**
 * Tests the config method
 *
 * @return void 
 */
	public function testConfig() {
		$config = array('foo' => 'bar');
		$result = $this->cakeAmqpBase->config($config);
		$this->assertTrue($result instanceof CakeAmqpBase);
		$this->assertEquals($config, $this->cakeAmqpBase->config());
	}

/**
 * Tests the connected() method
 *
 * @return void 
 */
	public function testConnected() {
		$mockConnection = $this->getMock('MockAMQPConnection');
		$mockConnection->expects($this->once())
			->method('isConnected')
			->will($this->returnValue(true));

		$this->cakeAmqpBase->setProtectedProperty('_connection', $mockConnection);
		$this->assertTrue($this->cakeAmqpBase->connected());
	}
}


