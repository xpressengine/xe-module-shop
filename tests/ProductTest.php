<?php

require_once "lib/Shop_Generic_Tests.class.php";
require dirname(__FILE__) . '/lib/Bootstrap.php';

require_once dirname(__FILE__) . '/../libs/repositories/ProductRepository.php';

/**
 *  Test features related to Products
 *  @author Corina Udrescu (dev@xpressengine.org)
 */
class ProductTest extends Shop_Generic_Tests_DatabaseTestCase
{
	const PRODUCT = 1,
					CATEGORY_BOOKS = 265,
					CATEGORY_PHOTOGRAPHY = 266,
					CATEGORY_ARCHITECTURE = 267,
					CATEGORY_PROGRAMMING = 268,
					CATEGORY_TSHIRTS = 276,
					ATTRIBUTE_URL = 253,
					ATTRIBUTE_COLOR = 278,
					ATTRIBUTE_SIZE = 274,
					ATTRIBUTE_PUBLISH_YEAR = 273,
					ATTRIBUTE_AUTHOR = 279
					;

	/**
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	public function getDataSet()
	{
		return new Shop_DbUnit_ArrayDataSet(array(
			'xe_shop_products' => array(
				array(
					'product_srl' => self::PRODUCT,
					'member_srl' => 4,
					'module_srl' => 10,
					'parent_product_srl' => 0,
					'product_type' => 'simple',
					'title' => 'carte',
					'description' => 'povestiri',
					'short_description' => 'povestiri',
					'sku' => 'carte',
					'weight' => 100,
					'status' => 'enabled',
					'friendly_url' => 'carte',
					'price' => 200,
					'qty' => 10,
					'in_stock' => 'Y',
					'related_products' => NULL,
					'regdate' => date("YmdGis"),
					'last_update' => date("YmdGis")
				)
			),
			'xe_shop_categories' => array(
				array('category_srl' => self::CATEGORY_BOOKS, 'module_srl' => 104, 'parent_srl' => 0,   'filename' => NULL, 'title' => 'Carti', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185846, 'last_update' => 20120807185846),
				array('category_srl' => self::CATEGORY_PHOTOGRAPHY, 'module_srl' => 104, 'parent_srl' => self::CATEGORY_BOOKS, 'filename' => NULL, 'title' => 'Fotografie', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185857, 'last_update' => 20120807185857),
				array('category_srl' => self::CATEGORY_ARCHITECTURE, 'module_srl' => 104, 'parent_srl' => self::CATEGORY_BOOKS, 'filename' => NULL, 'title' => 'Arhitectura', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185902, 'last_update' => 20120807185902),
				array('category_srl' => self::CATEGORY_PROGRAMMING, 'module_srl' => 104, 'parent_srl' => self::CATEGORY_BOOKS, 'filename' => NULL, 'title' => 'Programare', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185908, 'last_update' => 20120807185908),
				array('category_srl' => self::CATEGORY_TSHIRTS, 'module_srl' => 104, 'parent_srl' => 0, 'filename' => NULL, 'title' => 'Tricouri', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807190353, 'last_update' => 20120807190353)
			),
			'xe_shop_product_categories' => array(
				array('product_srl' => self::PRODUCT, 'category_srl' => self::CATEGORY_BOOKS),
				array('product_srl' => self::PRODUCT, 'category_srl' => self::CATEGORY_PHOTOGRAPHY),
			),
			'xe_shop_attributes'  => array(
				array('attribute_srl' => self::ATTRIBUTE_URL, 'member_srl' => 4, 'module_srl' => 104, 'title' => 'URL', 'type' => 1, 'required' => 'Y', 'status' => 'Y', 'default_value' => NULL, 'values' => NULL, 'regdate' => 20120807160414, 'last_update' => 20120807160414),
				array('attribute_srl' => self::ATTRIBUTE_COLOR, 'member_srl' => 4, 'module_srl' => 104, 'title' => 'Culoare', 'type' => 1, 'required' => 'Y', 'status' => 'Y', 'default_value' => NULL, 'values' => NULL, 'regdate' => 20120808110402, 'last_update' => 20120808110402),
				array('attribute_srl' => self::ATTRIBUTE_SIZE, 'member_srl' => 4, 'module_srl' => 104, 'title' => 'Marime', 'type' => 5, 'required' => 'Y', 'status' => 'Y', 'default_value' => 'M', 'value' => 'S|M|L', 'regdate' => 20120807190419, 'last_update' => 20120807190419),
				array('attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'member_srl' => 4, 'module_srl' => 104, 'title' => 'Anul aparitiei', 'type' => 1, 'required' => 'N', 'status' => 'Y', 'default_value' => NULL, 'values' => NULL, 'regdate' => 20120807190150, 'last_update' => 20120807190150),
				array('attribute_srl' => self::ATTRIBUTE_AUTHOR, 'member_srl' => 4, 'module_srl' => 104, 'title' => 'Autor', 'type' => 1, 'required' => 'Y', 'status' => 'Y', 'default_value' => NULL, 'values' => NULL, 'regdate' => 20120808110540, 'last_update' => 20120808110540)
			),
			'xe_shop_attributes_scope' => array(
				array('attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'category_srl' => self::CATEGORY_BOOKS),
				array('attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'category_srl' => self::CATEGORY_PHOTOGRAPHY),
				array('attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'category_srl' => self::CATEGORY_ARCHITECTURE),
				array('attribute_srl' => self::ATTRIBUTE_PUBLISH_YEAR, 'category_srl' => self::CATEGORY_PROGRAMMING),
				array('attribute_srl' => self::ATTRIBUTE_AUTHOR, 'category_srl' => self::CATEGORY_PHOTOGRAPHY),
				array('attribute_srl' => self::ATTRIBUTE_COLOR, 'category_srl' => self::CATEGORY_TSHIRTS),
				array('attribute_srl' => self::ATTRIBUTE_SIZE, 'category_srl' => self::CATEGORY_TSHIRTS)
			),
			'xe_shop_product_attributes' => array(
			)
		));
	}

	/**
	 * Test inserting a product attribute
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testInsertProductAttribute()
	{
		$repository = new ProductRepository();
		$product = $repository->getProduct(1);

		// Update a global attribute, just to check that update works
		// Global = applies to all categories
		$product->attributes[self::ATTRIBUTE_URL] = "some-slug";
		$repository->updateProduct($product);

		$new_product = $repository->getProduct(1);

		$this->assertEquals(1, count($new_product->attributes));
		$this->assertEquals("some-slug", $new_product->attributes[self::ATTRIBUTE_URL]);
	}

	/**
	 * Test inserting product attributes
	 *
	 * Each category has associated a set of attributes
	 * (e.g. All books have the Author attribute, All T-Shirts have Color)
	 * Based on the category a product is in, it can have values for these attributes.
	 *
	 * This test checks that if Color is provided for a product in the Books
	 * category it will not be added.
	 *
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testInsertProductAttributeSkipsAttributesNotInScope()
	{
		$repository = new ProductRepository();
		$product = $repository->getProduct(1);

		$product->attributes[self::ATTRIBUTE_AUTHOR] = "J. K. Rowling";
		$product->attributes[self::ATTRIBUTE_PUBLISH_YEAR] = 2003;
		$product->attributes[self::ATTRIBUTE_COLOR] = "Blue";

		$repository->updateProduct($product);

		$new_product = $repository->getProduct(1);

		$this->assertEquals(2, count($new_product->attributes));

		$this->assertFalse(array_key_exists(self::ATTRIBUTE_COLOR, $new_product->attributes));
		$this->assertEquals("J. K. Rowling", $new_product->attributes[self::ATTRIBUTE_AUTHOR]);
		$this->assertEquals(2003, $new_product->attributes[self::ATTRIBUTE_PUBLISH_YEAR]);
	}


}