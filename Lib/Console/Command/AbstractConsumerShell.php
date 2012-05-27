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
	}

	protected function startListening() {
		$this->_amqp->consume($this->consumerName, array($this, 'processMessage'));
	}

	public function processMessage($consumer, $envelope, $data) {
		if ($this->autoAck === true) {
			$consumer->ack($envelope);
		}

		$this->onMessage($consumer, $envelope, $data);
	}

	abstract public function onMessage($consumer, $envelope, $data);
}