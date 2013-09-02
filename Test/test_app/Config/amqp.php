<?php
/**
 * Amqp configuration class.
 *
 * host =>
 * the host of your RabbitMQ server
 *
 * port =>
 * The port of your RabbitMQ server, default: 5672
 *
 * user =>
 * The username
 *
 * pass =>
 * The password
 *
 * vhost =>
 * The RabbitMQ vhost to use, default: /
 */
class AMQP_CONFIG {

	public $default = array(
		'host' => 'localhost',
		'port' => '5672',
		'user' => 'username',
		'pass' => 'password',
		'vhost' => '/',
	);

	public $test = array(
		'host' => 'localhost',
		'port' => '5672',
		'user' => 'user',
		'pass' => 'password',
		'vhost' => 'test-vhost',
	);

	public $one = array(
		'host' => 'localhost',
		'port' => '5672',
		'user' => 'username-one',
		'pass' => 'password',
		'vhost' => 'vhost-one',
	);

	public $two = array(
		'host' => 'localhost',
		'port' => '5672',
		'user' => 'username-two',
		'pass' => 'password',
		'vhost' => 'vhost-two',
	);
}
