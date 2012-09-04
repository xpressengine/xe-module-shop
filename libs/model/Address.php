<?php
require_once dirname(__FILE__) . '/BaseItem.php';

class Address extends BaseItem
{

    public
        $address_srl,
        $member_srl,
        $address,
        $country,
        $region,
        $city,
        $postal_code,
        $phone,
        $fax,
        $type,
        $info,
        $regdate,
        $last_update;

    /** @var AddressRepository */
    public $repo;

}