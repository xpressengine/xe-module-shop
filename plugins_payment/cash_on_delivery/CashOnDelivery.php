<?php

class CashOnDelivery extends PaymentMethodAbstract
{
    public function processPayment(Cart $cart, &$error_message)
    {
        return TRUE;
    }

	/**
	 * Make sure all mandatory fields are set
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		return true;
	}
}