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

    const CATEGORY_SAMSUNG = 451,
            CATEGORY_NOKIA = 473,
            CATEGORY_LG = 474,
            CATEGORY_MASERATI = 475,
            CATEGORY_LAPTOPS = 462,
            CATEGORY_APPLE = 463,
            CATEGORY_FUJITSU = 464,
            CATEGORY_PHONES = 502,
            CATEGORY_CARS = 503,
            CATEGORY_SONY_ERICSSON = 508;

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
				array('category_srl' => 1008, 'module_srl' => 1001, 'title' => 'Dummy category 1008', 'parent_srl' => 1000),
                array('category_srl' => 1010, 'module_srl' => 1004, 'title' => 'Dummy category 1010', 'parent_srl' => 2),
                array('category_srl' => self::CATEGORY_SAMSUNG,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'Samsung','description' => 'sdfsadfsadfsa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120921192939','last_update' => '20120924180718','order' => self::CATEGORY_SAMSUNG),
                array('category_srl' => self::CATEGORY_NOKIA,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'Nokia','description' => 'sdfdasf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183752','last_update' => '20120924180728','order' => self::CATEGORY_NOKIA),
                array('category_srl' => self::CATEGORY_LG,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'LG','description' => 'sdfsaf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183758','last_update' => '20120924180737','order' => self::CATEGORY_LG),
                array('category_srl' => self::CATEGORY_MASERATI,'module_srl' => '107','parent_srl' => self::CATEGORY_CARS,'filename' => NULL,'title' => 'Maserati','description' => 'aaaa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183804','last_update' => '20120924180750','order' => self::CATEGORY_MASERATI),
                array('category_srl' => self::CATEGORY_LAPTOPS,'module_srl' => '107','parent_srl' => 0,'filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bad977.','title' => 'Laptops','description' => 'descriere laptops','product_count' => '6','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','order' => self::CATEGORY_LAPTOPS),
                array('category_srl' => self::CATEGORY_APPLE,'module_srl' => '107','parent_srl' => self::CATEGORY_LAPTOPS,'filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bafb2a.','title' => 'Apple','description' => '','product_count' => '0','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120924180743','order' => self::CATEGORY_APPLE),
                array('category_srl' => self::CATEGORY_FUJITSU,'module_srl' => '107','parent_srl' => self::CATEGORY_APPLE,'filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bb0f06.','title' => 'Fujitsu','description' => '','product_count' => '5','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','order' => self::CATEGORY_FUJITSU),
                array('category_srl' => self::CATEGORY_PHONES,'module_srl' => '107','parent_srl' => 0,'filename' => NULL,'title' => 'Phones','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184414','last_update' => '20120924184414','order' => self::CATEGORY_PHONES),
                array('category_srl' => self::CATEGORY_CARS,'module_srl' => '107','parent_srl' => 0,'filename' => NULL,'title' => 'Cars','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184441','last_update' => '20120924184441','order' => self::CATEGORY_CARS),
                array('category_srl' => self::CATEGORY_SONY_ERICSSON,'module_srl' => '107','parent_srl' => self::CATEGORY_PHONES,'filename' => NULL,'title' => 'Sony Ericsson','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924185725','last_update' => '20120924185725','order' => self::CATEGORY_SONY_ERICSSON),
                array('category_srl' => '4451','module_srl' => '1111','parent_srl' => '4502','filename' => NULL,'title' => 'Samsung','description' => 'sdfsadfsadfsa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120921192939','last_update' => '20120924180718','order' => '4451'),
                array('category_srl' => '4473','module_srl' => '1111','parent_srl' => '4451','filename' => NULL,'title' => 'Nokia','description' => 'sdfdasf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183752','last_update' => '20120924180728','order' => '4473'),
                array('category_srl' => '4474','module_srl' => '1111','parent_srl' => '4451','filename' => NULL,'title' => 'LG','description' => 'sdfsaf','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183758','last_update' => '20120924180737','order' => '4475'),
                array('category_srl' => '4475','module_srl' => '1111','parent_srl' => '4503','filename' => NULL,'title' => 'Maserati','description' => 'aaaa','product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120922183804','last_update' => '20120924180750','order' => '4475'),
                array('category_srl' => '4462','module_srl' => '1111','parent_srl' => '0','filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bad977.','title' => 'Laptops','description' => 'descriere laptops','product_count' => '6','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','order' => '4462'),
                array('category_srl' => '4463','module_srl' => '1111','parent_srl' => '4462','filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bafb2a.','title' => 'Apple','description' => '','product_count' => '0','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120924180743','order' => '4463'),
                array('category_srl' => '4464','module_srl' => '1111','parent_srl' => '4463','filename' => './files/attach/images/shop/107/product-categories/product-category-505dc98bb0f06.','title' => 'Fujitsu','description' => '','product_count' => '5','friendly_url' => '','include_in_navigation_menu' => 'Y','regdate' => '20120922172203','last_update' => '20120923191348','order' => '4464'),
                array('category_srl' => '4502','module_srl' => '1111','parent_srl' => '0','filename' => NULL,'title' => 'Phones','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184414','last_update' => '20120924184414','order' => '4502'),
                array('category_srl' => '4503','module_srl' => '1111','parent_srl' => '0','filename' => NULL,'title' => 'Cars','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924184441','last_update' => '20120924184441','order' => '4503'),
                array('category_srl' => '4508','module_srl' => '1111','parent_srl' => '4502','filename' => NULL,'title' => 'Sony Ericsson','description' => NULL,'product_count' => '0','friendly_url' => NULL,'include_in_navigation_menu' => 'Y','regdate' => '20120924185725','last_update' => '20120924185725','order' => '4508')
			),
			'xe_shop_products' => array(),
			'xe_shop_product_categories' => array()
		));
	}

	/**
	 * Tests inserting a new Product category - makes sure all fields are properly persisted
	 *
	 * @return void
	 */
	public function testInsertCategory_ValidData()
	{
		$shopModel = getModel('shop');
		$repository = $shopModel->getCategoryRepository();

        // Create new Product category object
        $category = new Category();
        $category->module_srl = 2;
        $category->title = "Product category 1";

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
		$args->category_srl = 1002;
		try
		{
			$output = $repository->deleteCategory($args);

			// Check that deleting was successful
			$this->assertTrue($output);

			// Check that the record is no longer in the database
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_shop_categories WHERE category_srl = 1002");
			$this->assertEquals(0, $count[0]->count);

			// Check that the other record was not also deleted by mistake
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_shop_categories WHERE category_srl = 1000");
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
			elseif($id == 1006)
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

		$product = new SimpleProduct();
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

	/**
	 * Test that product count gets updated correctly when product is configurable
	 * This means it should only count the main product, not including
	 * its variants (aka associated products).
	 *
	 * @return void
	 */
	public function testProductCountDoesntIncludeAssociatedProducts()
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

		$product = new ConfigurableProduct();
		$product->product_srl = 12;
		$product->title = "Some product";
		$product->member_srl = 4;
		$product->module_srl = 1;
		$product->sku = 'some-product';
		$product->friendly_url = $product->sku;
		$product->price = 100;
		$product->categories[] = 1000;
		$product->configurable_attributes[21] = "Attribute1";
		$product->configurable_attributes[22] = "Attribute2";
		$product_repository->insertProduct($product);

		$associated_product1 = $product_repository->createProductFromParent($product, array("a", "b"));
		$product_repository->insertProduct($associated_product1);

		$associated_product2 = $product_repository->createProductFromParent($product, array("c", "d"));
		$product_repository->insertProduct($associated_product2);

		// Check that count was increased
		$category = $category_repository->getCategory(1000);
		$this->assertEquals(1, $category->product_count);
	}

    /**
     * Test that when invalid categories are found, they are ignored
     */
    public function testThatInvalidCategoriesAreIgnored()
    {
        /**
         * @var shopModel $shopModel
         */
        $shopModel = getModel('shop');
        $category_repository = $shopModel->getCategoryRepository();

        // This method will return a PHP fatal error if invalid child is found
        // PHP Fatal error:  Call to a member function addChild() on a non-object
        $tree = $category_repository->getCategoriesTree(1004);

        $this->assertNotNull($tree);
    }

    /**
     * Test a more mingled hierarchy, with more levels
     */
    public function testMoreComplicatedTreeHierarchy()
    {
        // Retrieve tree
        $shopModel = &getModel('shop');
        $repository = $shopModel->getCategoryRepository();

        $tree = $repository->getCategoriesTree(107);

        // Check hierarchy
        $this->assertNotNull($tree);
        $this->assertNull($tree->category); // Root node should not have any product category associated

        foreach($tree->children as $id => $node)
        {
            if($id == self::CATEGORY_LAPTOPS)
            {
                $this->assertEquals(self::CATEGORY_LAPTOPS, $node->category->category_srl);
                $this->assertEquals(1, count($node->children));

                $first_child_node = array_shift(array_values($node->children));
                $this->assertEquals(self::CATEGORY_APPLE, $first_child_node->category->category_srl);
                $this->assertEquals(1, count($first_child_node->children));

                $first_child_of_child_node = array_shift(array_values($first_child_node->children));
                $this->assertEquals(self::CATEGORY_FUJITSU, $first_child_of_child_node->category->category_srl);
                $this->assertEquals(0, count($first_child_of_child_node->children));
            }
            elseif($id == self::CATEGORY_PHONES)
            {
                $this->assertEquals(self::CATEGORY_PHONES, $node->category->category_srl);
                $this->assertEquals(4, count($node->children));
            }
            elseif($id == self::CATEGORY_CARS)
            {
                $this->assertEquals(self::CATEGORY_CARS, $node->category->category_srl);
                $this->assertEquals(1, count($node->children));
            }
            else
            {
                $this->fail("Unexpected node found as root: " . $id);
            }
        }
    }

    /**
     * Test a more mingled hierarchy, with more levels
     *
     * This bug caused two nodes to be ignored, because the sorting was done by parent srl
     * but not taking into account depth, thus the missing nodes
     */
    public function testMoreComplicatedTreeHierarchyAfterManuallyChanginParentsAndOrder()
    {
        // Retrieve tree
        $shopModel = &getModel('shop');
        $repository = $shopModel->getCategoryRepository();

        $tree = $repository->getCategoriesTree(1111);

        // Check hierarchy
        $this->assertNotNull($tree);
        $this->assertNull($tree->category); // Root node should not have any product category associated

        foreach($tree->children as $id => $node)
        {
            if($id == '4' . self::CATEGORY_LAPTOPS)
            {
                $this->assertEquals('4' . self::CATEGORY_LAPTOPS, $node->category->category_srl);
                $this->assertEquals(1, count($node->children));

                $first_child_node = array_shift(array_values($node->children));
                $this->assertEquals('4' . self::CATEGORY_APPLE, $first_child_node->category->category_srl);
                $this->assertEquals(1, count($first_child_node->children));

                $first_child_of_child_node = array_shift(array_values($first_child_node->children));
                $this->assertEquals('4' . self::CATEGORY_FUJITSU, $first_child_of_child_node->category->category_srl);
                $this->assertEquals(0, count($first_child_of_child_node->children));
            }
            elseif($id == '4' . self::CATEGORY_PHONES)
            {
                $this->assertEquals('4' . self::CATEGORY_PHONES, $node->category->category_srl);
                $this->assertEquals(2, count($node->children));

                $samsung = array_shift($node->children);
                $this->assertEquals('4' . self::CATEGORY_SAMSUNG, $samsung->category->category_srl);
                $this->assertEquals(2, count($samsung->children));

                    $nokia = array_shift($samsung->children);
                    $this->assertEquals('4' . self::CATEGORY_NOKIA, $nokia->category->category_srl);

                    $lg = array_shift($samsung->children);
                    $this->assertEquals('4' . self::CATEGORY_LG, $lg->category->category_srl);

                $sony = array_shift($node->children);
                $this->assertEquals('4' . self::CATEGORY_SONY_ERICSSON, $sony->category->category_srl);
            }
            elseif($id == '4' . self::CATEGORY_CARS)
            {
                $this->assertEquals('4' . self::CATEGORY_CARS, $node->category->category_srl);
                $this->assertEquals(1, count($node->children));
            }
            else
            {
                $this->fail("Unexpected node found as root: " . $id);
            }
        }
    }
}

/* End of file CategoryTest.php */
/* Location: ./modules/shop/tests/CategoryTest.php */
