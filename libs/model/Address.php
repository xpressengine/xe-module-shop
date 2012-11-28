<?php
class Address extends BaseItem
{

    public
        $address_srl,
        $member_srl,
        $firstname,
        $lastname,
        $email,
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


    public function save()
    {
        return $this->address_srl ? $this->repo->update($this) : $this->repo->insert($this);
    }

    public function __toString()
    {
        return <<<GATA
$this->firstname $this->lastname,
$this->email,
$this->address,
$this->country,
$this->region,
$this->city,
$this->company
GATA;
;
    }

    public function isDefaultBillingAddress()
    {
        return $this->default_billing == 'Y' ? true : false;
    }

    public function isDefaultShippingAddress()
    {
        return $this->default_shipping == 'Y' ? true : false;
    }

	public function isValid()
	{
		if(is_null($this->postal_code)
			|| is_null($this->country)
			|| is_null($this->firstname)
			|| is_null($this->lastname)
			|| is_null($this->address)
			|| is_null($this->email)
			|| is_null($this->city)
		)
			return false;
		return true;
	}
}