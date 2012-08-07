<?php

require_once dirname(__FILE__) . '/BaseRepository.php';
require_once dirname(__FILE__) . '/../model/ProductCategory.php';

/**
 * Handles database operations for Product Category
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class ProductCategoryRepository extends BaseRepository
{
	/**
	 * Insert a new Product category; returns the ID of the newly created record
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $product_category ProductCategory
	 * @return int
	 */
	public function insertProductCategory(ProductCategory $product_category)
	{
		$product_category->product_category_srl = getNextSequence();
		$output = executeQuery('shop.insertProductCategory', $product_category);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		return $product_category->product_category_srl;
	}

	/**
	 * Deletes a product category by $product_category_srl or $module_srl
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $args array
	 */
	public function deleteProductCategory($args)
	{
		if(!isset($args->product_category_srl) && !isset($args->module_srl))
			throw new Exception("Missing arguments for Product category delete: please provide [product_category_srl] or [module_srl]");

		$output = executeQuery('shop.deleteProductCategory', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

        $output = executeQuery('shop.deleteAttributesScope', $args);
        if(!$output->toBool())
        {
            throw new Exception($output->getMessage(), $output->getError());
        }

		return TRUE;
	}

	/**
	 * Retrieve a ProductCategory object from the database given a srl
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $product_category_srl int
	 * @return ProductCategory
	 */
	public function getProductCategory($product_category_srl)
	{
		$args = new stdClass();
		$args->product_category_srl = $product_category_srl;

		$output = executeQuery('shop.getProductCategory', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		$product_category = new ProductCategory($output->data);

		return $product_category;
	}

	/**
	 * Update a product category
	 *
	 * @author   Corina Udrescu (dev@xpressengine.org)
	 * @param $product_category ProductCategory
	 * @throws Exception
	 * @return boolean
	 */
	public function updateProductCategory(ProductCategory $product_category)
	{
		$output = executeQuery('shop.updateProductCategory', $product_category);
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
	public function getProductCategoriesTree($module_srl)
	{
		$args = new stdClass();
		$args->module_srl = $module_srl;

		// Retrieve categories from database
		$output = executeQueryArray('shop.getProductCategories', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		// Arrange hierarchically
		$nodes = array();
		$nodes[0] = new ProductCategoryTreeNode();
		foreach($output->data as $pc)
		{
			$nodes[$pc->product_category_srl] = new ProductCategoryTreeNode(new ProductCategory($pc));
			$nodes[$pc->parent_srl]->addChild($nodes[$pc->product_category_srl]);
		}

		return $nodes[0];
	}

	/**
	 * Save category image to disc
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function saveProductCategoryImage($module_srl, $original_filename, $tmp_name)
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
	public function deleteProductCategoryImage($filename)
	{
		FileHandler::removeFile($filename);

	}

}
