<?php
class Shipment extends BaseItem
{

    public
        $shipment_srl,
        $order_srl,
        $module_srl,
        $package_number,
        $comments,
        $regdate;
    /** @var Order */
    public $order;
    /** @var ShipmentRepository */
    public $repo;

    public function save()
    {
        return $this->shipment_srl ? $this->repo->update($this) : $this->repo->insert($this);
    }

    public function checkAndUpdateStocks()
    {
        $products = $this->order->getProducts();
        $productRepo = new ProductRepository();
        /** @var $orderProduct OrderProduct */
        $productsEmptyStocks = array();
        foreach($products as $orderProduct){
            /** @var $product SimpleProduct */
            $product = $productRepo->getProduct($orderProduct->product_srl);
            if($orderProduct->quantity == $product->qty){
                $productsEmptyStocks[] = $product;
            }
            $product->substractFromStock($orderProduct->quantity);
        }
        return $productsEmptyStocks;
    }


}