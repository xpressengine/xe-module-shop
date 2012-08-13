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
		}
		else
		{
			$this->insertProductCategories($product);
			$this->insertProductAttributes($product);
			if($product->product_type == 'configurable') $this->insertProductConfigurableAttributes($product);
		}
		return $product->product_srl;
	}

	/**
	 * Insert product attributes
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function insertProductAttributes(Product $product)
	{
		$valid_attributes = $this->getProductCategoriesAttributes($product);

		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		foreach($product->attributes as $attribute_srl => $attribute_value)
		{
			if(!in_array($attribute_srl, $valid_attributes)) continue;
			$args->attribute_srl = $attribute_srl;
			$args->attribute_value = $attribute_value;
			$output = executeQuery('shop.insertProductAttribute', $args);
			if(!$output->toBool())
			{
				throw new Exception($output->getMessage(), $output->getError());
			}
		}
		return TRUE;
	}

	/**
	 * Insert product configurable attributes
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function insertProductConfigurableAttributes(Product $product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		foreach($product->configurable_attributes as $config_attribute){
			$args->attribute_srl = $config_attribute;
			$output = executeQuery('shop.insertProductAttribute',$args);
			if(!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
		}
		return TRUE;
	}

	/**
	 * Given a product, returns all attributes the
	 * product can have according to the categories
	 * it belongs to
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $product_srl
	 * @returns array
	 */
	public function getProductCategoriesAttributes(Product $product)
	{
		$args = new stdClass();
		$args->category_srls = $product->categories;

		$output = executeQueryArray('shop.getCategoryAttributes', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		$attributes_list = array();
		foreach($output->data as $attribute)
		{
			$attributes_list[] = $attribute->attribute_srl;
		}

		return $attributes_list;

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
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		foreach($product->categories as $category)
		{
			$args->category_srl = $category;

			// Insert product category
			$output = executeQuery('shop.insertProductCategories', $args);
			if(!$output->toBool())
			{
				throw new Exception($output->getMessage(), $output->getError());
			}

			// Get number of products in category
			$count_output = executeQuery('shop.getProductsInCategoryCount', $args);
			if(!$count_output->toBool())
			{
				throw new Exception($count_output->getMessage(), $count_output->getError());
			}

			// Update product count
			$update_args = new stdClass();
			$update_args->category_srl = $args->category_srl;
			$update_args->product_count = $count_output->data->product_count;
			$output = executeQuery('shop.updateCategory', $update_args);
			if(!$output->toBool())
			{
				throw new Exception($output->getMessage(), $output->getError());
			}
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
		$product = new Product();
		$product->product_srl = $args->product_srl;
		$this->deleteProductCategories($product);
		$this->deleteProductAttributes($product);

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
		$output = executeQuery('shop.deleteProductAttributes', $args);
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
	 * Delete product attributes
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function deleteProductAttributes(Product &$product)
	{
		if(!$product->product_srl)
		{
			throw new Exception("Invalid arguments! Please provide product_srl for delete atrributes.");
		}

		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$output = executeQuery('shop.deleteProductAttributes', $args);
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
		$args->product_srl = $product_srl;

		$output = executeQuery('shop.getProduct', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		$product = new Product($output->data);
        $this->getProductCategories($product);
		$this->getProductAttributes($product);
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
	 * Retrieve product attributes
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function getProductAttributes(Product &$product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$output = executeQueryArray('shop.getProductAttributes', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		foreach($output->data as $attribute)
		{
			if($attribute->value) $product->attributes[$attribute->attribute_srl] = $attribute->value;
			else $product->configurable_attributes[] = $attribute->attribute_srl;
		}

		return TRUE;
	}

	/**
	 * Create product from parent product
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product Product , $combination array
	 * @return $product
	 */
	public function createProductFromParent(Product $parent_product, array $values)
	{
		$product = new Product();
		$product->member_srl = $parent_product->member_srl;
		$product->module_srl = $parent_product->module_srl;
		$product->parent_product_srl = $parent_product->product_srl;
		$product->product_type = 'simple';
		$product->title = $parent_product->title.'_'.implode('_',$values);
		$product->sku = $parent_product->sku.'_'.implode('_',$values);
		$product->price = $parent_product->price;
		$product->categories = $parent_product->categories;
		for($i=0;$i<count($values);$i++){
			$product->attributes[$parent_product->configurable_attributes[$i]] = $values[$i];
		}

		return $product;
	}

	/**
	 * Retrieve a Product List object from the database given a modul_srl
	 * @author Dan Dragan (dev@xpressengine.org)
	 *
	 * @param srdClass $args Must have: module_srl; Can have: page, category_srl
	 *
	 * @throws Exception
	 * @return stdClass $output
	 */
    public function getProductList($args){
        if(!isset($args->module_srl))
            throw new Exception("Missing arguments for get product list : please provide [module_srl]");

		if(!$args->page) $args->page = 1;

		if($args->category_srls & count($args->category_srls) > 0)
		{
			$output = executeQuery('shop.getProductListByCategory', $args);
		}
		else
		{
        	$output = executeQuery('shop.getProductList', $args);
		}
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
			$this->updateProductAttributes($product);
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

	/**
	 * Update product attributes
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function updateProductAttributes(Product &$product)
	{
		$this->deleteProductAttributes($product);
		$this->insertProductAttributes($product);
		return TRUE;
	}
}
