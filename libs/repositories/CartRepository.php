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
    public function insert(Cart &$o)
    {
        if ($o->cart_srl) throw new Exception('A srl must NOT be specified for the insert operation!');
        $o->cart_srl = getNextSequence();
        $output = executeQuery('shop.insertCart', $o);
        if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        return $output;
    }
}