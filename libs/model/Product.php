<?php

require_once dirname(__FILE__) . '/BaseItem.php';

/**
 * Model class for Product
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class Product extends BaseItem
{
    public $product_srl;
    public $member_srl;
    public $module_srl;
    public $parent_product_srl;
    public $product_type;
    public $title;
    public $description;
    public $short_description;
    public $sku;
    public $weight;
    public $status;
    public $friendly_url;
    public $price;
    public $qty;
    public $in_stock;
    public $regdate;
    public $last_updated;
    public $related_products;
    public $categories = array();
	public $attributes = array();
	public $configurable_attributes = array();
	public $images = array();


	/**
	 * Constructor override - initialises default values when none given
	 */
	public function __construct($args = NULL)
	{
		parent::__construct($args);

		if(!isset($this->description)) $this->description = "";
		if(!isset($this->short_description)) $this->short_description = "";
		if(!isset($this->status)) $this->status = "enabled";
		if(!isset($this->friendly_url)) $this->friendly_url = $this->sku;
	}

	/**
	 * Loads an array into object properties
	 *
	 * @param array $data Array with values for properties
	 *
	 * @override
	 */
	public function loadFromArray(array $data)
	{
		foreach($data as $field => $value)
		{
			if(property_exists(get_called_class(), $field))
			{
				$this->$field = $value;
			}
			elseif(strpos($field, 'attribute_') === 0)
			{
				$attribute_srl = str_replace('attribute_', '', $field);
				$this->attributes[$attribute_srl] = $value;
			}
		}
	}
}


/* End of file Product.class.php */
/* Location: ./modules/shop/libs/Product.class.php */
