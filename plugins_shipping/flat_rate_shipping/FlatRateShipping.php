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
     * // TODO Enforce parameter type Address when class is ready
     *
     * @param Cart $cart SHipping cart for which to calculate shipping
     * @param Address $shipping_address Address to which products should be shipped
     */
    public function calculateShipping(Cart $cart, $shipping_address)
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
}