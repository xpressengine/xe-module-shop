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

		$product_category = new ProductCategory();
		$product_category->product_category_srl = $output->data->product_category_srl;
		$product_category->module_srl = $output->data->module_srl;
		$product_category->parent_srl = $output->data->parent_srl;
		$product_category->file_srl = $output->data->file_srl;
		$product_category->title = $output->data->title;
		$product_category->description = $output->data->description;
		$product_category->product_count = $output->data->product_count;
		$product_category->friendly_url = $output->data->friendly_url;
		$product_category->include_in_navigation_menu = $output->data->include_in_navigation_menu;
		$product_category->regdate = $output->data->regdate;
		$product_category->last_update = $output->data->last_update;

		return $product_category;
	}
}
