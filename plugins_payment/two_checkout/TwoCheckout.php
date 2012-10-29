<?php

class TwoCheckout extends PaymentMethodAbstract
{
	const GATEWAY_API_URL = 'https://www.2checkout.com/checkout/spurchase';

	public function isLive()
	{
		if($this->use_demo_mode === 'Y') return false;
		return true;
	}

	/**
	 * Checks is custom plugin parameters are set and valid;
	 * If no validation is needed, just return true;
	 * @return mixed
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if(!isset($this->sid)) return false;
		return true;
	}

	public function processPayment(Cart $cart, &$error_message)
	{
		// TODO: Implement processPayment() method.
	}
}