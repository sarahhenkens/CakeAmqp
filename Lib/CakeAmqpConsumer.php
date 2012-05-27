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
 * Callback to receive messages on
 *
 * @var mixed 
 */
	protected $_callback = null;

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
		$this->_callback = $callback;

		$this->connection();

		if (!$this->connected()) {
			throw new CakeException(__d('cake_amqp', 'Not connected to broker'));
		}

		$this->_queues[$this->_consumerQueue]->consume(array($this, 'processMessage'));

		//$this->_channel->basic_consume($this->_consumerQueue, $consumerName, false, false, false, false, $callback);
	}

/**
 * Callback to process messages from the queue
 *
 * @param type $envelope 
 */
	public function processMessage($envelope, $queue) {
		$queue->ack($envelope->getDeliveryTag());

		$data = array(
			'object' => $envelope,
			'body' => $envelope->getBody()
		);

		call_user_func($this->_callback, $data);
	}
}
