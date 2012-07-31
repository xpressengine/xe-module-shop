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
	protected function setUp()
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
	 * Test deleting a Product category by id with valid data (srl is provided)
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testDeleteProductCategoryBySrl_ValidData()
	{
		// Insert two product category in the database, so that we will have what to delete
		Database::executeNonQuery("
			INSERT INTO xe_product_categories (product_category_srl, module_srl, title)
				VALUES(1000, 1001, 'Dummy category 1000')
			");
		Database::executeNonQuery("
			INSERT INTO xe_product_categories (product_category_srl, module_srl, title)
				VALUES(1002, 1001, 'Dummy category 1002')
			");

		// Delete Product category 1000
		$shopModel = &getModel('shop');
		$args = new stdClass();
		$args->product_category_srl = 1000;
		try
		{
			$output = $shopModel->deleteProductCategory($args);

			// Check that deleting was successful
			$this->assertTrue($output);

			// Check that the record is no longer in the database
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_product_categories WHERE product_category_srl = 1000");
			$this->assertEquals(0, $count[0]->count);

			// Check that the other record was not also deleted by mistake
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_product_categories WHERE product_category_srl = 1002");
			$this->assertEquals(1, $count[0]->count);

			// Revert changes: delete the two product categories added previously
			Database::executeNonQuery("DELETE FROM xe_product_categories WHERE product_category_srl IN (1000, 1002)");
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Test deleting more Product categories by module_srl with valid data (srl is provided)
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testDeleteProductCategoryByModuleSrl_ValidData()
	{
		// Insert two product category in the database, so that we will have what to delete
		Database::executeNonQuery("
			INSERT INTO xe_product_categories (product_category_srl, module_srl, title)
				VALUES(1000, 1001, 'Dummy category 1000')
			");
		Database::executeNonQuery("
			INSERT INTO xe_product_categories (product_category_srl, module_srl, title)
				VALUES(1002, 1001, 'Dummy category 1002')
			");
		Database::executeNonQuery("
			INSERT INTO xe_product_categories (product_category_srl, module_srl, title)
				VALUES(1004, 1003, 'Dummy category 1002')
			");

		// Delete Product category 1000
		$shopModel = &getModel('shop');
		$args = new stdClass();
		$args->module_srl = 1001;
		try
		{
			$output = $shopModel->deleteProductCategory($args);

			// Check that deleting was successful
			$this->assertTrue($output);

			// Check that the record is no longer in the database
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_product_categories WHERE module_srl = 1001");
			$this->assertEquals(0, $count[0]->count);

			// Check that the other record was not also deleted by mistake
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_product_categories WHERE module_srl = 1003");
			$this->assertEquals(1, $count[0]->count);

			// Revert changes: delete the two product categories added previously
			Database::executeNonQuery("DELETE FROM xe_product_categories WHERE product_category_srl IN (1000, 1002, 1004)");
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
