<?php

require(__DIR__ . '/../vendor/autoload.php');

$data = array(
	'Title' => array(
		'str' => 'awesome',
		'int' => 7,
		'flt' => 10.2,
	),
	'Title 2' => array(
		'bool' => true,
		'arr' => array(
			'a', 'b', 'c', 6 => 'd', 'e', 'key' => 'f'
		)
	)
);

$x = new \donatj\Ini\Builder();
echo $x->generate($data);