<?php

class OrderProduct extends BaseItem implements IProductItem
{
    public $order_srl;
    public $product_srl;
    public $quantity; // Ordered quantity

    public $member_srl;
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
    public $qty; // Stock quantity
    public $in_stock;
    public $primary_image_filename;
    public $related_products;
    public $regdate;
    public $last_update;

    public function getRepo()
    {
        return "OrderRepository";
    }

    /**
     * Number of items ordered
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Product title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    function getThumbnailPath($width = 80, $height = 0, $thumbnail_type = '')
    {
        $thumbnail = new ShopThumbnail($this->order_srl, $this->primary_image_filename);
        return $thumbnail->getThumbnailPath($width, $height, $thumbnail_type);
    }
}