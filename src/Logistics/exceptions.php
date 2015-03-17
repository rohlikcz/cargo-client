<?php

namespace Logistics;

use Exception;



interface IException
{
}



abstract class TokenException extends Exception implements IException
{
}



class TokenNotFoundException extends TokenException
{
}



class InvalidTokenException extends TokenException
{
}



class RuntimeException extends Exception implements IException
{
}



class InvalidConfigException extends Exception implements IException
{
}



class BadResponseException extends Exception implements IException
{
}



class BadResponseCodeException extends BadResponseException
{
}


class ClientNotFoundException extends RuntimeException
{
}
