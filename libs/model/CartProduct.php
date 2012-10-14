<?php

class CartProduct extends BaseItem implements IProductItem
{
    private $product;
    public $quantity = 1;
    public $cart_product_srl;
    public $cart_product_title;
    public $cart_product_price;

    public function __construct($data)
    {
        $this->setProduct(ProductFactory::buildInstance($data));

        if ($this->getProduct()->isDownloadable()){
            // cannot have more than one item for each downloadable product
            $data->quantity = 1;
        }

        $this->cart_product_srl = $data->cart_product_srl;
        $this->cart_product_title = $data->cart_product_title;
        $this->cart_product_price = $data->cart_product_price;
        $this->quantity = $data->quantity ? $data->quantity : 1;
        parent::__construct();
    }


    public function getRepo()
    {
        return "CartRepository";
    }

    public function setProduct(ICartItemProduct $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return ICartItemProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    public function __get($property)
    {
        /**
         * If property is not defined, call getter, if any
         */
        if(method_exists($this, 'get' . ucfirst($property)))
        {
            return call_user_func(array($this, 'get' . ucfirst($property)));
        }
        if(method_exists($this, 'is' . ucfirst($property)))
        {
            return call_user_func(array($this, 'is' . ucfirst($property)));
        }
        if(isset($this->product->$property))
        {
            return $this->product->$property;
        }
        return NULL;
    }

    /**
     * Product title
     */
    public function getTitle()
    {
        return $this->product ? $this->product->title : $this->cart_product_title;
    }

    /**
     * Ordered quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Price
     */
    public function getPrice($discounted = true)
    {
        return $this->product ? $this->product->getPrice($discounted) : $this->cart_product_price;
    }

    function getThumbnailPath($width = 80, $height = 0, $thumbnail_type = '')
    {
        if($this->product) {
            if($this->product->parent_product_srl){
                $productRepo = new ProductRepository();
                $parent_product = $productRepo->getProduct($this->product->parent_product_srl);
                return $parent_product->getPrimaryImage()->getThumbnailPath($width, $height, $thumbnail_type);
            }else{
                return $this->product->getPrimaryImage()->getThumbnailPath($width, $height, $thumbnail_type);
            }
        }
        else{
            return '';
        }
    }

    /**
     * // TODO When accesing availability like this: $cart_product->available
     * // the default value for $checkIfInStock=true is used; This in not always correct! To investigate
     *
     * @param bool $checkIfInStock
     * @return bool
     */
    public function isAvailable()
    {
		$shopInfo = new ShopInfo($this->product->module_srl);
		$checkIfInStock = ($shopInfo->getOutOfStockProducts() == 'Y');

        if ($this->product->isPersisted()) {
            return $this->product->isAvailable($checkIfInStock);
        }
        return FALSE;
    }
}