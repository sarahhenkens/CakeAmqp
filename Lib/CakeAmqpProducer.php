<?php

App::uses('CakeAmqpBase', 'CakeAmqp.Lib');

class CakeAmqpProducer extends CakeAmqpBase {

/**
 * Default attributes that are sent to the publish method of the exchange.
 *
 * @var array
 */
	public $defaults = array(
		'content_type' => 'application/json',
		'timeout' => false
	);

/**
 * Sends a message to the broker. $data will be json encoded before it is sent to the broker.
 * $options will be passed to the publish method's attributes.
 *
 * @param string $exchange
 * @param string $routingKey
 * @param mixed $data
 * @param array $options 
 */
	public function send($exchange, $routingKey, $data, $options = array()) {
		$this->connection();

		if (!$this->connected()) {
			throw new CakeException(__d('cake_amqp', 'Not connected to broker'));
		}

		if (!isset($this->_exchanges[$exchange])) {
			throw new CakeException(__d('cake_amqp', 'Exchange does not exist: %s', $exchange));
		}

		$options = $this->defaults + $options;

		if ($options['timeout']) {
			$options['timestamp'] = microtime(true);
			$options['expires'] = microtime(true) + $options['timeout'];
		}

		$this->_exchanges[$exchange]->publish(json_encode($data), $routingKey, AMQP_NOPARAM, $options);
	}
}
