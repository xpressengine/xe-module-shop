<?php

/**
 * Handles database operations for the shopping Cart
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class CartRepository extends BaseRepository
{

    //region Cart operations
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
    //endregion


    //region CartProducts operations
    public function insertCartProduct($cart_srl, $product_srl, $quantity=1)
    {
        return $this->query('insertCartProduct', array('cart_srl' => $cart_srl, 'product_srl' => $product_srl, 'quantity' => $quantity));
    }

    public function getCartProducts($cart_srl, array $product_srls)
    {
        return $this->query('getCartProducts', array('cart_srl' => $cart_srl, 'product_srls' => $product_srls));
    }

    public function deleteCartProducts($cart_srl, array $product_srls=null)
    {
        return $this->query('deleteCartProducts', array('cart_srl' => $cart_srl, 'product_srls' => $product_srls));
    }

    public function updateCartProduct($cart_srl, $product_srl, $quantity)
    {
        return $this->query('updateCartProduct', array('cart_srl' => $cart_srl, 'product_srl' => $product_srl, 'quantity' => $quantity));
    }
    //endregion


    /**
     * This returns a cart object corresponding for the input parameters or creates a new cart
     * @return Cart|null
     */
    public function getCart($module_srl=null, $cart_srl=null, $member_srl=null, $session_id=null, $create=false)
    {
        $params = self::validateParamsForUniqueIdentification($module_srl, $cart_srl, $member_srl, $session_id);
        $output = $this->query('getCart', $params);
        if (empty($output->data)) {
            if ($create) {
                return $this->getNewCart($module_srl, $member_srl, $session_id);
            }
            else return null;
        }
        else return new Cart($output->data);
    }

    /**
     * Returns a cart object corresponding for the input parameters or null
     * @return Cart|null
     */
    public function hasCart($module_srl=null, $cart_srl=null, $member_srl=null, $session_id=null)
    {
        $params = self::validateParamsForUniqueIdentification($module_srl, $cart_srl, $member_srl, $session_id);
        $output = $this->query('getCart', $params);
        return empty($output->data) ? null : new Cart($output->data);
    }


    /**
     * Returns an array necessary for selecting the cart object
     *
     * @return array sufficient data for cart identification (for the select query)
     * @throws Exception Invalid input
     */
    public static function validateParamsForUniqueIdentification($module_srl = null, $cart_srl = null, $member_srl = null, $session_id = null)
    {
        if (is_numeric($cart_srl)) return array(
            'cart_srl' => $cart_srl
        );
        if (is_numeric($member_srl)) {
            if (is_numeric($module_srl)) return array(
                'member_srl' => $member_srl,
                'module_srl' => $module_srl
            );
            throw new Exception('Count not identify cart by member_srl (module_srl needed)');
        }
        if ($session_id) {
            if (is_numeric($module_srl)) return array(
                'session_id' => $session_id,
                'module_srl' => $module_srl
            );
            throw new Exception('Count not identify cart by session_id (module_srl needed)');
        }
        throw new Exception('Invalid input for cart identification');
    }

    public function getNewCart($module_srl, $member_srl = null, $session_id = null, $items = 0)
    {
        if (!$session_id) $session_id = session_id();
        $cart = new Cart(array(
            'module_srl' => $module_srl,
            'member_srl' => $member_srl,
            'session_id' => $session_id,
            'items'      => $items
        ));
        $cart->save();
        return $cart;
    }

    public function countCartProducts($module_srl=null, $cart_srl=null, $member_srl=null, $session_id=null, $sumQuantities=false)
    {
        $params = self::validateParamsForUniqueIdentification($module_srl, $cart_srl, $member_srl, $session_id);
        $what = ($sumQuantities ? 'total' : 'count');
        $rez = $this->query('getCartCount', $params)->data->$what;
        return $rez ? $rez : 0;
    }

}