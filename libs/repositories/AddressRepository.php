<?php

require_once dirname(__FILE__) . '/../model/Address.php';
require_once "BaseRepository.php";

/**
 * Handles database operations for the Address table
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class AddressRepository extends BaseRepository
{
    public function insert(Address &$address)
    {
        if ($address->order_srl) throw new Exception('A srl must NOT be specified for the insert operation!');
        $address->address_srl = getNextSequence();
        return $this->query('insertAddress', get_object_vars($address));
    }
}