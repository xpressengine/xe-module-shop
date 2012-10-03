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
        $this->setProduct(new SimpleProduct($data));
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

    public function setProduct(SimpleProduct $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return SimpleProduct
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
        return null;
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
    public function getPrice()
    {
        return $this->product ? $this->product->price : $this->cart_product_price;
    }

    function getThumbnailPath($width = 80, $height = 0, $thumbnail_type = '')
    {
        return $this->product ? $this->product->getPrimaryImage()->getThumbnailPath($width, $height, $thumbnail_type) : '';
    }

    /**
     * // TODO When accesing availability like this: $cart_product->available
     * // the default value for $checkIfInStock=true is used; This in not always correct! To investigate
     *
     * @param bool $checkIfInStock
     * @return bool
     */
    public function isAvailable($checkIfInStock = true)
    {
        if ($this->product->isPersisted()) {
            return $this->product->isAvailable($checkIfInStock);
        }
        return false;
    }
}