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

    //atomic operations:

    //Cart:

    public function insertCart(Cart &$cart)
    {
        if ($cart->cart_srl) throw new Exception('A srl must NOT be specified for the insert operation!');
        $cart->cart_srl = getNextSequence();
        $output = executeQuery('shop.insertCart', $cart);
        return self::check($output);
    }

    public function updateCart(Cart $cart)
    {
        if (!is_numeric($cart->cart_srl)) throw new Exception('You must specify a srl for the updated cart');
        $output = executeQuery('shop.updateCart', $cart);
        return self::check($output);
    }

    public function deleteCarts(array $cart_srls)
    {
        $output = executeQuery('shop.deleteCarts', (object) array('cart_srls' => $cart_srls));
        return self::check($output);
    }

    public function deleteCartsByModule($module_srl)
    {
        $output = executeQuery('shop.deleteCarts', (object) array('module_srl' => $module_srl));
        return self::check($output);
    }

    public function getCartByMember($member_srl, $module_srl)
    {
        $output = executeQuery('shop.getCartByMember', (object) array('member_srl' => $member_srl, 'module_srl' => $module_srl));
        return self::check($output);
    }

    public function getCartByGuest($guest_srl, $module_srl)
    {
        $output = executeQuery('shop.getCartByGuest', (object) array('guest_srl' => $guest_srl, 'module_srl' => $module_srl));
        return self::check($output);
    }

    //CartProduct:

    public function insertCartProduct($cart_srl, $product_srl)
    {
        $output = executeQuery('shop.insertCartProduct', (object) array('cart_srl' => $cart_srl, 'product_srl' => $product_srl));
        return self::check($output);
    }

    public function getCartProducts($cart_srl, array $product_srls)
    {
        $output = executeQuery('shop.getCartProducts', (object) array('cart_srl' => $cart_srl, 'product_srls' => $product_srls));
        return self::check($output);
    }

    public function deleteCartProducts($cart_srl, array $product_srls)
    {
        $output = executeQuery('shop.deleteCartProducts', (object) array('cart_srl' => $cart_srl, 'product_srls' => $product_srls));
        return self::check($output);
    }


    //compound operations:

    public function getCart($module_srl=null)
    {
        //TODO: find guest srl (guest workflow needs implemented)
        $guest_srl = null;
        $member_srl = self::getMemberSrl();
        $output = $member_srl ? $this->getCartByMember($member_srl, $module_srl) : $this->getCartByGuest($guest_srl, $module_srl);
        if (empty($output->data)) {
            $cart = new Cart(array(
                'module_srl' => $module_srl,
                'member_srl' => self::getMemberSrl(),
                'guest_srl' => self::getGuestSrl(),
                'session_id' => '',
                'items' => 0
            ));
            $output = $this->insertCart($cart);
        }
    }

}