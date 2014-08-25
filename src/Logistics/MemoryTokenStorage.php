<?php

namespace Logistics;

use Nette\Object;



class MemoryTokenStorage extends Object implements ITokenStorage
{

	/**
	 * @var Token[]
	 */
	private $tokens = [];



	/**
	 * @param Token $token
	 * @throws InvalidTokenException
	 */
	public function saveToken(Token $token)
	{
		if ($token->isExpired()) {
			throw new InvalidTokenException("It doesn't make sense to save expired token.");
		}
		$this->tokens[$token->getAppId()][$token->getToken()] = $token;
	}



	/**
	 * @param Consumer $consumer
	 * @return Token
	 * @throws TokenNotFoundException
	 */
	public function findToken(Consumer $consumer)
	{
		$appId = $consumer->getAppId();

		if (array_key_exists($appId, $this->tokens)) {
			foreach ($this->tokens[$appId] as $key => $token) {

				if ($token->isExpired()) {
					unset($this->tokens[$appId][$key]);

				} else {
					return $token;
				}
			}
		}
		throw new TokenNotFoundException("Missing valid token for consumer with ID $appId.");
	}



	/**
	 * @param Token $token
	 */
	public function markTokenAsBroken(Token $token)
	{
		unset($this->tokens[$token->getAppId()][$token->getToken()]);
	}

}
