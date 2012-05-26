<?php

require_once __DIR__ . DS . '..' . DS . 'Vendor' . DS . 'autoloader.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class CakeAmqp extends Object {

/**
 * Holds the singleton instance
 *
 * @var CakeAmqp 
 */
	static protected $_instance = null;

/**
 * Holds the flag if a connection has been made to the AMQP broker
 *
 * @var boolean 
 */
	protected $_connected = false;

/**
 * Holds the instance of the AMQPConnection object
 *
 * @var AMQPConnection 
 */
	protected $_connection = null;

/**
 * Holds the instance of the AMQPChannel object
 *
 * @var AMQPChannel 
 */
	protected $_channel = null;

/**
 * List of valid exchange types
 *
 * @var array 
 */
	protected $_exchangeTypes = array('topic', 'direct', 'fanout', 'headers');

/**
 * Holds the configured exchanges
 *
 * @var array 
 */
	protected $_exchanges = array();

/**
 * Holds the configured queues
 *
 * @var array 
 */
	protected $_queues = array();

/**
 * Return a singleton instance of CakeAmqp.
 *
 * @return CakeAmqp instance
 */
	static function getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new CakeAmqp();
		}

		return self::$_instance;
	}

/**
 * CakeAmqp constructor which checks the configuration
 *
 * @throws CakeException 
 */
	function __construct() {
		$path = APP . 'Config' . DS . 'amqp.php';

		if (!file_exists($path)) {
			throw new CakeException(__d('cake_amqp', 'Configuration file not found: %s', $path));
		}

		require_once($path);

		if (!class_exists('AMQP_CONFIG')) {
			throw new CakeException(__d('cake_amqp', 'Configure file is not valid: missing AMQP_CONFIG'));
		}

		if (!Configure::read('CakeAmqp')) {
			throw new CakeException(__d('cake_amqp', 'No bindings configuration found'));
		}
	}

/**
 * Cleanly close the connection
 * 
 */
	function __destruct() {
		if ($this->_connected) {
			$this->_connection->close();
		}
	}

/**
 * Returns the current AMQPConnection object
 *
 * Will try to connect to the broken if no connection was made
 *
 * @return AMQPConnection 
 */
	public function connection() {
		if ($this->_connected) {
			return $this->_connection;
		}

		try {
			$this->_connection = new AMQPConnection(
				AMQP_CONFIG::$default['host'],
				AMQP_CONFIG::$default['port'],
				AMQP_CONFIG::$default['user'],
				AMQP_CONFIG::$default['pass'],
				AMQP_CONFIG::$default['vhost']
			);
			$this->_channel = $this->_connection->channel();

			$this->_connected = true;

			$this->_configure();

			return $this->_connection;
		} catch (Exception $ex) {
			$this->_connected = false;
			throw $ex;
		}
	}

/**
 * Declares configured exchanges, queues and bindings 
 *
 * @return void
 */
	protected function _configure() {
		foreach (Configure::read('CakeAmqp.exchanges') as $name => $options) {
			if (is_string($options)) {
				$name = $options;
				$options = array();
			}
			$this->declareExchange($name, $options);
		}

		foreach (Configure::read('CakeAmqp.queues') as $name => $options) {
			if (is_string($options)) {
				$name = $options;
				$options = array();
			}
			$this->declareQueue($name, $options);
		}

		foreach (Configure::read('CakeAmqp.bindings') as $routingKey => $options) {
			$this->bind($routingKey, $options['exchange'], $options['queue'], $options);
		}
	}

/**
 * Declares an exchange on broken
 *
 * available options:
 *
 * - type: string (see $_exchangeTypes for valid types)
 * - passive: boolean
 * - durable: boolean
 * - auto_delete: boolean
 * - internal: boolean
 * - nowait: boolean
 *
 * @param string $name
 * @param array $options 
 */
	protected function declareExchange($name, $options = array()) {
		if ($this->_connected === false) {
			throw new CakeException(__d('cake_amqp', 'Not connected to broker'));
		}

		if (isset($this->_exchanges[$name])) {
			throw new CakeException(__d('cake_amqp', 'Exchange already exists: %s', $name));
		}

		$defaults = array(
			'type' => 'direct',
			'passive' => false,
			'durable' => false,
			'auto_delete' => true,
			'internal' => false,
			'nowait' => false
		);

		$options = array_merge($defaults, $options);

		if (!in_array($options['type'], $this->_exchangeTypes)) {
			throw new CakeException('cake_amqp', 'Exchange type not supported: %s', $options['type']);
		}

		$this->_channel->exchange_declare($name,
			$options['type'],
			$options['passive'],
			$options['durable'],
			$options['auto_delete'],
			$options['internal'],
			$options['nowait']
		);

		$this->_exchanges[$name] = $options;
	}

/**
 * Declares a queue on broken
 *
 * available options:
 *
 * - passive: boolean
 * - durable: boolean
 * - exclusive: boolean
 * - auto_delete: boolean
 * - nowait: boolean
 *
 * @param string $name
 * @param array $options 
 */
	protected function declareQueue($name, $options = array()) {
		if ($this->_connected === false) {
			throw new CakeException(__d('cake_amqp', 'Not connected to broker'));
		}

		if (isset($this->_queues[$name])) {
			throw new CakeException(__d('cake_amqp', 'Queue already exists: %s', $name));
		}

		$defaults = array(
			'passive' => false,
			'durable' => false,
			'exclusive' => false,
			'auto_delete' => true,
			'nowait' => false
		);

		$options = array_merge($defaults, $options);

		$this->_channel->queue_declare($name,
			$options['passive'],
			$options['durable'],
			$options['exclusive'],
			$options['auto_delete'],
			$options['nowait']
		);

		$this->_queues[$name] = $options;
	}

/**
 * Binds a queue to an exchange with a routing key
 *
 * available options:
 *
 * - nowait: boolean
 *
 * @param string $routingKey
 * @param string $exchange
 * @param string|array $queues
 * @param array $options
 * @throws CakeException 
 */
	protected function bind($routingKey, $exchange, $queues, $options) {
		if ($this->_connected === false) {
			throw new CakeException(__d('cake_amqp', 'Not connected to broker'));
		}

		$defaults = array('nowait' => false);
		$options = array_merge($defaults, $options);

		if (is_string($queues)) {
			$queues = array($queues);
		}

		if (!isset($this->_exchanges[$exchange])) {
			throw new CakeException(__d('cake_amqp', 'Exchange does not exist: %s', $exchange));
		}

		foreach ($queues as $queue) {
			if (!isset($this->_queues[$queue])) {
				throw new CakeException(__d('cake_amqp', 'Queue does not exist: %s', $queue));
			}

			$this->_channel->queue_bind($queue, $exchange, $routingKey, $options['nowait']);
		}
	}

/**
 * Sends a message to the broker
 *
 * @param string $exchange
 * @param string $routingKey
 * @param mixed $data
 * @param array $options 
 */
	public function publish($exchange, $routingKey, $data, $options = array()) {
		$this->connection();

		if (!$this->_connected) {
			throw new CakeException(__d('cake_amqp', 'Not connected to broker'));
		}

		if (!isset($this->_exchanges[$exchange])) {
			throw new CakeException(__d('cake_amqp', 'Exchange does not exist: %s', $exchange));
		}

		$message = new AMQPMessage($data, array('delivery-mode' => 2));
		$this->_channel->basic_publish($message, $exchange, $routingKey, true);
	}
}