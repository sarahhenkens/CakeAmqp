<?php

App::uses('CakeAmqpBase', 'CakeAmqp.Lib');

class CakeAmqpProducer extends CakeAmqpBase {

/**
 * Sends a message to the broker
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

		$this->_exchanges[$exchange]->publish($data, $routingKey);
	}
}
