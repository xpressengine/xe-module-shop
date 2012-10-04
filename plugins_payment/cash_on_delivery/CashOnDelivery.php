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
	public function isConfigured()
	{
		return true;
	}
}