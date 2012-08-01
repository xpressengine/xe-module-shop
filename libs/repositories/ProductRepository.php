<?php

require_once dirname(__FILE__) . '/../model/Product.class.php';

/**
 * Handles database operations for Product
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class ProductRepository
{
	/**
	 * Insert a new Product  returns the ID of the newly created record
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product Product
	 * @return int
	 */
	public function insertProduct(Product $product)
	{
		$product->product_srl = getNextSequence();
		$output = executeQuery('shop.insertProduct', $product);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		return $product->product_srl;
	}

	/**
	 * Deletes a product by $product_srl or $module_srl
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $args array
	 */
	public function deleteProduct($args)
	{
		if(!isset($args->product_srl) && !isset($args->module_srl))
			throw new Exception("Missing arguments for Product delete: please provide [product_srl] or [module_srl]");

		$output = executeQuery('shop.deleteProduct', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		return TRUE;
	}

	/**
	 * Retrieve a Product object from the database given a srl
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product_srl int
	 * @return Product
	 */
	public function getProduct($product_srl)
	{
		$args = new stdClass();
		$args->product_category_srl = $product_srl;

		$output = executeQuery('shop.getProduct', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		$product = new Product();
		$product->product_srl = $output->data->product_srl;
        $product->member_srl = $output->data->member_srl
		$product->module_srl = $output->data->module_srl;
		$product->parent_product_srl = $output->data->parent_product_srl;
		$product->product_type = $output->data->product_type;
		$product->title = $output->data->title;
		$product->description = $output->data->description;
		$product->short_description = $output->data->short_description;
		$product->sku = $output->data->sku;
		$product->weight = $output->data->weight;
		$product->friendly_url = $output->data->friendly_url;
        $product->price = $output->data->price;
        $product->qty = $output->data->qty;
        $product->in_stock = $output->data->in_stock;
		$product->regdate = $output->data->regdate;
        $product->last_updated = $output->data->last_updated;
        $product->related_products = $output->data->related_products;

		return $product;
	}

	/**
	 * Update a product
	 *
	 * @author   Dan Dragan (dev@xpressengine.org)
	 * @param $product Product
	 * @throws Exception
	 * @return boolean
	 */
	public function updateProduct(Product $product)
	{
		$output = executeQuery('shop.updateProduct', $product);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		return TRUE;
	}
}
