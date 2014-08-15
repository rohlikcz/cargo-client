<?php

namespace Logistics;

use Nette\Object;



class Consumer extends Object
{

	/**
	 * @var string
	 */
	private $appId;

	/**
	 * @var string
	 */
	private $secret;



	/**
	 * @param string $appId
	 * @param string $secret
	 */
	public function __construct($appId, $secret)
	{
		$this->appId = $appId;
		$this->secret = $secret;
	}



	/**
	 * @return string
	 */
	public function getAppId()
	{
		return $this->appId;
	}



	/**
	 * @return string
	 */
	public function getSecret()
	{
		return $this->secret;
	}



	/**
	 * @return array
	 */
	public function exportToArray()
	{
		return [
			'appId' => $this->appId,
			'secret' => $this->secret,
		];
	}

}
