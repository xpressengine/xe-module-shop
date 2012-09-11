<?php

/**
 * Handles database operations for the Address table
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class AddressRepository extends BaseRepository
{
    /**
     * insert Address
     *
     * @author Dan Dragan
     * @param Address $address
     * @return mixed
     * @throws Exception
     */
    public function insert(Address &$address)
    {
        if ($address->address_srl) throw new Exception('A srl must NOT be specified for the insert operation!');
        $address->address_srl = getNextSequence();
        return $this->query('insertAddress', get_object_vars($address));
    }

    /**
     * update Address
     *
     * @author Dan Dragan
     * @param Address $address
     * @return mixed
     * @throws Exception
     */
    public function update(Address &$address)
    {
        if (!$address->address_srl) throw new Exception('A srl must be specified for the update operation!');
        return $this->query('updateAddress', get_object_vars($address));
    }

    /**
     * Make all existing address not to be default for billing
     *
     * @author Dan Dragan
     * @param $member_srl
     * @return mixed
     */
    public function unsetDefaultBillingAddress($member_srl){
        $args = new stdClass();
        $args->member_srl = $member_srl;
        $args->default_billing = 'N';
        return $this->query('updateDefaultBillingAddress',$args);
    }

    /**
     * Make all existing address not to be default  for shipping
     *
     * @author Dan Dragan
     * @param $member_srl
     * @return mixed
     */
    public function unsetDefaultShippingAddress($member_srl){
        $args = new stdClass();
        $args->member_srl = $member_srl;
        $args->default_shipping = 'N';
        return $this->query('updateDefaultShippingAddress',$args);
    }

    /**
     * get address by address_srl
     *
     * @author Dan Dragan
     * @param $address_srl
     * @return Address
     */
    public function getAddress($address_srl){
        $args = new stdClass();
        $args->address_srl = $address_srl;
        $output = $this->query('getAddress',$args);
        return new Address($output->data);
    }

    /**
     * return all addresses separated into default and additional addresses
     *
     * @author Dan Dragan
     * @param $member_srl
     * @param $returnBulk boolean Tells wether to return a simple array of addresses or mark them accordingly (default billing etc)
     * @return stdClass
     */
    public function getAddresses($member_srl, $returnBulk=false)
    {
        $output = $this->query('getAddresses', array('member_srl'=>$member_srl), true);
        $bulk = array();
        if(count($output->data)){
            foreach($output->data as $data){
                $address = new Address($data);
                if ($returnBulk) {
                    $bulk[] = $address;
                    continue;
                }
                if($address->default_billing == 'Y') $default_billing = $address;
                if($address->default_shipping == 'Y') $default_shipping = $address;
                if($address->default_billing == 'N' && $address->default_shipping == 'N') $additional_addresses[] = $address;
            }
        }
        if ($returnBulk) return empty($bulk) ? null : $bulk;
        $addresses = new stdClass();
        $addresses->default_billing = $default_billing;
        $addresses->default_shipping = $default_shipping;
        $addresses->additional_addresses = $additional_addresses;
        $addresses->count = count($output->data);
        return $addresses;
    }

    /**
     * delete address by address_srl
     *
     * @author Dan Dragan
     * @param $address_srl
     * @return mixed
     */
    public function deleteAddress($address_srl){
        $args = new stdClass();
        $args->address_srl = $address_srl;
        return $this->query('deleteAddress',$args);
    }
}