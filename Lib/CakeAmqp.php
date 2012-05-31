<?php

App::uses('CakeAmqpProducer', 'CakeAmqp.Lib');

class CakeAmqp extends Object {

/**
 * Holds the producer instance
 *
 * @var CakeAmqpProducer 
 */
	protected static $_instance = null;

/**
 * Singleton factory method to get the instance of a producer
 *
 * @return CakeAmqpProducer 
 */
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new CakeAmqpProducer();
		}

		return self::$_instance;
	}

/**
 * Static method for sending messages
 *
 * @see CakeAmqpProducer::send()
 *
 * @param type $exchange
 * @param type $routingKey
 * @param type $data
 * @param type $options
 *
 * @return type 
 */
	public static function send($exchange, $routingKey, $data, $options = array()) {
		return self::getInstance()->send($exchange, $routingKey, $data, $options);
	}
}