<?php

/**
 * Handles database operations for Shipment table
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class ShipmentRepository extends BaseRepository
{

    public function insert(Shipment &$shipment)
    {
        if ($shipment->shipment_srl) throw new Exception('A srl must NOT be specified for the insert operation!');
        $shipment->shipment_srl = getNextSequence();
        return $this->query('insertShipment', get_object_vars($shipment));
    }

    public function update(Shipment $shipment)
    {
        if (!is_numeric($shipment->order_srl)) throw new Exception('You must specify a srl for the updated shipment');
        return $this->query('updateShipment', get_object_vars($shipment));
    }

    public function getList($module_srl)
    {
        $params = array('module_srl'=> $module_srl, 'order_type' => 'desc');
        $output = $this->query('getShipmentList', $params);
        foreach ($output->data as $i=>$data) $output->data[$i] = new Shipment((array) $data);
        return $output;
    }

    public function getShipmentByOrderSrl($order_srl)
    {
        $output = $this->query('getShipmentByOrderSrl',array('order_srl'=> $order_srl));
        return empty($output->data) ? null : $shipment = new Shipment((array) $output->data);
    }

}