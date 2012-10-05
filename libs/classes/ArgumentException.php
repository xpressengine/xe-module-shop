<?php

class ArgumentException extends Exception
{
	public function __construct($message, $code = 0, Exception $previous = null) {
		ShopLogger::log("ArgumentException: <a href='#' class='logger_message_details'>" . $message . '</a><div style="display:none">' . $this->getTraceAsString() . '</div>');
		parent::__construct($message, $code, $previous);
	}
}