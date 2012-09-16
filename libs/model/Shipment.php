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

    public function save()
    {
        return $this->shipment_srl ? $this->repo->update($this) : $this->repo->insert($this);
    }

}