<?php
/**
 * Base model class for Product
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
abstract class Product extends BaseItem
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
	public $images = array();
	public $primary_image;
	public $primary_image_filename;


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
				if($field == "configurable_attributes")
				{
					foreach($value as $attribute_srl)
					{
						$this->configurable_attributes[$attribute_srl] = "";
					}
				}
				else
				{
					$this->$field = $value;
				}
			}
			elseif(strpos($field, 'attribute_') === 0)
			{
				$attribute_srl = str_replace('attribute_', '', $field);
				$this->attributes[$attribute_srl] = $value;
			}
		}
	}

	/**
	 * Checks if product is simple
	 *
	 * @return boolean
	 */
	public function isSimple()
	{
		return $this->product_type == 'simple';
	}

	/**
	 * Checks if product is configurable
	 *
	 * @return boolean
	 */
	public function isConfigurable()
	{
		return $this->product_type == 'configurable';
	}

    /**
     * Return path to product image
     */
    public function getPrimaryImagePath()
    {
        if($this->primary_image_filename)
        {
            return "./files/attach/images/shop/$this->module_srl/product-images/$this->product_srl/$this->primary_image_filename";
        }
        else
        {
            return "./files/attach/shop/$this->module_srl/img/missingProduct.png";
        }
    }
}

/**
 * Model class for a simple product
 */
class SimpleProduct extends Product
{
	public function __construct($args = null)
	{
		parent::__construct($args);
		$this->product_type = 'simple';
	}

    public function getRepo()
    {
        return 'ProductRepository';
    }
}

/**
 * Model class for configurable product
 */
class ConfigurableProduct extends Product
{
	public $associated_products = array();
	public $configurable_attributes = array(); // Associated array: [attribute srl] => [attribute title]

	public function __construct($args = null)
	{
		parent::__construct($args);
		$this->product_type = 'configurable';
	}

    public function getRepo()
    {
        return 'ProductRepository';
    }
}

/* End of file Product.class.php */
/* Location: ./modules/shop/libs/Product.class.php */
