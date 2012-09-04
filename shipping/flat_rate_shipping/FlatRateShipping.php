<?php

require_once dirname(__FILE__) . '/../ShippingMethodAbstract.php';

class FlatRateShipping extends ShippingMethodAbstract implements ShippingMethodInterface
{
    public function __construct()
    {
        $this->shipping_method_dir = _XE_PATH_ . 'modules/shop/shipping/flat_rate_shipping';
        parent::__construct();
    }

    public function getType()
    {
        return $this->shipping_info->type;
    }

    private function setType($type)
    {
        if(!isset($type)) return;
        $this->shipping_info->type = $type;
    }

    public function getPrice()
    {
        return $this->shipping_info->price;
    }

    private function setPrice($price)
    {
        if(!isset($price)) return;
        $this->shipping_info->price = $price;
    }

    protected function saveShippingInfo($shipping_info)
    {
        $this->setPrice($shipping_info->price);
        $this->setType($shipping_info->type);
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
        if($this->getType() == 'per_item')
        {
            $products = $cart->getProducts();
            $total_quantity = 0;
            foreach($products as $product)
                $total_quantity += $product->quantity;
            return $total_quantity * $this->getPrice();
        }

        return $this->getPrice();
    }
}