<?php
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
        $telephone,
        $fax,
        $company,
        $default_shipping,
        $default_billing,
        $additional_info,
        $regdate,
        $last_update;

    /** @var AddressRepository */
    public $repo;

}