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
}

/* End of file Product.class.php */
/* Location: ./modules/shop/libs/Product.class.php */
