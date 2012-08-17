<?php

require_once dirname(__FILE__) . '/../model/Cart.php';
require_once "BaseRepository.php";

/**
 * Handles database operations for the shopping Cart
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class CartRepository extends BaseRepository
{

    //Cart:

    public function insertCart(Cart &$cart)
    {
        if ($cart->cart_srl) throw new Exception('A srl must NOT be specified for the insert operation!');
        $cart->cart_srl = getNextSequence();
        return $this->query('insertCart', get_object_vars($cart));
    }

    public function updateCart(Cart $cart)
    {
        if (!is_numeric($cart->cart_srl)) throw new Exception('You must specify a srl for the updated cart');
        return $this->query('updateCart', get_object_vars($cart));
    }

    public function deleteCarts(array $cart_srls)
    {
        return $this->query('deleteCarts', array('cart_srls' => $cart_srls));
    }

    public function deleteCartsByModule($module_srl)
    {
        return $this->query('deleteCarts', array('module_srl' => $module_srl));
    }

    public function getCartByMember($member_srl, $module_srl)
    {
        $output = $this->query('getCartByMember', array('member_srl' => $member_srl, 'module_srl' => $module_srl));
        if (!empty($output->data)) return new Cart($output->data);
        $cart = new Cart(array(
            'module_srl' => $module_srl,
            'member_srl' => $member_srl,
            'session_id' => session_id(),
            'items'      => 0
        ));
        $this->insertCart($cart);
        return $cart;
    }

    public function getCartByGuest($guest_srl, $module_srl)
    {
        $output = $this->query('getCartByGuest', array('guest_srl' => $guest_srl, 'module_srl' => $module_srl));
        if (!empty($output->data)) return new Cart($output->data);
        $cart = new Cart(array(
            'module_srl' => $module_srl,
            'guest_srl' => $guest_srl,
            'session_id' => session_id(),
            'items'      => 0
        ));
        $this->insertCart($cart);
        return $cart;
    }

    //CartProduct:

    public function insertCartProduct($cart_srl, $product_srl, $quantity=1)
    {
        return $this->query('insertCartProduct', array('cart_srl' => $cart_srl, 'product_srl' => $product_srl, 'quantity' => $quantity));
    }

    public function getCartProducts($cart_srl, array $product_srls)
    {
        return $this->query('getCartProducts', array('cart_srl' => $cart_srl, 'product_srls' => $product_srls));
    }

    public function deleteCartProducts($cart_srl, array $product_srls)
    {
        return $this->query('deleteCartProducts', array('cart_srl' => $cart_srl, 'product_srls' => $product_srls));
    }

}