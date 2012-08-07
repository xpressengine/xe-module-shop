<?php

require_once dirname(__FILE__) . '/BaseRepository.php';
require_once dirname(__FILE__) . '/../model/Category.php';

/**
 * Handles database operations for Product Category
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class CategoryRepository extends BaseRepository
{
	/**
	 * Insert a new Product category; returns the ID of the newly created record
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $category Category
	 * @return int
	 */
	public function insertCategory(Category $category)
	{
		$category->category_srl = getNextSequence();
		$output = executeQuery('shop.insertCategory', $category);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		return $category->category_srl;
	}

	/**
	 * Deletes a product category by $category_srl or $module_srl
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $args array
	 */
	public function deleteCategory($args)
	{
		if(!isset($args->category_srl) && !isset($args->module_srl))
			throw new Exception("Missing arguments for Product category delete: please provide [category_srl] or [module_srl]");

		$output = executeQuery('shop.deleteCategory', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

        $output = executeQuery('shop.deleteAttributesScope', $args);
        if(!$output->toBool())
        {
            throw new Exception($output->getMessage(), $output->getError());
        }

        $output = executeQuery('shop.deleteProductCategories', $args);
        if(!$output->toBool())
        {
            throw new Exception($output->getMessage(), $output->getError());
        }

		return TRUE;
	}

	/**
	 * Retrieve a Category object from the database given a srl
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $category_srl int
	 * @return Category
	 */
	public function getCategory($category_srl)
	{
		$args = new stdClass();
		$args->category_srl = $category_srl;

		$output = executeQuery('shop.getCategory', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		$category = new Category($output->data);

		return $category;
	}

	/**
	 * Update a product category
	 *
	 * @author   Corina Udrescu (dev@xpressengine.org)
	 * @param $category Category
	 * @throws Exception
	 * @return boolean
	 */
	public function updateCategory(Category $category)
	{
		$output = executeQuery('shop.updateCategory', $category);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		return TRUE;
	}

	/**
	 * Get all product categories for a module as a tree
	 * Returns root node
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $module_srl int
	 */
	public function getCategoriesTree($module_srl)
	{
		$args = new stdClass();
		$args->module_srl = $module_srl;

		// Retrieve categories from database
		$output = executeQueryArray('shop.getCategories', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		// Arrange hierarchically
		$nodes = array();
		$nodes[0] = new CategoryTreeNode();
		foreach($output->data as $pc)
		{
			$nodes[$pc->category_srl] = new CategoryTreeNode(new Category($pc));
			$nodes[$pc->parent_srl]->addChild($nodes[$pc->category_srl]);
		}

		return $nodes[0];
	}

	/**
	 * Save category image to disc
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function saveCategoryImage($module_srl, $original_filename, $tmp_name)
	{
		$tmp_arr = explode('.', $original_filename);
		$extension = $tmp_arr[count($tmp_arr) - 1];

		$path = sprintf('./files/attach/shop/%d/product-categories/', $module_srl);
		$filename = sprintf('%s%s.%s', $path, uniqid('product-category-'), $extension);
		FileHandler::copyFile($tmp_name, $filename);

		return $filename;
	}

	/**
	 * Delete category image from disc
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function deleteCategoryImage($filename)
	{
		FileHandler::removeFile($filename);

	}

}
