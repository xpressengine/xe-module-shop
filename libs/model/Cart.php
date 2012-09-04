<?php
require_once dirname(__FILE__) . '/BaseItem.php';

class Cart extends BaseItem
{

    public
        $cart_srl,
        $module_srl,
        $member_srl,
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

    /**
     * Returns an array necessary for selecting the cart object
     *
     * @return array sufficient data for cart identification (for the select query)
     * @throws Exception Invalid input
     */
    public static function validateParamsForUniqueIdentification($module_srl=null, $cart_srl=null, $member_srl=null, $session_id=null)
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

    public function getProducts()
    {
        $output = $this->query('getCartProductsList', array_merge(array('cart_srl'=>$this->cart_srl)));
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

    public function getProductsList(array $args=array())
    {
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
        $output = $this->query('deleteCartProducts', array('cart_srl'=>$this->cart_srl, 'product_srls'=>$product_srls));
        //TODO: optimize queries here
        $this->items = $this->count(true);
        $this->save();
    }


    public function updateProducts(array $quantities)
    {
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
        $data = array('cart_srl' => $this->cart_srl, 'module_srl' => $this->module_srl, 'member_srl'=>$this->member_srl);
        $data = array_merge( $data, $this->formTranslation($orderData) );
        $order = new Order($data);
        $order->save();
        return $order;
    }

    private function formTranslation(array $input)
    {
        // $data should be in the format compatible with Order's constructor
        $data = array();

        if (self::validateFormBlock($login = $input['login'])) {
            //login or exception
            return true;
        } elseif (self::validateFormBlock($billing = $input['billing'])) {
            $data['billing_address'] = serialize(array(
                'address' => $billing['address'],
                'country' => $billing['country'],
                'region'  => $billing['region'],
                'city'    => $billing['city'],
                'zip'     => $billing['zip'],
                'fax'     => $billing['fax'],
                'phone'   => $billing['phone']
            ));
            $data['client_name'] = $billing['firstname'] . ' ' . $billing['lastname'];
            $data['client_email'] = $billing['email'];
            $data['client_company'] = $billing['company'];
        }
        elseif (self::validateFormBlock($billing = $input['shipping'])) {
        }
        elseif (self::validateFormBlock($billing = $input['payment'])) {
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
        //Checks if $array has at least one value, but this could get more complex
        foreach ($array as $val) if ($val) return true;
        return false;
    }

}