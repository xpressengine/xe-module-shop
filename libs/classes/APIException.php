<?php

class APIException extends Exception
{
	public function __construct($message, $code = 0, Exception $previous = null) {
		$log_message = "APIException: <a href='#' class='logger_message_details'>" . $message . '</a>';
		$log_message .='<div style="display:none">' . $this->getTraceAsString() . '</br>';
		$log_message .= print_r($_REQUEST, true) . '</div>';

		ShopLogger::log($log_message);

		parent::__construct($message, $code, $previous);
	}
}