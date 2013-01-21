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
    public $discount_price;
    public $qty;
    public $in_stock;
    public $is_featured;
    public $regdate;
    public $last_update;
    public $related_products;
    public $categories = array();
	public $attributes = array();
	public $images = array();
	public $primary_image;
	public $primary_image_filename;
    public $content_filename;
    public $document_srl; //used to link products to xe comments
    public $document;  // document item
    public $comment_list; // list of comments

    /** @var ProductRepository */
    public $repo;


	/**
	 * Constructor override - initialises default values when none given
	 */
	public function __construct($args = NULL)
	{
		parent::__construct($args);

		if(!isset($this->description)) $this->description = "";
		if(!isset($this->short_description)) $this->short_description = "";
		if(!isset($this->status)) $this->status = "enabled";
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
     * Checks if product is downloadable
     *
     * @return boolean
     */
    public function isDownloadable()
    {
        return 'downloadable' == $this->product_type;
    }

    /**
     * get product url
     * @param bool $relative
     * @return string
     */
    public function getUrl($relative=true)
    {
        if (!$this->friendly_url) return getUrl('', 'act', 'dispShopProduct', 'vid', BaseRepository::vid(), 'product_srl', $this->product_srl);
        $repo = $this->repo;
        return $repo::getUrl($this->friendly_url, $relative);
    }

    /**
     * retrieves product primary images
     * @return ProductImage
     */
    public function getPrimaryImage()
    {
        if($this->primary_image_filename && isset($this->images[$this->primary_image_filename]))
            return $this->images[$this->primary_image_filename];

        return new ProductImage(array(
                        'image_srl' => $this->product_srl
                        , 'module_srl' => $this->module_srl
                        , 'product_srl' => $this->product_srl
                        , 'filename' => $this->primary_image_filename
                        ));
    }

    /**
     * get product price
     * @param bool $discounted
     * @return mixed
     */
    public function getPrice($discounted = true){
        if($discounted && $this->discount_price > 0) return $this->discount_price;
        else return $this->price;
    }

    /**
     * verify if is in stock
     * @return bool
     */
    public function isInStock()
    {
        if($this->qty > 0) return true;
        return false;
    }

    /**
     * verify if product is available
     * @param bool $shopSettingsCheck
     * @return bool
     * @throws ShopException
     */
    public function isAvailable($shopSettingsCheck=true)
    {
        if (!$this->isPersisted()) {
            throw new ShopException('Product not persisted');
        }
        if ($shopSettingsCheck) {
            $shopInfo = new ShopInfo($this->module_srl);
            $shopSettingsCheck = ($shopInfo->getOutOfStockProducts() == 'Y');
        }
        return
            $this->status != 'disabled' &&
            (
                !$shopSettingsCheck ||
                ($shopSettingsCheck && $this->in_stock == 'Y')
            );
    }

}

/**
 * Model class for a simple product
 */
class SimpleProduct extends Product implements ICartItemProduct
{
    /** @var ProductRepository */
    public $repo;

    /**
     * constructor function
     * @param null $args
     */
    public function __construct($args = null)
	{
		parent::__construct($args);
		$this->product_type = 'simple';
	}

    /**
     * get Product Repository
     * @return null|string
     */
    public function getRepo()
    {
        return 'ProductRepository';
    }

    /**
     * substract from stock when a product is bought
     * @param $qty
     * @throws ShopException
     */
    public function substractFromStock($qty)
    {
        if($qty > $this->qty){
            throw new ShopException("You do not have enough items in stock to ship this order. Go and update stocks for product $this->product_srl !");
        }
        $this->qty -= $qty;
        $this->repo->updateProduct($this);
    }
}

/**
 * Model class for configurable product
 */
class ConfigurableProduct extends Product
{
	public $associated_products = array();
	public $configurable_attributes = array(); // Associated array: [attribute srl] => [attribute title]

    /**
     * constructor function
     * @param null $args
     */
    public function __construct($args = null)
	{
		parent::__construct($args);
		$this->product_type = 'configurable';
	}

    /**
     * get Product Repository
     * @return null|string
     */
    public function getRepo()
    {
        return 'ProductRepository';
    }
}

/**
 * Model class for configurable product
 */
class DownloadableProduct extends Product implements ICartItemProduct
{

    public function __construct($args = null)
    {
        parent::__construct($args);
        $this->product_type = 'downloadable';
    }

    public function getRepo()
    {
        return 'ProductRepository';
    }
}

/**
 * Interface meant to designate the products which can be inserted into a cart;
 * at this moment, only SimpleProduct and DownloadableProduct are qualified for.
 */
interface ICartItemProduct{}

class ProductFactory{
    static public function buildInstance($data){
        if ($data->product_type == "simple"){
            return new SimpleProduct($data);
        }elseif ($data->product_type == "downloadable"){
            return new DownloadableProduct($data);
        }elseif ($data->product_type == "configurable" || $data->configurable_attributes){
            return new ConfigurableProduct($data);
        }
        return new SimpleProduct($data);
    }
}

/* End of file Product.class.php */
/* Location: ./modules/shop/libs/Product.class.php */
