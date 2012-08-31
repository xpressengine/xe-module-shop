<?php

require_once dirname(__FILE__) . '/../ShippingMethodAbstract.php';

class FlatRateShipping extends ShippingMethodAbstract implements ShippingMethodInterface
{
    public function __construct()
    {
        $this->shipping_method_dir = _XE_PATH_ . 'modules/shop/shipping/flat_rate_shipping';
        parent::__construct();
    }

}