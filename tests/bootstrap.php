<?php

@set_time_limit(0);

$autoloader = require_once __DIR__ . '/../vendor/autoload.php';
$autoloader->add('LogisticsTests', __DIR__);

function run(Tester\TestCase $testCase)
{
	$testCase->run(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : NULL);
}

/**
 * @param string|\Closure $name
 * @param NULL|\Closure $test
 * @throws InvalidArgumentException
 */
function test($name, $test = NULL)
{
	if ($test instanceof \Closure) {
		$test();

	} elseif ($name instanceof \Closure) {
		$name();

	} else {
		throw new \InvalidArgumentException();
	}
}

return $autoloader;
