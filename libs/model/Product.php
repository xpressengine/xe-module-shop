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

    /**
     * Constructor
     * Can create a new empty object or from properties array
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $args array
     */
    public function __construct($args = NULL)
    {
        if(isset($args))
        {
            if(isset($args->product_srl)) $this->product_srl = $args->product_srl;
            if(isset($args->member_srl)) $this->member_srl = $args->member_srl;
            if(isset($args->module_srl)) $this->module_srl = $args->module_srl;
            if(isset($args->parent_product_srl)) $this->parent_product_srl = $args->parent_product_srl;
            if(isset($args->product_type)) $this->product_type = $args->product_type;
            if(isset($args->title)) $this->title = $args->title;
            if(isset($args->description)) $this->description = $args->description;
            if(isset($args->short_description)) $this->short_description = $args->short_description;
            if(isset($args->sku)) $this->sku = $args->sku;
            if(isset($args->weight)) $this->weight = $args->weight;
            if(isset($args->status)) $this->status = $args->status;
            if(isset($args->friendly_url)) $this->friendly_url = $args->friendly_url;
            if(isset($args->price)) $this->price = $args->price;
            if(isset($args->qty)) $this->qty = $args->qty;
            if(isset($args->in_stock)) $this->in_stock = $args->in_stock;
            if(isset($args->related_products)) $this->related_products = $args->related_products;
            if(isset($args->regdate)) $this->regdate = $args->regdate;
            if(isset($args->last_updated)) $this->last_update = $args->last_updated;
        }

    }
}


/* End of file Product.class.php */
/* Location: ./modules/shop/libs/Product.class.php */
