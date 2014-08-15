<?php

namespace Logistics;

use Kdyby\Curl\Request;



interface IRequestFactory
{

	/**
	 * @param string $resourcePath
	 * @param array|string $post
	 * @return Request
	 */
	function createRequest($resourcePath, $post = []);

} 
