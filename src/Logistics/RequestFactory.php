<?php

namespace Logistics;

use Kdyby\Curl\Request;
use Nette\Object;



class RequestFactory extends Object implements IRequestFactory
{

	/**
	 * @var string
	 */
	private $apiBaseUrl;



	/**
	 * @param string $apiBaseUrl
	 */
	public function __construct($apiBaseUrl)
	{
		$this->apiBaseUrl = $apiBaseUrl;
	}



	/**
	 * @param string $resourcePath
	 * @param array|string $post
	 * @return Request
	 */
	public function createRequest($resourcePath, $post = [])
	{
		return new Request($this->apiBaseUrl . '/' . $resourcePath, $post);
	}

}
