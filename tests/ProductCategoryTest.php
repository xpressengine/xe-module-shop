<?php

require dirname(__FILE__) . '/lib/Bootstrap.php';
require dirname(__FILE__) . '/lib/TestAgainstDatabase.php';

/**
 *  Test features related to Product categories
 *  @author Corina Udrescu (dev@xpressengine.org)
 */
class ProductCategoryTest extends TestAgainstDatabase
{
	/**
	 * Set up environment for testing before each test method
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function setUp()
	{
		parent::setUp();
	}

	/**
	 * Tests inserting a new Product category - makes sure all fields are properly persisted
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testInsertProductCategory_ValidData()
	{
		// Create new Product category object
		$args = new stdClass();
		$args->module_srl = 2;
		$args->title = "Product category 1";

		$shopModel = getModel('shop');
		try
		{
			// Try to insert the new Category
			$product_category_srl = $shopModel->insertProductCategory($args);

			// Check that a srl was returned
			$this->assertNotNull($product_category_srl);

			// Read the newly created object from the database, to compare it with the source object
			$output = Database::executeQuery("SELECT * FROM xe_product_categories WHERE product_category_srl = $product_category_srl");
			$this->assertEquals(1, count($output));

			$product_category = $output[0];
			$this->assertEquals($args->module_srl, $product_category->module_srl);
			$this->assertEquals($args->title, $product_category->title);
			$this->assertNotNull($product_category->regdate);
			$this->assertNotNull($product_category->last_update);

			// Delete product we just added after test is finished
			Database::executeNonQuery("DELETE FROM xe_product_categories WHERE product_category_srl = $product_category_srl");
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Clean-up testing environment after every test method
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function tearDown()
	{
		parent::tearDown();
	}

}

/* End of file ProductCategoryTest.php */
/* Location: ./modules/shop/tests/ProductCategoryTest.php */
