<?php

require_once dirname(__FILE__) . '/BaseItem.php';

/**
 * Model class for Payment Gateway
 *
 * @author Daniel Ionescu (dev@xpressengine.org)
**/
class PaymentGateway extends BaseItem
{
    private $payment_gateway;

    public $id;
    public $display_name;  /// Display name
    public $name; /// Unique name = folder name
    public $status = 0;
    public $props;

    public function __construct()
    {

    }

    public static function getInstanceFromDatabaseInfo($data)
    {
        $instance = new PaymentGateway();
        $instance->id = $data->id;
        $instance->name = $data->name;
        $instance->display_name = $data->display_name;
        $instance->status = $data->status;
        $instance->props = $data->props;
        return $instance;
    }

    public static function getInstanceFromPaymentExtensionClass(PaymentGatewayAbstract $payment_gateway)
    {
        $instance = new PaymentGateway();
        $instance->id = null;
        $instance->status = 0;
        $instance->props = array();
        $instance->name = $payment_gateway->getName();
        $instance->display_name = $payment_gateway->getDisplayName();
        return $instance;
    }

}
