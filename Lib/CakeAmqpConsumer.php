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
 * @param $queue The name of the queue to consume one
 * @param $config Configuration to use
 * @throws CakeException 
 */
	function __construct($queue, $config = 'default') {
		parent::__construct($config);

		$this->_consumerQueue = $queue;
	}

/**
 * Consumers only need to declare queues to listen on
 *
 * @return void
 */
	public function declareConfig($config = array()) {
		$appConfig = Configure::read('CakeAmqp');
		if (empty($config)) {
			$config = $appConfig;
		}

		if (!isset($config['queues'][$this->_consumerQueue])) {
			throw new CakeException(__d('cake_amqp', 'Missing configurion for queue: %s', $this->_consumerQueue));
		}

		$this->declareQueue($this->_consumerQueue, $config['queues'][$this->_consumerQueue]);
	}

/**
 * Setup consumer
 *
 * callback function receives the following 3 parameters
 *  - CakeAmqpConsumer $consumer: the instance of the consumer
 *  - AMQPEnvelope $envelope: The message
 *  - array $data: Meta data
 * 
 * @param string $consumerName Name of the consumer
 * @param mixed $callback Callback to receive messages on
 * @param array $options
 */
	public function consume($consumerName, $callback, $options = array()) {
		$this->_callback = $callback;

		$this->connection();

		if (!$this->connected()) {
			throw new CakeException(__d('cake_amqp', 'Not connected to broker'));
		}

		$this->_queues[$this->_consumerQueue]->consume(array($this, 'processMessage'));
	}

/**
 * Callback to process messages received from the queue
 *
 * @param AMQPEnvelope $envelope
 * @param AMQPQueue $queue
 *
 * @return void
 */
	public function processMessage(AMQPEnvelope $envelope, AMQPQueue $queue) {
		$data = json_decode($envelope->getBody(), true);

		call_user_func($this->_callback, $this, $envelope, $data);
	}

/**
 * Acks the message
 *
 * @param AMQPEnvelope $envelope
 *
 * @return void
 */
	public function ack(AMQPEnvelope $envelope) {
		$this->_queues[$this->_consumerQueue]->ack($envelope->getDeliveryTag());
	}

/**
 * Nacks the message
 *
 * @param AMQPEnvelope $envelope
 *
 * @return void
 */
	public function nack(AMQPEnvelope $envelope) {
		$this->_queues[$this->_consumerQueue]->nack($envelope->getDeliveryTag());
	}
}
