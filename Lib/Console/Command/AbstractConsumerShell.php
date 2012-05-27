<?php
App::uses('CakeAmqpConsumer', 'CakeAmqp.Lib');

abstract class AbstractConsumerShell extends AppShell {

/**
 * The name of the consumer
 *
 * @var string 
 */
	public $consumerName = 'Abstract Consumer';

/**
 * The queue name that the consumer wants to consume
 *
 * @var string 
 */
	public $queue = null;

/**
 * Set to true to auto ack messages
 *
 * @var boolean 
 */
	public $autoAck = false;

/**
 * Holds the CakeAmqp instance running in consumer mode
 *
 * @var CakeAmqp 
 */
	protected $_amqp = null;

	public function startup() {
		if ($this->queue === null) {
			throw new CakeException(__d('cake_amqp', '$queue parameter is not configured'));
		}

		$this->_amqp = new CakeAmqpConsumer($this->queue);

		$this->_amqp->consume($this->consumerName, array($this, 'processMessage'));
	}

	protected function startListening() {
		$this->_amqp->listen();
	}

	public function processMessage($data) {
		if ($this->autoAck === true) {
			$this->ack($message);
		}

		$this->onMessage($data['body'], $data);
	}

	abstract public function onMessage($payload, $data);
}