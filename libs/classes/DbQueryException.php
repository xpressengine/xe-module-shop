<?php

class DBQueryException extends Exception
{
	public function __construct($message, $code = 0, Exception $previous = null) {
		ShopLogger::log($message);
		parent::__construct($message, $code, $previous);
	}
}