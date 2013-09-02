<?php

App::uses('CakeAmqpBase', 'CakeAmqp.Lib');

Configure::write('CakeAmqp.config_path', __DIR__ . DS . '..' . DS . '..' . DS . 'test_app' . DS . 'Config' . DS);

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

		$result = $this->cakeAmqpBase->config('one');
		$this->assertTrue($result instanceof CakeAmqpBase);
		$result = $this->cakeAmqpBase->config();
		$this->assertEquals('username-one', $result['user']);

		$result = $this->cakeAmqpBase->config('two');
		$this->assertTrue($result instanceof CakeAmqpBase);
		$result = $this->cakeAmqpBase->config();
		$this->assertEquals('username-two', $result['user']);
	}

/**
 * testConfigInvalidKey method
 *
 * @expectedException CakeException
 * @expectedExceptionMessage Connection not present in configuration file
 *
 * @return void
 */
	public function testConfigInvalidKey() {
		$this->cakeAmqpBase->config('foobar');
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


