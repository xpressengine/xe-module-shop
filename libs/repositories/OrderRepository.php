<?php

require_once dirname(__FILE__) . '/../model/Order.php';
require_once "BaseRepository.php";
require_once "CartRepository.php";

/**
 * Handles database operations for Orders table
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class OrderRepository extends BaseRepository
{

    public function getList($module_srl, $member_srl=null)
    {
        $params = array('module_srl'=> $module_srl);
        if ($member_srl) $params = array_merge($params, array('member_srl'=> $member_srl));
        $output = $this->query('getOrdersList', $params);
        foreach ($output->data as $i=>$data) $output->data[$i] = new Order((array) $data);
        return $output;
    }

    public function insert(Order &$order)
    {
        if ($order->order_srl) throw new Exception('A srl must NOT be specified for the insert operation!');
        $order->order_srl = getNextSequence();
        return $this->query('insertOrder', get_object_vars($order));
    }

    public function update(Order $order)
    {
        if (!is_numeric($order->order_srl)) throw new Exception('You must specify a srl for the updated order');
        return $this->query('updateOrder', get_object_vars($order));
    }

    public function deleteOrders(array $order_srls)
    {
        return $this->query('deleteOrders', array('order_srls' => $order_srls));
    }

    /**
     * Copies Cart properties into a new Order object
     * @param Cart $cart
     *
     * @return Order
     */
    public function getOrderFromCart(Cart $cart, $calculateNextSequence=true)
    {
        return new Order(array(
            'order_srl' => $calculateNextSequence ? getNextSequence() : null,
            'cart_srl' => $cart->cart_srl,
        ));
    }


    public function insertOrderProduct($order_srl, $product_srl, $quantity = 1)
    {
        return $this->query('insertOrderProduct', array('order_srl' => $order_srl, 'product_srl' => $product_srl, 'quantity' => $quantity));
    }

    public function deleteOrderProducts($order_srl, array $product_srls=null)
    {
        return $this->query('deleteOrderProducts', array('order_srl' => $order_srl, 'product_srls' => $product_srls));
    }

    public function getOrderBySrl($srl)
    {
        $output = $this->query('getOrderBySrl', array('order_srl'=> $srl));
        return empty($output->data) ? null : new Order((array) $output->data);
    }


}