<?php

namespace Logistics;

use Nette\Object;



class LogisticsClientsPool extends Object
{

	/**
	 * @var LogisticsClient[]
	 */
	protected $logisticsClients;



	/**
	 * @param LogisticsClient[] $logisticsClients
	 */
	public function __construct(array $logisticsClients)
	{
		foreach ($logisticsClients as $name => $logisticsClient) {
			$this->registerClient($name, $logisticsClient); // for type validation
		}
	}



	/**
	 * @param string $name
	 * @return LogisticsClient
	 * @throws ClientNotFoundException
	 */
	public function getClient($name)
	{
		if (!array_key_exists($name, $this->logisticsClients)) {
			throw new ClientNotFoundException("Client $name was not found in available clients pool.");
		}

		return $this->logisticsClients[$name];
	}



	/**
	 * @param string $name
	 * @param LogisticsClient $logisticsClient
	 */
	private function registerClient($name, LogisticsClient $logisticsClient)
	{
		$this->logisticsClients[$name] = $logisticsClient;
	}

}
