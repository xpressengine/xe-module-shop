<?php
require_once dirname(__FILE__) . '/BaseItem.php';

class Cart extends BaseItem
{

    public
        $cart_srl,
        $module_srl,
        $member_srl,
        $guest_srl,
        $session_id,
        $items = 0,
        $regdate,
        $last_update;

    /** @var CartRepository */
    public $repo;

    public function save()
    {
        return $this->cart_srl ? $this->repo->updateCart($this) : $this->repo->insertCart($this);
    }

    public function addProduct($product, $quantity=1)
    {
        if (!$this->cart_srl) throw new Exception('Cart is not persisted');
        $product_srl = ( $product instanceof Product ? $product->product_srl : $product );
        //check if product already added
        $output = $this->repo->getCartProducts($this->cart_srl, array($product_srl));
        if (empty($output->data)) {
            return $this->repo->insertCartProduct($this->cart_srl, $product_srl, $quantity);
        }
        //if already exists increase quantity
        return $this->setProductQuantity($product_srl, $output->data->quantity + $quantity);
    }

    public function setProductQuantity($product_srl, $quantity)
    {
        if (!$this->cart_srl) throw new Exception('Cart is not persisted');
        return $this->repo->updateCartProduct($this->cart_srl, $product_srl, $quantity);
    }

}