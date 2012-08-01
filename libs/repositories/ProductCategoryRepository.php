<?php

require_once dirname(__FILE__) . '/../model/ProductCategory.class.php';

/**
 * Handles database operations for Product Category
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class ProductCategoryRepository
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

		return TRUE;
	}
}
