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

}