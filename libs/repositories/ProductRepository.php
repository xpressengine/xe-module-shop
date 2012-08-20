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
			$this->insertProductImages($product);
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
	 * Insert product images
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function insertProductImages(Product $product)
	{
		$shopModel = getModel('shop');
		$imageRepository = $shopModel->getImageRepository();
		foreach($product->images as $image){
			$image->product_srl = $product->product_srl;
			$image->module_srl = $product->module_srl;
			$image->member_srl = $product->member_srl;
			$imageRepository->insertImage($image);
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
		foreach($product->configurable_attributes as $config_attribute_srl => $config_attribute_title){
			$args->attribute_srl = $config_attribute_srl;
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

		// If this is an associated product, also add parent's configurable attributes as valid
		if($product->parent_product_srl)
		{
			/**
			 * @var ConfigurableProduct $parent_product
			 */
			$parent_product = $this->getProduct($product->parent_product_srl);
			$attributes_list = array_merge($attributes_list, array_keys($parent_product->configurable_attributes));
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
		$product = new SimpleProduct();
		$product->product_srl = $args->product_srl;
		$product->module_srl = $args->module_srl;
		$this->deleteProductCategories($product);
		$this->deleteProductAttributes($product);
		$this->deleteProductImages($product);

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
		$output = executeQuery('shop.deleteProductImages', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		foreach($args->product_srls as $product_srl){
			$path = sprintf('./files/attach/images/shop/%d/product-images/%d/', $args->module_srl,$product_srl);
			FileHandler::removeDir($path);
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
		$args = new stdClass();
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
		$args->product_srls = array($product->product_srl);
		$output = executeQuery('shop.deleteProductAttributes', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		return TRUE;
	}

	/**
	 * Delete product images
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function deleteProductImages(Product &$product)
	{
		if(!$product->product_srl)
		{
			throw new Exception("Invalid arguments! Please provide product_srl for delete atrributes.");
		}

		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$output = executeQuery('shop.deleteProductImages', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		$path = sprintf('./files/attach/images/shop/%d/product-images/%d/', $product->module_srl,$product->product_srl);
		FileHandler::removeDir($path);
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

		// If product does not exist, return null
		if(!$output->data)
		{
			return NULL;
		}

		if($output->data->product_type == 'simple')
		{
			$product = new SimpleProduct($output->data);
		}
		else
		{
			$product = new ConfigurableProduct($output->data);

			// Get associated products
			$associated_products_args = new stdClass();
			$associated_products_args->configurable_product_srls = array($product->product_srl);

			$associated_products_output = executeQueryArray('shop.getAssociatedProducts', $associated_products_args);
			if(!$associated_products_output->toBool())
			{
				throw new Exception($associated_products_output->getMessage());
			}

			$associated_products = $associated_products_output->data;
			foreach($associated_products as $associated_product)
			{
				$product_object = new SimpleProduct($associated_product);
				$this->getProductAttributes($product_object);
				$product->associated_products[] = $product_object;
			}
		}
        $this->getProductCategories($product);
		$this->getProductAttributes($product);
		$this->getProductImages($product);
		return $product;
	}

    /**
     * Retrieve a Product object from the database given a friendly url string
     *
     * @author Florin Ercus (dev@xpressengine.org)
     *
     * @param $str string
     *
     * @return Product
     */
    public function getProductByFriendlyUrl($str)
    {
        $output = $this->query('getProductByFriendlyUrl', array('friendly_url' => $str));
        return empty($output->data) ? NULL : new SimpleProduct($output->data);
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
		$args = new stdClass();
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
			else $product->configurable_attributes[$attribute->attribute_srl] = $attribute->title;
		}

		return TRUE;
	}

	/**
	 * Retrieve product images
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function getProductImages(Product &$product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$shopModel = getModel('shop');
		$imageRepository = $shopModel->getImageRepository();
		$output = executeQueryArray('shop.getProductImages', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}

		foreach($output->data as $image)
		{
			$oImage = new Image($image);
			$product->images[] = $oImage;
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
	public function createProductFromParent(ConfigurableProduct $parent_product, array $values)
	{
		$product = new SimpleProduct();
		$product->member_srl = $parent_product->member_srl;
		$product->module_srl = $parent_product->module_srl;
		$product->parent_product_srl = $parent_product->product_srl;
		$product->product_type = 'simple';
		$product->title = $parent_product->title.'_'.implode('_',$values);
		$product->sku = $parent_product->sku.'_'.implode('_',$values);
		$product->price = $parent_product->price;
		$product->categories = $parent_product->categories;
		$configurable_attributes_srls = array_keys($parent_product->configurable_attributes);
		for($i=0;$i<count($values);$i++){
			$product->attributes[$configurable_attributes_srls[$i]] = $values[$i];
		}

		return $product;
	}

	/**
	 * Retrieve a Product List object from the database given a module_srl
	 * @author Dan Dragan (dev@xpressengine.org)
	 *
	 * @param srdClass $args Must have: module_srl; Can have: page, category_srl
	 *
	 * @throws Exception
	 * @return stdClass $output
	 */
    public function getProductList($args, $loadAttributes = FALSE){
        if(!isset($args->module_srl))
            throw new Exception("Missing arguments for get product list : please provide [module_srl]");

		if(!$args->page) $args->page = 1;

		if($args->category_srls & count($args->category_srls) > 0)
		{
			$output = executeQueryArray('shop.getProductListByCategory', $args);
		}
		else
		{
        	$output = executeQueryArray('shop.getProductList', $args);
		}

		if(!$output->toBool())
		{
			throw new Exception($output->getMessage());
		}

		// Get top level products
		$configurable_products = array();
        foreach ($output->data as $product){
			if($product->product_type == 'simple')
			{
				$product_object = new SimpleProduct($product);
				if($loadAttributes)
				{
					$this->getProductAttributes($product_object);
				}
			}
			else
			{
				$product_object = new ConfigurableProduct($product);
				if($loadAttributes)
				{
					$this->getProductAttributes($product_object);
				}
				$configurable_products[] = $product->product_srl;
			}
            $products[$product->product_srl] = $product_object;
        }


		if(count($configurable_products) > 0)
		{
			// Get associated products and link to their parents
			$associated_products_args = new stdClass();
			$associated_products_args->module_srl = $args->module_srl;
			$associated_products_args->configurable_product_srls = $configurable_products;

			$associated_products_output = executeQueryArray('shop.getAssociatedProducts', $associated_products_args);
			if(!$associated_products_output->toBool())
			{
				throw new Exception($associated_products_output->getMessage());
			}

			$associated_products = $associated_products_output->data;
			foreach($associated_products as $associated_product)
			{
				$product_object = new SimpleProduct($associated_product);
				if($loadAttributes)
				{
					$this->getProductAttributes($product_object);
				}
				$products[$associated_product->parent_product_srl]->associated_products[] = $product_object;
			}
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
			$this->updateProductImages($product);
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

	/**
	 * Update product images
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function updateProductImages(Product &$product)
	{
		$args = new stdClass();
		$args->image_srls = $product->delete_images;
		$shopModel = getModel('shop');
		$imageRepository = $shopModel->getImageRepository();
		if(isset($product->primary_image)) $this->updatePrimaryImage($product);
		if(isset($args->image_srls)){
			$delete_images = $imageRepository->getImages($args->image_srls);
			foreach($delete_images as $delete_image){
				$path = sprintf('./files/attach/images/shop/%d/product-images/%d/%s', $product->module_srl,$product->product_srl,$delete_image->filename);
				FileHandler::removeFile($path);
			}
			$output = executeQuery('shop.deleteProductImages', $args);
			if(!$output->toBool())
			{
				throw new Exception($output->getMessage(), $output->getError());
			}
		}
		$this->insertProductImages($product);
		return TRUE;
	}

	/**
	 * Set primary image
	 *
	 * @author Dan Dragan (dev@xpressengine.org)
	 * @param $product Product
	 * @return boolean
	 */
	public function updatePrimaryImage($product)
	{
		$args = new stdClass();
		$args->product_srl = $product->product_srl;
		$args->is_primary = "N";
		$output = executeQuery('shop.updatePrimaryImage', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
		$args->primary_image = $product->primary_image;
		$args->is_primary = "Y";
		$output = executeQuery('shop.updatePrimaryImage', $args);
		if(!$output->toBool())
		{
			throw new Exception($output->getMessage(), $output->getError());
		}
	}
}
