<?php
class Shipment extends BaseItem
{

    public
        $shipment_srl,
        $order_srl,
        $module_srl,
        $order,
        $package_number,
        $comments,
        $regdate;

    /** @var ShipmentRepository */
    public $repo;

}