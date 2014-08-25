<?php

namespace Logistics;

use Kdyby\Curl\Request;
use Nette\Object;
use Nette\Utils\Json;



class LogisticsClient extends Object
{

	const JSON_CONTENT_TYPE = 'application/json; charset=UTF-8';

	/**
	 * @var Connector
	 */
	private $connector;

	/**
	 * @var IRequestFactory
	 */
	private $requestFactory;



	/**
	 * @param Connector $connector
	 * @param IRequestFactory $requestFactory
	 */
	public function __construct(Connector $connector, IRequestFactory $requestFactory)
	{
		$this->connector = $connector;
		$this->requestFactory = $requestFactory;
	}



	/**
	 * @param array $order
	 * @return int
	 */
	public function createOrder(array $order)
	{
		$payload = Json::encode($order);

		$request = $this->requestFactory->createRequest('orders', $payload)->setMethod(Request::POST);
		$request->headers['Content-Type'] = self::JSON_CONTENT_TYPE;

		$response = Json::decode($this->connector->send($request)->getResponse());

		return $response->_id;
	}



	/**
	 * @param int $remoteId
	 * @return array
	 */
	public function readOrder($remoteId)
	{
		$request = $this->requestFactory->createRequest('orders/' . intval($remoteId));
		$response = Json::decode($this->connector->send($request)->getResponse(), TRUE);

		return $response;
	}



	/**
	 * @param int $remoteId
	 * @param array $order
	 * @throws BadResponseException
	 */
	public function patchOrder($remoteId, array $order)
	{
		$payload = Json::encode($order);

		$request = $this->requestFactory->createRequest('orders/' . intval($remoteId), $payload)->setMethod(Request::PATCH);
		$request->headers['Content-Type'] = self::JSON_CONTENT_TYPE;
		$response = $this->connector->send($request);

		if (($responseCode = $response->getCode()) !== Http\Response::S202_ACCEPTED) {
			throw new BadResponseException('Expeced ' . Http\Response::S202_ACCEPTED . ' response code, ' . $responseCode . ' given.');
		}
	}



	/**
	 * @param int $remoteId
	 * @throws BadResponseException
	 */
	public function cancelOrder($remoteId)
	{
		$this->patchOrder($remoteId, [
			'state' => 'canceled',
		]);
	}

}
