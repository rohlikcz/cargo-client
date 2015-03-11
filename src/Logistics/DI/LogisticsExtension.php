<?php

namespace Logistics\DI;

use Logistics\Connector;
use Logistics\Consumer;
use Logistics\InvalidConfigException;
use Logistics\LogisticsClient;
use Logistics\MemoryTokenStorage;
use Logistics\RequestFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Config\Helpers;
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
			$builder->addDefinition($tokenStorageName = $this->prefix('tokenFactory'))
				->setClass(MemoryTokenStorage::class);

			$tokenStorageReference = self::formatServiceReference($tokenStorageName);

		} else {
			// touch reference to validate it
			if (!$builder->getServiceName($tokenStorageReference = $config['tokenStorage'])) {
				throw new InvalidConfigException("Invalid reference to service implementing ITokenStorage given: $config[tokenStorage]");
			}
		}

		if (array_key_exists('clients', $config) && is_array($config['clients'])) {
			foreach ($config['clients'] as $name => $clientConfig) {
				$this->configureClient(
					Helpers::merge($clientConfig, $builder->expand($this->defaults)),
					$tokenStorageReference,
					$name
				);
			}

		} else {
			$this->configureClient(
				Helpers::merge($config, $builder->expand($this->defaults)),
				$tokenStorageReference
			);
		}
	}



	/**
	 * @param array $config
	 * @param string $tokenStorageReference
	 * @param string|NULL $name
	 * @throws InvalidConfigException
	 */
	private function configureClient(array $config, $tokenStorageReference, $name = NULL)
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

		$builder->addDefinition($consumerName = $this->prefix("consumer$name"))
			->setClass(Consumer::class, [$config['appId'], $config['secret']])
			->setAutowired(FALSE);

		$consumerRefernce = self::formatServiceReference($consumerName);

		if (!array_key_exists('requestFactory', $config)) {
			$builder->addDefinition($requestFactoryName = $this->prefix("requestFactory$name"))
				->setClass(RequestFactory::class, [$config['apiBaseUrl']])
				->setAutowired(FALSE);

			$requestFactoryReference = self::formatServiceReference($requestFactoryName);

		} else {
			// touch reference to validate it
			if (!$builder->getServiceName($requestFactoryReference = $config['requestFactory'])) {
				throw new InvalidConfigException("Invalid reference to service implementing IRequestFactory given: $config[requestFactory]");
			}
		}

		$builder->addDefinition($connectorName = $this->prefix("connector$name"))
			->setClass(Connector::class, [$consumerRefernce, $tokenStorageReference, $requestFactoryReference])
			->setAutowired(FALSE);

		$connectorReference = self::formatServiceReference($connectorName);

		$logisticsClientDefiniton = $builder->addDefinition($this->prefix("logisticsClient$name"))
			->setClass(LogisticsClient::class, [$connectorReference, $requestFactoryReference]);

		if ($name !== '') {
			$logisticsClientDefiniton->setAutowired(FALSE);
		}
	}



	/**
	 * @param string $name
	 * @return string
	 */
	private static function formatServiceReference($name)
	{
		return '@' . $name;
	}

}
