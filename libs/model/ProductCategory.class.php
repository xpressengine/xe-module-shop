<?php

require dirname(__FILE__) . '/ShopItem.class.php';

/**
 * Model class for Product Category
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class ProductCategory extends ShopItem
{
	public $product_category_srl;
	public $module_srl;
	public $parent_srl;
	public $file_srl;
	public $title;
	public $description;
	public $product_count;
	public $friendly_url;
	public $include_in_navigation_menu;
	public $regdate;
	public $last_update;
}

/* End of file ProductCategory.class.php */
/* Location: ./modules/shop/libs/ProductCategory.class.php */
