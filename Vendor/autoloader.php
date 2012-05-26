<?php

require __DIR__ . DS . 'Zend' . DS . 'Loader' . DS . 'StandardAutoloader.php';
$autoLoader = new Zend\Loader\StandardAutoloader(array(
	'namespaces' => array(
		'PhpAmqpLib' => __DIR__ . DS . 'php-amqplib' . DS . 'PhpAmqpLib',
	),
	'fallback_autoloader' => true,
));
$autoLoader->register();