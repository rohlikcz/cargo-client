<?php

namespace Logistics;



interface ITokenStorage
{

	/**
	 * @param Token $token
	 */
	function saveToken(Token $token);



	/**
	 * @param Consumer $consumer
	 * @return Token
	 * @throws TokenNotFoundException
	 */
	function findToken(Consumer $consumer);



	/**
	 * @param Token $token
	 */
	function markTokenAsBroken(Token $token);

} 
