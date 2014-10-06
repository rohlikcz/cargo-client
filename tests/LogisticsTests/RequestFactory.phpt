<?php

/**
 * Test: Logistics\RequestFactory
 *
 * @testCase LogisticsTests\RequestFactoryTest
 */

namespace LogisticsTests;

use Kdyby\Curl\Request;
use Logistics\RequestFactory;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';



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



\run(new RequestFactoryTest());
