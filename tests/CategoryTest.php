<?php

require_once dirname(__FILE__) . '/lib/Bootstrap.php';
require_once dirname(__FILE__) . "/lib/Shop_Generic_Tests.class.php";

require_once dirname(__FILE__) . '/../libs/model/Category.php';

/**
 *  Test features related to Product categories
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class CategoryTest extends Shop_Generic_Tests_DatabaseTestCase
{

	/**
	 * Returns the test dataset.
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet()
	{
		return new Shop_DbUnit_ArrayDataSet(array(
			'xe_shop_categories' => array(
				array('category_srl' => 1000, 'module_srl' => 1001, 'title' => 'Dummy category 1000', 'parent_srl' => 0, 'filename' => 'files/attach/picture.jpg'),
				array('category_srl' => 1002, 'module_srl' => 1001, 'title' => 'Dummy category 1002', 'parent_srl' => 1000),
				array('category_srl' => 1004, 'module_srl' => 1003, 'title' => 'Dummy category 1004', 'parent_srl' => 0),
				array('category_srl' => 1006, 'module_srl' => 1001, 'title' => 'Dummy category 1006', 'parent_srl' => 0),
				array('category_srl' => 1008, 'module_srl' => 1001, 'title' => 'Dummy category 1008', 'parent_srl' => 1000)
			)
		));
	}

	/**
	 * Tests inserting a new Product category - makes sure all fields are properly persisted
	 *
	 * @return void
	 */
	public function testInsertCategory_ValidData()
	{
		// Create new Product category object
		$category = new Category();
		$category->module_srl = 2;
		$category->title = "Product category 1";

		$shopModel = getModel('shop');
		$repository = $shopModel->getCategoryRepository();
		try
		{
			// Try to insert the new Category
			$category_srl = $repository->insertCategory($category);

			// Check that a srl was returned
			$this->assertNotNull($category_srl);

			// Read the newly created object from the database, to compare it with the source object
			$output = Database::executeQuery("SELECT * FROM xe_shop_categories WHERE category_srl = $category_srl");
			$this->assertEquals(1, count($output));

			$new_category = $output[0];
			$this->assertEquals($category->module_srl, $new_category->module_srl);
			$this->assertEquals($category->title, $new_category->title);
			$this->assertNotNull($new_category->regdate);
			$this->assertNotNull($new_category->last_update);

			// Delete product we just added after test is finished
			Database::executeNonQuery("DELETE FROM xe_shop_categories WHERE category_srl = $category_srl");
		} catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Test deleting a Product category by id with valid data (srl is provided)
	 *
	 * @return void
	 */
	public function testDeleteCategoryBySrl_ValidData()
	{
		// Delete Product category 1000
		$shopModel = &getModel('shop');
		$repository = $shopModel->getCategoryRepository();
		$args = new stdClass();
		$args->category_srl = 1000;
		try
		{
			$output = $repository->deleteCategory($args);

			// Check that deleting was successful
			$this->assertTrue($output);

			// Check that the record is no longer in the database
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_shop_categories WHERE category_srl = 1000");
			$this->assertEquals(0, $count[0]->count);

			// Check that the other record was not also deleted by mistake
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_shop_categories WHERE category_srl = 1002");
			$this->assertEquals(1, $count[0]->count);
		} catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Test deleting more Product categories by module_srl with valid data (srl is provided)
	 *
	 * @return void
	 */
	public function testDeleteCategoryByModuleSrl_ValidData()
	{
		// Delete Product category 1000
		$shopModel = &getModel('shop');
		$repository = $shopModel->getCategoryRepository();

		$args = new stdClass();
		$args->module_srl = 1001;
		try
		{
			$output = $repository->deleteCategory($args);

			// Check that deleting was successful
			$this->assertTrue($output);

			// Check that the record is no longer in the database
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_shop_categories WHERE module_srl = 1001");
			$this->assertEquals(0, $count[0]->count);

			// Check that the other record was not also deleted by mistake
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_shop_categories WHERE module_srl = 1003");
			$this->assertEquals(1, $count[0]->count);
		} catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Test retrieving a Category object from the database
	 *
	 * @return void
	 */
	public function testGetCategory_ValidData()
	{
		// Try to retieve
		$shopModel = getModel('shop');
		$repository = $shopModel->getCategoryRepository();
		try
		{
			$category = $repository->getCategory(1000);

			$this->assertNotNull($category);
			$this->assertEquals(1000, $category->category_srl);
			$this->assertEquals(1001, $category->module_srl);
			$this->assertEquals("Dummy category 1000", $category->title);
		} catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Test updating a product category
	 *
	 * @depends testGetCategory_ValidData
	 *
	 * @return void
	 */
	public function testUpdateCategory_ValidData()
	{
		// Retrieve an object from the database
		$shopModel = getModel('shop');
		$repository = $shopModel->getCategoryRepository();
		$category = $repository->getCategory(1000);

		// Update some of its properties
		$category->title = "A whole new title!";

		// Try to update
		try
		{
			$output = $repository->updateCategory($category);

			$this->assertEquals(TRUE, $output);

			// Check that properties were updated
			$new_category = $repository->getCategory($category->category_srl);
			$this->assertEquals($category->title, $new_category->title);
		} catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}


	/**
	 * Test that tree hierarchy is properly returned
	 *
	* @return void
	 */
	public function testCategoryTreeHierarchy()
	{
		// Retrieve tree
		$shopModel = &getModel('shop');
		$repository = $shopModel->getCategoryRepository();

		$tree = $repository->getCategoriesTree(1001);

		// Check hierarchy
		$this->assertNotNull($tree);
		$this->assertNull($tree->category); // Root node should not have any product category associated

		foreach($tree->children as $id => $node)
		{
			if($id == 1000)
			{
				$this->assertEquals(1000, $node->category->category_srl);
				$this->assertEquals(2, count($node->children));
			}
			elseif($id = 1006)
			{
				$this->assertEquals(1006, $node->category->category_srl);
				$this->assertEquals(0, count($node->children));
			}
			else
			{
				$this->fail("Unexpected node found as root: " . $id);
			}
		}
	}

	/**
	 * Test product category tree as flat structure
	 * Used for UI, when printing nested lists
	 *
	 * @return void
	 */
	public function testCategoryFlatTreeHierarchy()
	{
		// Retrieve tree
		$shopModel = &getModel('shop');
		$repository = $shopModel->getCategoryRepository();

		$tree = $repository->getCategoriesTree(1001);
		$flat_tree = $tree->toFlatStructure();
		foreach($flat_tree as $node)
		{
			echo $node->depth . ' ' . $node->category->category_srl . PHP_EOL;
		}

		// Test flat structure
		$this->assertTrue(is_array($flat_tree));
		$this->assertEquals(4, count($flat_tree));

		$this->assertEquals(1000, $flat_tree[0]->category->category_srl);
		$this->assertEquals(0, $flat_tree[0]->depth);
		$this->assertEquals(1002, $flat_tree[1]->category->category_srl);
		$this->assertEquals(1, $flat_tree[1]->depth);
		$this->assertEquals(1008, $flat_tree[2]->category->category_srl);
		$this->assertEquals(1, $flat_tree[2]->depth);
		$this->assertEquals(1006, $flat_tree[3]->category->category_srl);
		$this->assertEquals(0, $flat_tree[3]->depth);
	}

	/**
	 * Test that Product category image gets updated
	 *
	 * @return void
	 */
	public function testRemoveCategoryFilename()
	{
		$shopModel = &getModel('shop');
		$repository = $shopModel->getCategoryRepository();

		$category = $repository->getCategory(1000);
		$category->filename = '';

		// Try to update
		try
		{
			$output = $repository->updateCategory($category);

			$this->assertEquals(TRUE, $output);

			// Check that properties were updated
			$new_category = $repository->getCategory($category->category_srl);
			$this->assertEquals($category->filename, $new_category->filename);

		} catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}


	}

	/**
	 * Test that Product category [include_in_navigation_menu] gets updated
	 *
	 * @return void
	 */
	public function testUpdateCategoryIncludeInNavigationMenu()
	{
		$shopModel = &getModel('shop');
		$repository = $shopModel->getCategoryRepository();

		$category = $repository->getCategory(1000);
		$category->include_in_navigation_menu = 'N';

		// Try to update
		try
		{
			$output = $repository->updateCategory($category);

			$this->assertEquals(TRUE, $output);

			// Check that properties were updated
			$new_category = $repository->getCategory($category->category_srl);

			echo "Expected: " . $category->getIncludeInNavigationMenu() . PHP_EOL;
			echo "Actual: " . $new_category->getIncludeInNavigationMenu() . PHP_EOL;

			$this->assertEquals($category->include_in_navigation_menu
				, $new_category->include_in_navigation_menu);

		} catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Test that product count gets updated when a new product is added to a category
	 *
	 * @return void
	 */
	public function testProductCountUpdatesOnProductAdd()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');

		// Make sure product count is 0 at the beginning
		$category_repository = $shopModel->getCategoryRepository();
		$category = $category_repository->getCategory(1000);

		$this->assertEquals(0, $category->product_count);

		// Add new product
		$product_repository = $shopModel->getProductRepository();

		$product = new Product();
		$product->product_srl = 12;
		$product->title = "Some product";
		$product->member_srl = 4;
		$product->module_srl = 1;
		$product->product_type = 'simple';
		$product->sku = 'some-product';
		$product->friendly_url = $product->sku;
		$product->price = 100;
		$product->categories[] = 1000;

		$product_repository->insertProduct($product);

		// Check that count was increased
		$category = $category_repository->getCategory(1000);
		$this->assertEquals(1, $category->product_count);


	}
}

/* End of file CategoryTest.php */
/* Location: ./modules/shop/tests/CategoryTest.php */
