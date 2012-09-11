<?php
/**
 * @author Florin Ercus (dev@xpressengine.org)
 */
class Cart extends BaseItem
{

    public
        $cart_srl,
        $module_srl,
        $member_srl,
        $session_id,
        $billing_address_srl,
        $shipping_address_srl,
        $items = 0,
        $extra,
        $regdate,
        $last_update;

    /** @var CartRepository */
    public $repo;

    public function save()
    {
        return $this->cart_srl ? $this->repo->updateCart($this) : $this->repo->insertCart($this);
    }

    public function delete()
    {
        $this->query('deleteCarts', array('cart_srls' => array($this->cart_srl)));
        $this->query('deleteCartProducts', array('cart_srl'=> $this->cart_srl));
    }

    #region cart & stuff
    /**
     * @param      $product Product or product_srl
     * @param int  $quantity
     * @param bool $relativeQuantity
     *
     * @return mixed
     * @throws Exception
     */
    public function addProduct($product, $quantity=1, $relativeQuantity=true)
    {
        if (!$this->cart_srl) throw new Exception('Cart is not persisted');
        $product_srl = ( $product instanceof Product ? $product->product_srl : $product );
        if (!$product_srl) throw new Exception('Product is not persisted');
        //check if product already added
        $output = $this->repo->getCartProducts($this->cart_srl, array($product_srl));
        if (empty($output->data)) {
            $return = $this->repo->insertCartProduct($this->cart_srl, $product_srl, $quantity);
        }
        else $return = $this->setProductQuantity($product_srl, $relativeQuantity ? $output->data->quantity + $quantity : $quantity);

        //count with quantities
        $this->items = $this->count(true);
        $this->save();

        return $return;
    }

    /**
     * @param bool $sumQuantities take quantities into account or only count unique items?
     *
     * @return mixed number of items in cart
     */
    public function count($sumQuantities=false)
    {
        return $this->repo->countCartProducts($this->module_srl, $this->cart_srl, $this->member_srl, $this->session_id, $sumQuantities);
    }

    public function setProductQuantity($product_srl, $quantity)
    {
        if (!$this->cart_srl) throw new Exception('Cart is not persisted');
        return $this->repo->updateCartProduct($this->cart_srl, $product_srl, $quantity);
    }

    public function getProducts()
    {
        if (!$this->cart_srl) throw new Exception('Cart is not persisted');
        $output = $this->query('getCartAllProducts', array('cart_srl'=> $this->cart_srl));
        foreach ($output->data as $i=>&$data) {
            if ($data->product_srl) {
                $product = new SimpleProduct($data);
                $product->quantity = $data->quantity;
                $data = $product;
            }
            else unset($output->data[$i]);
        }
        return $output->data;
    }

    public function getCartProducts()
    {
        if (!$this->cart_srl) throw new Exception('Cart is not persisted');
        return $this->query('getAllCartProducts', array('cart_srl'=> $this->cart_srl))->data;
    }

    public function getProductsList(array $args=array())
    {
        if (!$this->cart_srl) throw new Exception('Cart is not persisted');
        $output = $this->query('getCartProductsList', array_merge(array('cart_srl'=>$this->cart_srl), $args));
        foreach ($output->data as $i=>&$data) {
            if ($data->product_srl) {
                $product = new SimpleProduct($data);
                $product->quantity = $data->quantity;
                $data = $product;
            }
            else unset($output->data[$i]);
        }
        return $output;
    }

    public function removeProducts(array $product_srls)
    {
        if (empty($product_srls)) throw new Exception('Empty array $products_srls');
        $output = $this->query('deleteCartProducts', array('cart_srl'=>$this->cart_srl, 'product_srls'=>$product_srls));
        //TODO: optimize queries here
        $this->items = $this->count(true);
        $this->save();
    }


    public function updateProducts(array $quantities)
    {
        if (empty($product_srls)) throw new Exception('Empty array $quantities');
        foreach ($quantities as $product_srl=>$quantity) {
            if (!is_numeric($product_srl) || !is_numeric($quantity)) throw new Exception('Problem with input $quantities array');
            if ($quantity == 0) $this->removeProducts(array($product_srl));
            else $this->query('updateCartProduct', array('cart_srl'=>$this->cart_srl, 'product_srl'=>$product_srl, 'quantity'=>$quantity));
        }
        //TODO: and here
        $this->items = $this->count(true);
        $this->save();
    }
    #endregion

    //region checkout
    /**
     * Checkout processor
     *
     * @param array $orderData
     *
     * @return Order
     * @throws Exception
     */
    public function checkout(array $orderData)
    {
        if (!$this->cart_srl) throw new Exception('Cart is not persisted');

        /* old save as order:
        $data = array_merge( $this->formTranslation($orderData), array(
            'cart_srl' => $this->cart_srl,
            'module_srl' => $this->module_srl,
            'member_srl'=> $this->member_srl)
        );
        $order = new Order($data);
        if ($this->getOrder()) {
            throw new Exception('Order already placed for current cart');
        }
        $order->save(); //obtain srl
        $order->saveCartProducts($this);
        $this->delete();
        return $order;
         * * */

        //save as cart:
        $orderData = $this->formTranslation($orderData);
        $this->setExtra($orderData['extra']);
        $this->billing_address_srl = $orderData['billing_address_srl'];
        $this->shipping_address_srl = $orderData['shipping_address_srl'];
        $this->save();
    }

    public function getAddresses()
    {
        if (!$this->member_srl) return false;
        $aRepo = new AddressRepository();
        return $aRepo->getAddresses($this->member_srl, true);
    }

    /**
     * retrieves Order attached to cart or null
     * @return null|Order
     */
    public function getOrder()
    {
        $output = $this->query('getCartOrder', array('cart_srl'=>$this->cart_srl));
        return empty($output->data) ? null : new Order((array) $output->data);
    }

    private function formTranslation(array $input)
    {
        $data = array('extra'=>array());
        if (self::validateFormBlock($general = $input['general'])) {
            //TODO: same for shipping_address_srl?
            $data['extra'] = array(
                'firstname' => $general['firstname'],
                'lastname'  => $general['lastname'],
                'email'     => $general['email']
            );
        }
        else throw new Exception('forgot about general info?');

        if (self::validateFormBlock($billing = $input['billing'])) {
            if (is_numeric($billing['address'])) {
                $data['billing_address_srl'] = $billing['address'];
            }
            elseif (self::validateFormBlock($newAddress = $input['new_billing_address'])) {
                $newAddress = new Address($newAddress);
                $newAddress->save();
                $data['billing_address_srl'] = $newAddress->address_srl;
            }
            else {
                throw new Exception('No billing address');
            }
        }

        if (self::validateFormBlock($shipping = $input['shipping'])) {
            $data['extra']['shipping_method'] = $shipping['method'];
            if (is_numeric($shipping['address'])) {
                $data['shipping_address_srl'] = $shipping['address'];
            } elseif (self::validateFormBlock($newAddress = $input['new_shipping_address'])) {
                $newAddress = new Address($newAddress);
                $newAddress->save();
                $data['shipping_address_srl'] = $newAddress->address_srl;
            }
            else {
                throw new Exception('No shipping address');
            }
        }
        if (self::validateFormBlock($payment = $input['payment'])) {
        }
        return empty($data) ? null : $data;
    }


    /**
     * @static Used to check if a form block has valid input (ex has any value)
     *
     * @param array $array
     *
     * @return bool
     */
    private static function validateFormBlock(array $array = null)
    {
        if ($array) foreach ($array as $val) if ($val) return true;
        return false;
    }
    //endregion

    //region extra
    public function setExtraArray(array $extra)
    {
        $this->extra = json_encode($extra);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getExtraArray()
    {
        return (array) json_decode($this->extra);
    }

    public function setExtra($key, $value=null)
    {
        if (!$a = $this->getExtraArray()) $a = array();
        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->setExtra($k, $v);
            }
        }
        else {
            $a[$key] = $value;
            $this->setExtraArray($a);
        }
        return $this;
    }

    public function getExtra($key)
    {
        $a = $this->getExtraArray();
        return isset($a[$key]) ? $a[$key] : null;
    }
    //endregion

}