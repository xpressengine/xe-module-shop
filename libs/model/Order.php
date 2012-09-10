<?php
class Order extends BaseItem
{

    public
        $order_srl,
        $module_srl,
        $cart_srl,
        $member_srl,
        $client_name,
        $client_email,
        $client_company,
        $billing_address,
        $shipping_address,
        $payment_method,
        $shipping_method,
        $shipping_cost,
        $total,
        $vat,
        $order_status,
        $ip,
        $regdate;

    /** @var OrderRepository */
    public $repo;

    public function save()
    {
        return $this->order_srl ? $this->repo->update($this) : $this->repo->insert($this);
    }

    public function __construct(array $data=null)
    {
        if ($data) {
            foreach (array('billing_address', 'shipping_address', 'shipping_method', 'payment_method') as $val) {
                if (!isset($orderData[$val])) {
                    //throw new Exception("Missing $val, can't continue.");
                }
            }
        }
        parent::__construct($data);
    }

    public function saveCartProducts(Cart $cart, $calculateTotal=true)
    {
        if (!$this->order_srl) throw new Exception('Order not persisted');
        if (!$cart->cart_srl) throw new Exception('Cart not persisted');
        //remove all already existing links
        $this->repo->deleteOrderProducts($this->order_srl);
        //set the new links
        $total = 0;
        foreach ($cart->getCartProducts() as $cp) {
            $this->repo->insertOrderProduct($this->order_srl, $cp->product_srl, $cp->quantity);
            $total += $cp->quantity * $cp->price;
        }
        $this->total = $total;
        if ($calculateTotal) $this->save();
    }

}