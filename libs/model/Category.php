<?php

require_once dirname(__FILE__) . '/BaseItem.php';

/**
 * Model class for Product Category
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class Category extends BaseItem
{
	public $category_srl;
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
	 * Calls getters for private properties when accessed directly
	 *
	 * @param string $name Property name
	 *
	 * @return null|string
	 */
	public function __get($name)
	{
		if($name == 'include_in_navigation_menu')
		{
			return $this->getIncludeInNavigationMenu();
		}

		return NULL;
	}

	/**
	 * Calls setters for private properties when accessed directly
	 *
	 * @param string $name  Property name
	 * @param mixed  $value Property value
	 *
	 * @return void
	 */
	public function __set($name, $value)
	{
		if($name == 'include_in_navigation_menu')
		{
			$this->setIncludeInNavigationMenu($value);
		}
	}

	/**
	 * Isset check for private properties accessed directly
	 *
	 * @param string $name Property name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		if($name == 'include_in_navigation_menu')
		{
			return isset($this->include_in_navigation_menu);
		}
		return FALSE;
	}


	/**
	 * Getter for [include_in_navigation_menu]
	 *
	 * @return string
	 */
	public function getIncludeInNavigationMenu()
	{
		return $this->include_in_navigation_menu;
	}

	/**
	 * Setter for [include_in_navigation_menu]
	 *
	 * @param char $include_in_navigation_menu Accepts 'Y' or 'N'
	 *
	 * @return void
	 */
	public function setIncludeInNavigationMenu($include_in_navigation_menu)
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
class CategoryTreeNode
{
	public $category;
	public $children = array();
	public $depth = 0;

	/**
	 * Constructor
	 *
	 * @param Category $pc Category object associated with current tree node
	 */
	public function __construct(Category $pc = NULL)
	{
		$this->category = $pc;
	}

	/**
	 * Add a child to this node
	 *
	 * @param CategoryTreeNode $node Node to add
	 *
	 * @return void
	 */
	public function addChild(CategoryTreeNode $node)
	{
		$this->children[$node->category->category_srl] = $node;
	}

	/**
	 * Removes a node from tree
	 *
	 * @param CategoryTreeNode $node Node to remove
	 *
	 * @return void
	 */
	public function removeChild(CategoryTreeNode $node)
	{
		unset($this->children[$node->category->category_srl]);
	}

	/**
	 *  Converts tree to flat structure easily iterable in template files
	 *
	 * @param int $depth Default 0
	 * @param int $index Default 0
	 *
	 * @return CategoryNode[]
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

/* End of file Category.php */
/* Location: ./modules/shop/libs/Category.php */
