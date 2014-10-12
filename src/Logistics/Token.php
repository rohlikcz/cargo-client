<?php

namespace Logistics;

use Kdyby\Curl\Request;
use Nette;
use Nette\Object;
use Nette\Utils\DateTime;



if (!class_exists('Nette\Utils\DateTime')) {
	class_alias('Nette\DateTime', 'Nette\Utils\DateTime');
}

class Token extends Object
{

	/**
	 * @var string
	 */
	protected $token;

	/**
	 * @var string
	 */
	protected $appId;

	/**
	 * @var DateTime
	 */
	protected $expiresAt;

	/**
	 * @var DateTime
	 */
	protected $issuedAt;



	/**
	 * @param string $token
	 * @param string $appId
	 * @param \DateTime $expiresAt
	 * @param \DateTime $issuedAt
	 */
	public function __construct($token, $appId, \DateTime $expiresAt, \DateTime $issuedAt = NULL)
	{
		$this->token = $token;
		$this->appId = $appId;
		$this->expiresAt = DateTime::from($expiresAt);
		$this->issuedAt = DateTime::from($issuedAt);
	}



	/**
	 * @return string
	 */
	public function getAppId()
	{
		return $this->appId;
	}



	/**
	 * @return \DateTime
	 */
	public function getExpiresAt()
	{
		return clone $this->expiresAt;
	}



	/**
	 * @param \DateTime $now
	 * @return bool
	 */
	public function isExpired(\DateTime $now = NULL)
	{
		return $this->expiresAt <= DateTime::from($now);
	}



	/**
	 * @return \DateTime
	 */
	public function getIssuedAt()
	{
		return clone $this->issuedAt;
	}



	/**
	 * @return string
	 */
	public function getToken()
	{
		return $this->token;
	}



	/**
	 * @param Request $request
	 */
	public function signRequest(Request $request)
	{
		$request->headers['X-Authentication-Simple'] = base64_encode($this->getToken());
	}

}
