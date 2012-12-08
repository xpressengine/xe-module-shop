<?php

class TableRateShipping extends ShippingMethodAbstract
{

	const TYPE_PRICE_DESTINATION = 'price_and_destination'
		, TYPE_WEIGHT_DESTINATION = 'weight_and_destination'
		, TYPE_ITEMS_COUNT_DESTINATION = 'items_count_and_destination';

	/**
	 * Checks is custom plugin parameters are set and valid;
	 * If no validation is needed, just return true;
	 * @return mixed
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		return true;
	}

	/**
	 * Calculates shipping rates
	 *
	 * @param Cart   $cart    Shipping cart for which to calculate shipping; includes shipping address
	 * @param String $service Represents the specific service for which to calcualte shipping (e.g. Standard or Priority)
	 */
	public function calculateShipping(Cart $cart, $service = NULL)
	{
		return 10;
	}

}