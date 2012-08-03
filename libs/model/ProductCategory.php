<?php

require_once dirname(__FILE__) . '/BaseItem.php';

/**
 * Model class for Product Category
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class ProductCategory extends BaseItem
{
	public $product_category_srl;
	public $module_srl;
	public $parent_srl = 0;
	public $filename;
	public $title;
	public $description;
	public $product_count = 0;
	public $friendly_url;
	private $include_in_navigation_menu = 'Y';
	public $regdate;
	public $last_update;

	/**
	 * Constructor
	 * Can create a new empty object or from properties array
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $args array
	 */
	public function __construct($args = NULL)
	{
		if(isset($args))
		{
			if(isset($args->product_category_srl)) $this->product_category_srl = $args->product_category_srl;
			if(isset($args->module_srl)) $this->module_srl = $args->module_srl;
			if(isset($args->parent_srl)) $this->parent_srl = $args->parent_srl;
			if(isset($args->filename)) $this->filename = $args->filename;
			if(isset($args->title)) $this->title = $args->title;
			if(isset($args->description)) $this->description = $args->description;
			if(isset($args->product_count)) $this->product_count = $args->product_count;
			if(isset($args->friendly_url)) $this->friendly_url = $args->friendly_url;
			if(isset($args->include_in_navigation_menu)) $this->setIncludeInNvaigationMenu($args->include_in_navigation_menu);
			if(isset($args->regdate)) $this->regdate = $args->regdate;
			if(isset($args->last_update)) $this->last_update = $args->last_update;
		}

	}

	/**
	 * Getter for [include_in_navigation_menu]
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function getIncludeInNavigationMenu()
	{
		return $this->include_in_navigation_menu;
	}

	/**
	 * Setter for [include_in_navigation_menu]
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $include_in_navigation_menu char
	 */
	public function setIncludeInNvaigationMenu($include_in_navigation_menu)
	{
		if(!isset($include_in_navigation_menu) || !in_array($include_in_navigation_menu, array('Y', 'N')))
		{
			$this->include_in_navigation_menu = 'Y';
			return;
		}

		$this->include_in_navigation_menu = $include_in_navigation_menu;
	}
}

/**
 * Models a Product category tree hierarchy
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class ProductCategoryTreeNode
{
	public $product_category;
	public $children = array();
	public $depth = 0;

	/**
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $pc ProductCategory
	 */
	public function __construct(ProductCategory $pc = NULL)
	{
		$this->product_category = $pc;
	}

	/**
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $node ProductCategoryTreeNode
	 */
	public function addChild(ProductCategoryTreeNode $node)
	{
		$this->children[$node->product_category->product_category_srl] = $node;
	}

	/**
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $node ProductCategoryTreeNode
	 */
	public function removeChild(ProductCategoryTreeNode $node)
	{
		unset($this->children[$node->product_category->product_category_srl]);
	}

	/**
	 *  Converts tree to flat structure easily iterable in template files
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $depth int
	 * @param $index 0
	 */
	public function toFlatStructure($depth = 0, $index = 0)
	{
		$flat_structure = array();
		foreach($this->children as $node)
		{
			$node->depth = $depth;
			$flat_structure[$index++] = $node;
			if(count($node->children))
			{
				$children_flat_structure = $node->toFlatStructure($depth + 1, $index);
				$index += count($children_flat_structure);
				$flat_structure = array_merge($flat_structure, $children_flat_structure);
			}
		}
		return $flat_structure;
	}

}

/* End of file ProductCategory.php */
/* Location: ./modules/shop/libs/ProductCategory.php */
