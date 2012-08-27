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
 * Defines options for generating the HTML for a category tree
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class HtmlCategoryTreeConfig
{
	/**
	 * @var bool Show or hide number of products in category
	 */
	public $showProductCount = TRUE;
	/**
	 * @var bool Show or hide add/edit/delete links next to category name; used for backend;
	 */
	public $showManagingLinks = FALSE;
	/**
	 * @var array When given, selected categories get added a class - active. Also, when $showCheckbox is true, this is used to send selected (checked) values
	 */
	public $selected = array();
	/**
	 * @var bool Show or hide checkboxes next to each category name; Useful for adding things to a certain category
	 */
	public $showCheckbox = FALSE;
	/**
	 * @var string When $showCheckbox is true, this is used as the input name of the checkboxes
	 */
	public $checkboxesName = 'categories';
	/**
	 * @var bool Show or hide category link; Turns category name into an anchor
	 */
	public $linkCategoryName = FALSE;
	/**
	 * @var array When $linkCategoryName is true, this is used to create the URL to link to; Represents parameters for getUrl function
	 */
	public $linkGetUrlParams = array();
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
	 * Converts tree to flat structure easily iterable in template files
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

	/**
	 * Returns an HTML representation of the tree
	 *
	 * @param HtmlCategoryTreeConfig $config options for generating the tree
	 *
	 * @return string
	 */
	public function toHTML(HtmlCategoryTreeConfig $config)
	{
		$flat_tree = $this->toFlatStructure();

		if(count($flat_tree) == 0)
		{
			global $lang;
			$html = '<ul><li>' . $lang->no_categories . '</li></ul>';
			return $html;
		}


		$html = '';
		if($config->showCheckbox)
		{
			$html .= '<ul class="multiple_checkbox" id="' . $config->checkboxesName . '">';
		}
		else
		{
			$html .= '<ul>';
		}

		$previous_depth = NULL;
		foreach($flat_tree as $node)
		{
			if($previous_depth === $node->depth)
			{
				$html .= '</li>';
			}
			if($previous_depth < $node->depth)
			{
				$html .= '<ul>';
			}
			if($previous_depth > $node->depth)
			{
				for($i = $node->depth; $i < $previous_depth; $i++)
				{
					$html .= '</li></ul>';
				}
				$html .= '</li>';
			}

			$class = '';
			if(in_array($node->category->category_srl, $config->selected))
			{
				$class = "class='active'";
			}
			$html .= '<li id="tree_' . $node->category->category_srl . '" ' . $class . '>';

			$nodeContent = '<span>';

			if($config->showCheckbox)
			{
				$nodeContent .= '<input type="checkbox" ';
				if(in_array($node->category->category_srl, $config->selected))
				{
					$nodeContent .= ' checked="checked" ';
				}
				$nodeContent .= ' name="' . $config->checkboxesName . '[]" ';
				$nodeContent .= ' value="' . $node->category->category_srl . '" ';
				$nodeContent .= '/>';
			}

			$nodeTitle = $node->category->title;
			if($config->linkCategoryName)
			{
				$config->linkGetUrlParams[] = 'category_srl';
				$config->linkGetUrlParams[] = $node->category->category_srl;
				$nodeTitle = '<a href="' . call_user_func_array('getUrl', $config->linkGetUrlParams) . '">' . $nodeTitle . '</a>';
			}
			$nodeContent .= $nodeTitle;

			if($config->showProductCount)
			{
				$nodeContent .= ' (' . $node->category->product_count . ')';
			}
			$nodeContent .= '</span>';

			if($config->showManagingLinks)
			{
				$nodeContent .= '<a href="#" class="add"><img src="./common/js/plugins/ui.tree/images/iconAdd.gif"></a>';
				$nodeContent .= '<a href="#" class="modify"><img src="./common/js/plugins/ui.tree/images/iconModify.gif"></a>';
				if(count($node->children) == 0)
				{
					$nodeContent .= '<a href="#" class="delete"><img src="./common/js/plugins/ui.tree/images/iconDel.gif"></a>';
				}
			}
			$html .= $nodeContent;

			$previous_depth = $node->depth;
		}
		if($previous_depth > 0)
		{
			for($i = 0; $i < $previous_depth; $i++)
			{
				$html .= '</li></ul>';
			}
		}

		$html .= '</li></ul>';

		return $html;
	}

}

/* End of file Category.php */
/* Location: ./modules/shop/libs/Category.php */
