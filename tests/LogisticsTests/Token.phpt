<?php

/**
 * Test: Logistics\Token
 *
 * @testCase LogisticsTests\TokenTest
 */

namespace LogisticsTests;

use Logistics\Token;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';



class TokenTest extends TestCase
{

	public function testCloneExpiresAt()
	{
		$token = new Token('', '', $expiresAt = new \DateTime());

		Assert::notSame($expiresAt, $token->getExpiresAt());
	}



	/**
	 * @dataProvider provideIsExpired
	 */
	public function testIsExpired($expected, $expiresAt, $now)
	{
		$token = new Token('', '', new \DateTime($expiresAt));

		Assert::same($expected, $token->isExpired(new \DateTime($now)));
	}



	public function provideIsExpired()
	{
		return [
			[TRUE, '2014-10-06 09:59:59', '2014-10-06 10:00:00'],
			[TRUE, '2014-10-06 10:00:00', '2014-10-06 10:00:00'],
			[FALSE, '2014-10-06 10:00:01', '2014-10-06 10:00:00'],
		];
	}

}



\run(new TokenTest());
