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
	/**
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	public function getDataSet()
	{
		return new Shop_DbUnit_ArrayDataSet(array(
			'xe_shop_products' => array(
				array(
					'product_srl' => 1,
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
				array('category_srl' => 265, 'module_srl' => 104, 'parent_srl' => 0,   'filename' => NULL, 'title' => 'Carti', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185846, 'last_update' => 20120807185846),
				array('category_srl' => 266, 'module_srl' => 104, 'parent_srl' => 265, 'filename' => NULL, 'title' => 'Fotografie', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185857, 'last_update' => 20120807185857),
				array('category_srl' => 267, 'module_srl' => 104, 'parent_srl' => 265, 'filename' => NULL, 'title' => 'Arhitectura', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185902, 'last_update' => 20120807185902),
				array('category_srl' => 268, 'module_srl' => 104, 'parent_srl' => 265, 'filename' => NULL, 'title' => 'Programare', 'description' => NULL, 'product_count' => 0, 'friendly_url' => NULL, 'include_in_navigation_menu' => 'Y', 'regdate' => 20120807185908, 'last_update' => 20120807185908)
			),
			'xe_shop_attributes' => array(
				array('attribute_srl'=>253,'member_srl'=>4,'module_srl'=>104,'title'=>'URL','type'=>1,'required'=>'Y','status'=>'Y','default_value'=>null,'values'=>null,'regdate'=>20120807160414,'last_update'=>20120807160414),
				array('attribute_srl'=>278,'member_srl'=>4,'module_srl'=>104,'title'=>'Culoare','type'=>1,'required'=>'Y','status'=>'Y','default_value'=>null,'values'=>null,'regdate'=>20120808110402,'last_update'=>20120808110402),
				array('attribute_srl'=>274,'member_srl'=>4,'module_srl'=>104,'title'=>'Marime','type'=>5,'required'=>'Y','status'=>'Y','default_value'=>'M','values'=>'S|M|L','regdate'=>20120807190419,'last_update'=>20120807190419),
				array('attribute_srl'=>273,'member_srl'=>4,'module_srl'=>104,'title'=>'Anul aparitiei','type'=>1,'required'=>'N','status'=>'Y','default_value'=>null,'values'=>null,'regdate'=>20120807190150,'last_update'=>20120807190150),
				array('attribute_srl'=>279,'member_srl'=>4,'module_srl'=>104,'title'=>'Autor','type'=>1,'required'=>'Y','status'=>'Y','default_value'=>null,'values'=>null,'regdate'=>20120808110540,'last_update'=>20120808110540)
			),
			'xe_shop_attributes_scope' => array(
				array('attribute_srl' => 273, 'category_srl' => 265),
				array('attribute_srl' => 273, 'category_srl' => 266),
				array('attribute_srl' => 273, 'category_srl' => 267),
				array('attribute_srl' => 273, 'category_srl' => 268),
				array('attribute_srl' => 279, 'category_srl' => 266)
			),
			'xe_shop_product_categories' => array(
				array('product_srl' => 1, 'category_srl' => 265),
				array('product_srl' => 1, 'category_srl' => 267),
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

		$product->attributes[279] = "Some property value 11";
		$product->attributes[273] = "Some property value 13";

		$repository->updateProduct($product);

		$new_product = $repository->getProduct(1);

		$this->assertEquals(2, count($new_product->attributes));

		$this->assertEquals("Some property value 11", $new_product->attributes[279]);
		$this->assertEquals("Some property value 13", $new_product->attributes[273]);
	}


}