<?php

/**
 * @testCase LogisticsTests\RequestFactoryTest
 */

namespace LogisticsTests;

require_once __DIR__ . '/../bootstrap.php';

use Kdyby\Curl\Request;
use Logistics\RequestFactory;
use Tester\Assert;
use Tester\TestCase;



class RequestFactoryTest extends TestCase
{

	public function testCreateRequest()
	{
		$factory = new RequestFactory('https://localhost');

		$request = $factory->createRequest('');
		Assert::type(Request::class, $request);
		Assert::notSame($request, $factory->createRequest(''));
	}

}



(new RequestFactoryTest())->run();
