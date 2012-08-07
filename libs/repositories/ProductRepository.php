<?php

require_once dirname(__FILE__) . '/../model/Product.php';
require_once dirname(__FILE__) . '/BaseRepository.php';

/**
 * Handles database operations for Product
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class ProductRepository extends BaseRepository
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
		} else {
            $this->insertProductCategories($product);
        }
		return $product->product_srl;
	}

    /**
     * Insert product categories
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $product Product
     * @return boolean
     */
    public function insertProductCategories(Product $product)
    {
        $args->product_srl = $product->product_srl;
        foreach($product->categories as $category){
            $args->category_srl = $category;
            $output = executeQuery('shop.insertProductCategories',$args);
            if(!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        }
        return TRUE;
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
        $args->product_srls[] = $args->product_srl;
        $output = executeQuery('shop.deleteProductCategories', $args);
        if(!$output->toBool())
        {
            throw new Exception($output->getMessage(), $output->getError());
        }
		return TRUE;
	}

    /**
     * Deletes more products by $product_srls
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $args array
     */
    public function deleteProducts($args)
    {
        if(!isset($args->product_srls))
            throw new Exception("Missing arguments for Products delete: please provide [product_srls]");

        $output = executeQuery('shop.deleteProducts', $args);
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
     * Delete product categories
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $product Product
     * @return boolean
     */
    public function deleteProductCategories(Product &$product)
    {
        $args->product_srls[] = $product->product_srl;
        $output = executeQuery('shop.deleteProductCategories',$args);
        if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
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
		$args->product_srl = $product_srl;

		$output = executeQuery('shop.getProduct', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		$product = new Product($output->data);
        $this->getProductCategories($product);
		return $product;
	}

    /**
     * Retrieve product categories
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $product Product
     * @return boolean
     */
    public function getProductCategories(Product &$product)
    {
        $args->product_srl = $product->product_srl;
        $output = executeQuery('shop.getProductCategories',$args);
        if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        if(!is_array($output->data)){
            $product->categories[] = $output->data->category_srl;
        }else{
            foreach($output->data as $item){
                $product->categories[] = $item->category_srl;
            }
        }
        return TRUE;
    }

    /**
     * Retrieve a Product List object from the database given a modul_srl
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $module_srl int
     * @return Product List
     */
    public function getProductList($module_srl){
        $args->page = Context::get('page');
        if(!$args->page) $args->page = 1;
        Context::set('page',$args->page);

        $args->module_srl = $module_srl;
        if(!isset($args->module_srl))
            throw new Exception("Missing arguments for get product list : please provide [module_srl]");

        $output = executeQuery('shop.getProductList', $args);
        foreach ($output->data as $product){
            $product_object = new Product($product);
            $products[] = $product_object;
        }
        $output->products = $products;
        return $output;
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
		} else {
            $this->updateProductCategories($product);
        }
		return TRUE;
	}

    /**
     * Update product categories
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $product Product
     * @return boolean
     */
    public function updateProductCategories(Product &$product)
    {
        $this->deleteProductCategories($product);
        $this->insertProductCategories($product);
        return TRUE;
    }
}
