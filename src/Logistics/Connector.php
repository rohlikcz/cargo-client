<?php

namespace Logistics;

use DateTime;
use Exception;
use Kdyby\Curl\CurlException;
use Kdyby\Curl\CurlSender;
use Kdyby\Curl\Request;
use Kdyby\Curl\Response;
use Nette\Diagnostics\Debugger;
use Nette\Object;
use Nette\Utils\Json;



class Connector extends Object
{

	const MAX_ATTEMPTS = 5;
	const DEFAULT_TIMEOUT = 30;
	const DEFAULT_CONNECTION_TIMEOUT = 15;

	/**
	 * @var Consumer
	 */
	private $consumer;

	/**
	 * @var ITokenStorage
	 */
	private $tokenStorage;

	/**
	 * @var IRequestFactory
	 */
	private $requestFactory;

	/**
	 * @var CurlSender
	 */
	private $curlSender;

	/**
	 * @var Token
	 */
	private $currentToken;



	/**
	 * @param Consumer $consumer
	 * @param ITokenStorage $tokenStorage
	 * @param IRequestFactory $requestFactory
	 * @param CurlSender $curlSender
	 */
	public function __construct(Consumer $consumer, ITokenStorage $tokenStorage, IRequestFactory $requestFactory, CurlSender $curlSender = NULL)
	{
		$this->consumer = $consumer;
		$this->requestFactory = $requestFactory;
		$this->tokenStorage = $tokenStorage;
		$this->curlSender = $curlSender;
	}



	/**
	 * @param Request $request
	 * @return Response
	 * @throws \Exception
	 */
	public function send(Request $request)
	{
		try {
			if (!isset($this->currentToken) || $this->currentToken->isExpired()) {
				$this->currentToken = $this->tokenStorage->findToken($this->consumer);
			}
			$this->currentToken->signRequest($request);

			return $this->getCurlSender()->send($request);
		} catch (Exception $e) {
			if ($e instanceof TokenException || $e instanceof CurlException) {

				if (isset($this->currentToken)) {
					$this->tokenStorage->markTokenAsBroken($this->currentToken);
				}
				$this->currentToken = $this->fetchNewToken();
				$this->tokenStorage->saveToken($this->currentToken);

				$this->currentToken->signRequest($request);

				return $this->getCurlSender()->send($request);
			}
			throw $e;
		}
	}



	/**
	 * @return Token
	 */
	private function fetchNewToken()
	{
		$request = $this->requestFactory->createRequest('login', $this->consumer->exportToArray())
			->setMethod(Request::POST);

		try {
			$response = Json::decode($this->getCurlSender()->send($request)->getResponse());

			return new Token($response->token, $this->consumer->getAppId(), new DateTime($response->expiration));

		} catch (CurlException $e) {
			Debugger::log('Cannot fetch new token: ' . $e->getMessage(), 'logistics');
			throw $e;
		}
	}



	/**
	 * @return CurlSender
	 */
	private function getCurlSender()
	{
		if ($this->curlSender === NULL) {
			$this->curlSender = new CurlSender;
			$this->curlSender->setTimeout(self::DEFAULT_TIMEOUT);
			$this->curlSender->setConnectTimeout(self::DEFAULT_CONNECTION_TIMEOUT);
			$this->curlSender->repeatOnFail = self::MAX_ATTEMPTS;
		}

		return $this->curlSender;
	}

}
