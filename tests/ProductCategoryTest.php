<?php

require dirname(__FILE__) . '/lib/Bootstrap.php';
require dirname(__FILE__) . '/lib/TestAgainstDatabase.php';

require_once dirname(__FILE__) . '/../libs/model/ProductCategory.php';

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
		// Insert a few product categories in the database, so that we will have what to delete
		Database::executeNonQuery("
			INSERT INTO xe_shop_product_categories (product_category_srl, module_srl, title, parent_srl, filename)
				VALUES(1000, 1001, 'Dummy category 1000', 0, 'files/attach/picture.jpg')
			");
		Database::executeNonQuery("
			INSERT INTO xe_shop_product_categories (product_category_srl, module_srl, title, parent_srl)
				VALUES(1002, 1001, 'Dummy category 1002', 1000)
			");
		Database::executeNonQuery("
			INSERT INTO xe_shop_product_categories (product_category_srl, module_srl, title, parent_srl)
				VALUES(1004, 1003, 'Dummy category 1002', 0)
			");
		Database::executeNonQuery("
			INSERT INTO xe_shop_product_categories (product_category_srl, module_srl, title, parent_srl)
				VALUES(1006, 1001, 'Dummy category 1006', 0)
			");
		Database::executeNonQuery("
			INSERT INTO xe_shop_product_categories (product_category_srl, module_srl, title, parent_srl)
				VALUES(1008, 1001, 'Dummy category 1008', 1000)
			");
	}
    /**
     * Tests inserting a new Product - makes sure all fields are properly persisted
     * @author Dan dragan (dev@xpressengine.org)
     */
    public function testInsertProduct_ValidData()
    {
        // Create new Product object
        $args = new stdClass();
        $args->module_srl = 201;
        $args->member_srl = 4;
        $args->product_type = "simple";
        $args->title = "Product 1";
        $args->description = "Lorem ipsum dolor sit amet, te amet scaevola volutpat eum, ius an decore recteque patrioque, mel sint epicurei ut. Ea lorem noluisse est, ea sed nisl libris electram. Cu vivendum facilisis scribentur mel, bonorum elaboraret no per. Nec eu vidit omittantur, ei putant timeam detraxit quo, urbanitas efficiendi sit id. Mei putent eirmod voluptua ut, at dictas invenire delicata duo.

Patrioque conceptam in mea. Est ad ullum ceteros, pro quem accumsan appareat id, pro nominati electram no. Duo lorem maiorum urbanitas te, cu eum dicunt laoreet, etiam sententiae scriptorem at mel. Vix tamquam epicurei et, quo tota iudicabit an. Duo ea agam antiopam. Et per diam percipitur.";
        $args->short_description = "short_description";
        $args->sku = "SKU1";
        $args->status = '1';
        $args->friendly_url = "product1";
        $args->price = 10.5;
        $args->qty = 0;
        $args->in_stock = "Y";

        $shopModel = getModel('shop');
		$repository = $shopModel->getProductRepository();
        try
        {
            // Try to insert the new Product

            $product_srl = $repository->insertProduct($args);

            // Check that a srl was returned
            $this->assertNotNull($product_srl);

            // Read the newly created object from the database, to compare it with the source object
            $output = Database::executeQuery("SELECT * FROM xe_shop_products WHERE product_srl = $product_srl");
            $this->assertEquals(1, count($output));

            $product = $output[0];
            $this->assertEquals($args->module_srl, $product->module_srl);
            $this->assertEquals($args->member_srl, $product->member_srl);
            $this->assertEquals($args->product_type, $product->product_type);
            $this->assertEquals($args->title, $product->title);
            $this->assertEquals($args->description, $product->description);
            $this->assertEquals($args->short_description, $product->short_description);
            $this->assertEquals($args->sku, $product->sku);
            $this->assertEquals($args->status, $product->status);
            $this->assertEquals($args->friendly_url, $product->friendly_url);
            $this->assertEquals($args->price, $product->price);
            $this->assertEquals($args->qty, $product->qty);
            $this->assertEquals($args->in_stock, $product->in_stock);
            $this->assertNotNull($product->regdate);
            $this->assertNotNull($product->last_update);

            // Delete product we just added after test is finished
            Database::executeNonQuery("DELETE FROM xe_shop_products WHERE product_srl = $product_srl");
        }
        catch(Exception $e)
        {
            $this->fail($e->getMessage());
        }
    }
	/**
	 * Tests inserting a new Product category - makes sure all fields are properly persisted
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testInsertProductCategory_ValidData()
	{
		// Create new Product category object
		$product_category = new ProductCategory();
		$product_category->module_srl = 2;
		$product_category->title = "Product category 1";

		$shopModel = getModel('shop');
		$repository = $shopModel->getProductCategoryRepository();
		try
		{
			// Try to insert the new Category
			$product_category_srl = $repository->insertProductCategory($product_category);

			// Check that a srl was returned
			$this->assertNotNull($product_category_srl);

			// Read the newly created object from the database, to compare it with the source object
			$output = Database::executeQuery("SELECT * FROM xe_shop_product_categories WHERE product_category_srl = $product_category_srl");
			$this->assertEquals(1, count($output));

			$new_product_category = $output[0];
			$this->assertEquals($product_category->module_srl, $new_product_category->module_srl);
			$this->assertEquals($product_category->title, $new_product_category->title);
			$this->assertNotNull($new_product_category->regdate);
			$this->assertNotNull($new_product_category->last_update);

			// Delete product we just added after test is finished
			Database::executeNonQuery("DELETE FROM xe_shop_product_categories WHERE product_category_srl = $product_category_srl");
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
		// Delete Product category 1000
		$shopModel = &getModel('shop');
		$repository = $shopModel->getProductCategoryRepository();
		$args = new stdClass();
		$args->product_category_srl = 1000;
		try
		{
			$output = $repository->deleteProductCategory($args);

			// Check that deleting was successful
			$this->assertTrue($output);

			// Check that the record is no longer in the database
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_shop_product_categories WHERE product_category_srl = 1000");
			$this->assertEquals(0, $count[0]->count);

			// Check that the other record was not also deleted by mistake
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_shop_product_categories WHERE product_category_srl = 1002");
			$this->assertEquals(1, $count[0]->count);
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
		// Delete Product category 1000
		$shopModel = &getModel('shop');
		$repository = $shopModel->getProductCategoryRepository();

		$args = new stdClass();
		$args->module_srl = 1001;
		try
		{
			$output = $repository->deleteProductCategory($args);

			// Check that deleting was successful
			$this->assertTrue($output);

			// Check that the record is no longer in the database
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_shop_product_categories WHERE module_srl = 1001");
			$this->assertEquals(0, $count[0]->count);

			// Check that the other record was not also deleted by mistake
			$count = Database::executeQuery("SELECT COUNT(*) as count FROM xe_shop_product_categories WHERE module_srl = 1003");
			$this->assertEquals(1, $count[0]->count);
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Test retrieving a ProductCategory object from the database
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testGetProductCategory_ValidData()
	{
		// Try to retieve
		$shopModel = getModel('shop');
		$repository = $shopModel->getProductCategoryRepository();
		try
		{
			$product_category = $repository->getProductCategory(1000);

			$this->assertNotNull($product_category);
			$this->assertEquals(1000, $product_category->product_category_srl);
			$this->assertEquals(1001, $product_category->module_srl);
			$this->assertEquals("Dummy category 1000", $product_category->title);
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Test updating a product category
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @depends testGetProductCategory_ValidData
	 */
	public function testUpdateProductCategory_ValidData()
	{
		// Retrieve an object from the database
		$shopModel = getModel('shop');
		$repository = $shopModel->getProductCategoryRepository();
		$product_category = $repository->getProductCategory(1000);

		// Update some of its properties
		$product_category->title = "A whole new title!";

		// Try to update
		try
		{
			$output = $repository->updateProductCategory($product_category);

			$this->assertEquals(TRUE, $output);

			// Check that properties were updated
			$new_product_category = $repository->getProductCategory($product_category->product_category_srl);
			$this->assertEquals($product_category->title, $new_product_category->title);
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}


	/**
	 * Test that tree hierarchy is properly returned
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testProductCategoryTreeHierarchy()
	{
		// Retrieve tree
		$shopModel = &getModel('shop');
		$repository = $shopModel->getProductCategoryRepository();

		$tree = $repository->getProductCategoriesTree(1001);

		// Check hierarchy
		$this->assertNotNull($tree);
		$this->assertNull($tree->product_category); // Root node should not have any product category associated

		foreach($tree->children as $id => $node)
		{
			if($id == 1000)
			{
				$this->assertEquals(1000, $node->product_category->product_category_srl);
				$this->assertEquals(2, count($node->children));
			}
			elseif($id = 1006)
			{
				$this->assertEquals(1006, $node->product_category->product_category_srl);
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
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testProductCategoryFlatTreeHierarchy()
	{
		// Retrieve tree
		$shopModel = &getModel('shop');
		$repository = $shopModel->getProductCategoryRepository();

		$tree = $repository->getProductCategoriesTree(1001);
		$flat_tree = $tree->toFlatStructure();
		foreach($flat_tree as $node)
		{
			echo $node->depth . ' ' . $node->product_category->product_category_srl . PHP_EOL;
		}

		// Test flat structure
		$this->assertTrue(is_array($flat_tree));
		$this->assertEquals(4, count($flat_tree));

		$this->assertEquals(1000, $flat_tree[0]->product_category->product_category_srl);
		$this->assertEquals(0, $flat_tree[0]->depth);
		$this->assertEquals(1002, $flat_tree[1]->product_category->product_category_srl);
		$this->assertEquals(1, $flat_tree[1]->depth);
		$this->assertEquals(1008, $flat_tree[2]->product_category->product_category_srl);
		$this->assertEquals(1, $flat_tree[2]->depth);
		$this->assertEquals(1006, $flat_tree[3]->product_category->product_category_srl);
		$this->assertEquals(0, $flat_tree[3]->depth);
	}

	/**
	 * Test that Product category image gets updated
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 */
	public function testRemoveProductCategoryFilename()
	{
		$shopModel = &getModel('shop');
		$repository = $shopModel->getProductCategoryRepository();

		$product_category = $repository->getProductCategory(1000);
		$product_category->filename = '';

		// Try to update
		try
		{
			$output = $repository->updateProductCategory($product_category);

			$this->assertEquals(TRUE, $output);

			// Check that properties were updated
			$new_product_category = $repository->getProductCategory($product_category->product_category_srl);
			$this->assertEquals($product_category->filename, $new_product_category->filename);

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
		// Revert changes: delete the product categories added previously
		Database::executeNonQuery("DELETE FROM xe_shop_product_categories WHERE product_category_srl IN (1000, 1002, 1004, 1006, 1008)");

		parent::tearDown();
	}

}

/* End of file ProductCategoryTest.php */
/* Location: ./modules/shop/tests/ProductCategoryTest.php */
