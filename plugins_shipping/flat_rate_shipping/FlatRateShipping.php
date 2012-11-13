<?php

require_once dirname(__FILE__) . '/../ShippingMethodAbstract.php';

class FlatRateShipping extends ShippingMethodAbstract
{
    public function __construct()
    {
        $this->shipping_method_dir = _XE_PATH_ . 'modules/shop/plugins_shipping/flat_rate_shipping';
        parent::__construct();
    }

    /**
     * Calculates shipping rates
     *
     * @param Cart $cart SHipping cart for which to calculate shipping
     * @param Address $shipping_address Address to which products should be shipped
     */
    public function calculateShipping(Cart $cart, $service = null)
    {
        if($this->type == 'per_item')
        {
            $products = $cart->getProducts();
            $total_quantity = 0;
            foreach($products as $product)
                $total_quantity += $product->quantity;
            return $total_quantity * $this->price;
        }

        return $this->price;
    }

	/**
	 * Checks is custom plugin parameters are set and valid;
	 * If no validation is needed, just return true;
	 * @return mixed
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if(!isset($this->price))
		{
			$error_message = 'msg_missing_shipping_price';
			return FALSE;
		}

		if(!isset($this->type))
		{
			$error_message = 'msg_missing_shipping_type';
			return FALSE;
		}

		if(!in_array($this->type, array('per_item', 'per_order')))
		{
			$error_message = 'msg_invalid_shipping_type';
			return FALSE;
		}
		if(!is_numeric($this->price))
		{
			$error_message = 'msg_invalid_shipping_price';
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Returns a list of available variants
	 * The structure is:
	 * array(
	 *     stdclass(
	 *         'name' => 'ups'
	 *         , 'display_name' => 'UPS'
	 *         , 'variant' => '01'
	 *         , 'variant_display_name' => 'Domestic'
	 *         ,  price => 12
	 * ))
	 *
	 * @param Address $shipping_address
	 * @return array
	 */
	public function getAvailableVariants(Cart $cart)
	{
		$variant = new stdClass();
		$variant->name = $this->getName();
		$variant->display_name = $this->getDisplayName();
		$variant->variant = null;
		$variant->variant_display_name = null;
		$variant->price = $this->calculateShipping($cart);
		return array($variant);
	}
}