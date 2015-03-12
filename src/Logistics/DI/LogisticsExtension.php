<?php

namespace Logistics\DI;

use Logistics\Connector;
use Logistics\Consumer;
use Logistics\InvalidConfigException;
use Logistics\LogisticsClient;
use Logistics\LogisticsClientsPool;
use Logistics\MemoryTokenStorage;
use Logistics\RequestFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Config\Helpers;
use Nette\DI\ServiceDefinition;
use Nette\Utils\Validators;



class LogisticsExtension extends CompilerExtension
{

	/**
	 * @var array
	 */
	private $defaults = [
		'apiBaseUrl' => 'https://cargo.damejidlo.cz/api/logistics',
		'appId' => NULL,
		'secret' => NULL,
	];



	/**
	 * @throws InvalidConfigException
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		if (!array_key_exists('tokenStorage', $config)) {
			$tokenStorageDefinition =  $builder->addDefinition($tokenStorageName = $this->prefix('tokenFactory'))
				->setClass(MemoryTokenStorage::class);

		} else {
			// touch reference to validate it
			if (!$builder->getServiceName($tokenStorageDefinition = $config['tokenStorage'])) {
				throw new InvalidConfigException("Invalid reference to service implementing ITokenStorage given: $config[tokenStorage]");
			}
		}

		if (array_key_exists('clients', $config) && is_array($config['clients'])) {
			$clients = [];
			foreach ($config['clients'] as $name => $clientConfig) {
				$clients[$name] = $this->configureClient(
					Helpers::merge($clientConfig, $builder->expand($this->defaults)),
					$tokenStorageDefinition,
					$name
				);
			}

			$this->configureClientsPool($clients);

		} else {
			$this->configureClient(
				Helpers::merge($config, $builder->expand($this->defaults)),
				$tokenStorageDefinition
			);
		}
	}



	/**
	 * @param array $config
	 * @param string $tokenStorageDefinition
	 * @param string|NULL $name
	 * @return ServiceDefinition
	 * @throws InvalidConfigException
	 */
	private function configureClient(array $config, $tokenStorageDefinition, $name = NULL)
	{
		$name = $name !== NULL ? ".$name" : '';
		$builder = $this->getContainerBuilder();

		$message = 'parameter % in LogisticsExtension configuration';
		Validators::assertField($config, 'appId', 'string', $message);
		Validators::assertField($config, 'secret', 'string', $message);
		if (!$builder->parameters['debugMode']) {
			Validators::assertField($config, 'apiBaseUrl', 'url', $message);
			Validators::assertField($config, 'apiBaseUrl', 'pattern:https\:\/\/.*', $message);
		}

		$consumerDefinition = $builder->addDefinition($consumerName = $this->prefix("consumer$name"))
			->setClass(Consumer::class, [$config['appId'], $config['secret']])
			->setAutowired(FALSE);

		if (!array_key_exists('requestFactory', $config)) {
			$requestFactoryDefinition = $builder->addDefinition($requestFactoryName = $this->prefix("requestFactory$name"))
				->setClass(RequestFactory::class, [$config['apiBaseUrl']])
				->setAutowired(FALSE);

		} else {
			// touch reference to validate it
			if (!$builder->getServiceName($requestFactoryDefinition = $config['requestFactory'])) {
				throw new InvalidConfigException("Invalid reference to service implementing IRequestFactory given: $config[requestFactory]");
			}
		}

		$connectorDefinition = $builder->addDefinition($connectorName = $this->prefix("connector$name"))
			->setClass(Connector::class, [$consumerDefinition, $tokenStorageDefinition, $requestFactoryDefinition])
			->setAutowired(FALSE);

		$logisticsClientDefiniton = $builder->addDefinition($this->prefix("logisticsClient$name"))
			->setClass(LogisticsClient::class, [$connectorDefinition, $requestFactoryDefinition]);

		if ($name !== '') {
			$logisticsClientDefiniton->setAutowired(FALSE);
		}

		return $logisticsClientDefiniton;
	}



	/**
	 * @param ServiceDefinition[] $clients
	 */
	private function configureClientsPool(array $clients)
	{
		$this->getContainerBuilder()->addDefinition($this->prefix('logisticsClientsPool'))
			->setClass(LogisticsClientsPool::class, [$clients]);
	}

}
