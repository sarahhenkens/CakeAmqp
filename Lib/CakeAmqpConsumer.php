<?php

App::uses('CakeAmqpBase', 'CakeAmqp.Lib');

class CakeAmqpConsumer extends CakeAmqpBase {

/**
 * Queue for the consumer to listen on
 *
 * @var string 
 */
	protected $_consumerQueue = '';

/**
 * CakeAmqp constructor which checks the configuration
 *
 * @throws CakeException 
 */
	function __construct($queue, $datasource = 'default') {
		parent::__construct($datasource);

		$this->_consumerQueue = $queue;
	}

/**
 * Declares configured exchanges, queues and bindings 
 *
 * @return void
 */
	protected function _configure() {
		if (!Configure::read('CakeAmqp.queues.' . $this->_consumerQueue)) {
			throw new CakeException(__d('cake_amqp', 'Missing configurion for queue: %s', $this->_consumerQueue));
		}

		$options = Configure::read('CakeAmqp.queues.' . $this->_consumerQueue);
		$this->declareQueue($this->_consumerQueue, $options);
	}

/**
 * Setup consumer
 *
 * @param type $callback
 * @param type $options 
 */
	public function consume($consumerName, $callback, $options = array()) {
		$this->connection();

		if (!$this->connected()) {
			throw new CakeException(__d('cake_amqp', 'Not connected to broker'));
		}

		$this->_channel->basic_consume($this->_consumerQueue, $consumerName, false, false, false, false, $callback);
	}

/**
 * Start listening
 *
 * @throws CakeException 
 */
	public function listen() {
		if (!$this->connected()) {
			throw new CakeException(__d('cake_amqp', 'Not connected to broker'));
		}

		while (count($this->_channel->callbacks)) {
			$this->_channel->wait();
		}
	}
}
