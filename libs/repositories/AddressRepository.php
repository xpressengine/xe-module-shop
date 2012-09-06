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
        if ($address->order_srl) throw new Exception('A srl must NOT be specified for the insert operation!');
        $address->address_srl = getNextSequence();
        return $this->query('insertAddress', get_object_vars($address));
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
     * return all addresses separated into default and additional addresses
     *
     * @author Dan Dragan
     * @param $member_srl
     * @return stdClass
     */
    public function getAddresses($member_srl){
        $args = new stdClass();
        $args->member_srl = $member_srl;
        $output = $this->query('getAddresses',$args);
        foreach($output->data as $data){
            $address = new Address($data);
            if($address->default_billing == 'Y') $default_billing = $address;
                elseif($address->default_shipping == 'Y') $default_shipping = $address;
                    else $additional_addresses[] = $address;
        }
        $addresses = new stdClass();
        $addresses->default_billing = $default_billing;
        $addresses->default_shipping = $default_shipping;
        $addresses->additional_addresses = $additional_addresses;
        $addresses->count = count($output->data);
        return $addresses;
    }
}