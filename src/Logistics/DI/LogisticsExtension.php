<?php

namespace Logistics\DI;

use Logistics\Connector;
use Logistics\Consumer;
use Logistics\InvalidConfigException;
use Logistics\LogisticsClient;
use Logistics\MemoryTokenStorage;
use Logistics\RequestFactory;
use Nette\DI\CompilerExtension;
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
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$message = 'parameter % in LogisticsExtension configuration';
		Validators::assertField($config, 'appId', 'string', $message);
		Validators::assertField($config, 'secret', 'string', $message);
		if (!$builder->parameters['debugMode']) {
			Validators::assertField($config, 'apiBaseUrl', 'url', $message);
			Validators::assertField($config, 'apiBaseUrl', 'pattern:https\:\/\/.*', $message);
		}

		$builder->addDefinition($this->prefix('consumer'))
			->setClass(Consumer::class, [$config['appId'], $config['secret']]);

		if (!array_key_exists('requestFactory', $config)) {
			$builder->addDefinition($this->prefix('requestFactory'))
				->setClass(RequestFactory::class)
				->setArguments([$config['apiBaseUrl']]);

		} else {
			// touch reference to validate it
			if (!$builder->getServiceName($config['requestFactory'])) {
				throw new InvalidConfigException("Invalid reference to service implementing IRequestFactory given: $config[requestFactory]");
			}
		}

		if (!array_key_exists('tokenStorage', $config)) {
			$builder->addDefinition($this->prefix('tokenFactory'))
				->setClass(MemoryTokenStorage::class);

		} else {
			// touch reference to validate it
			if (!$builder->getServiceName($config['tokenStorage'])) {
				throw new InvalidConfigException("Invalid reference to service implementing ITokenStorage given: $config[tokenStorage]");
			}
		}

		$builder->addDefinition($this->prefix('connector'))
			->setClass(Connector::class);

		$builder->addDefinition($this->prefix('logisticsClient'))
			->setClass(LogisticsClient::class);
	}

}
