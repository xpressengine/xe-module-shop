<?php

require dirname(__FILE__).'/Bootstrap.php';
require dirname(__FILE__).'/TestAgainstDatabase.php';
require dirname(__FILE__).'/Database.php';

class ProductCategoryTest extends TestAgainstDatabase
{
	public function setUp()
	{
		parent::setUp();
	}

	public function testInsertProductCategory_ValidData()
	{
		$args = new stdClass();
		$args->module_srl = 2;
		$args->title = "Product category 1";

		$shopModel = getModel('shop');
		try
		{
			$product_category_srl = $shopModel->insertProductCategory($args);
			$this->assertNotNull($product_category_srl);

			$output = Database::executeQuery("SELECT * FROM xe_product_categories WHERE product_category_srl = $product_category_srl");
			$this->assertEquals(1, count($output));

			$product_category = $output[0];
			$this->assertEquals($args->module_srl, $product_category->module_srl);
			$this->assertEquals($args->title, $product_category->title);

			// Delete product we just added after test is finished
			Database::executeNonQuery("DELETE FROM xe_product_categories WHERE product_category_srl = $product_category_srl");
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	public function tearDown()
	{
		parent::tearDown();
	}

}