<?php
require_once "lib/Shop_Generic_Tests.class.php";
require_once dirname(__FILE__) . '/lib/Bootstrap.php';
require_once dirname(__FILE__) . '/../libs/repositories/AttributeRepository.php';

class AttributeTest extends Shop_Generic_Tests_DatabaseTestCase
{
    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new Shop_DbUnit_ArrayDataSet(array(
            'xe_shop_attributes' => array(
                array(
                    'attribute_srl' => 1405,
                    'member_srl'    => 7,
					'module_srl'	=> 13,
                    'title'         => 'hehe',
                    'type'          => 'select',
                    'required'      => 'Y',
                    'status'        => 'Y',
                    'values'        => 'sa|b|cas',
                    'default_value' => 'sa',
                    'regdate'       => '20100424171523',
                    'last_update'   => '20100424191523'
                ),
                array(
                    'attribute_srl' => 1406,
                    'member_srl'    => 7,
					'module_srl'	=> 13,
                    'title'         => 'trolo',
                    'type'          => 'select',
                    'required'      => 'Y',
                    'status'        => 'Y',
                    'values'        => 'be|he|he2',
                    'default_value' => 'he',
                    'regdate'       => '20100424121513',
                    'last_update'   => '20100424141412'
                )
            )
        ));
    }

    public function testAddEntry()
    {
        $this->assertEquals(2, $this->getConnection()->getRowCount('xe_shop_attributes'), "First count");

		/**
		 * @var shopModel $model
		 */
		$model = getModel('shop');
        $model = $model->getAttributeRepository();
        $attribute = new Attribute((object) array(
            'member_srl'    => 7,
			'module_srl'	=> 9,
            'title'         => 'yoyo',
            'type'          => 'select',
            'required'      => 'Y',
            'status'        => 'Y',
            'values'        => 'a|b|c',
            'default_value' => 'c',
            'regdate'       => '20100424121513',
            'last_update'   => '20100424141412'
        ));
		$model->insertAttribute($attribute);
        $this->assertEquals(3, $this->getConnection()->getRowCount('xe_shop_attributes'), "Insert failed");
    }

	/**
	 * Test that configurable attributes are properly stored in the product object
	 */
	public function testConfigurableProductsAreStoredAsAssociativeArray()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');
		$product_repository = $shopModel->getProductRepository();

		$args = new stdClass();
		$args->module_srl = 112;
		$args->member_srl = 22;
		$args->title = "Some product";
		$args->sku = 'some-product';
		$args->price = 22;
		$args->configurable_attributes = array(1405, 1406);

		$configurable_product = new ConfigurableProduct($args);

		$this->assertEquals(2, count($configurable_product->configurable_attributes));

		foreach($configurable_product->configurable_attributes as $attribute_srl => $attribute_title)
		{
			$this->assertTrue(in_array($attribute_srl, array(1405, 1406)));
		}

	}

	/**
	 * Tests adding configurable attributes
	 */
	public function testAddConfigurableAttributes()
	{
		/**
		 * @var shopModel $shopModel
		 */
		$shopModel = getModel('shop');
		$product_repository = $shopModel->getProductRepository();

		$args = new stdClass();
		$args->module_srl = 112;
		$args->member_srl = 22;
		$args->title = "Some product";
		$args->sku = 'some-product';
		$args->price = 22;
		$args->configurable_attributes = array(1405, 1406);

		$configurable_product = new ConfigurableProduct($args);
		$new_product_srl = $product_repository->insertProduct($configurable_product);

		/**
		 * @var ConfigurableProduct $new_product
		 */
		$new_product = $product_repository->getProduct($new_product_srl);

		$this->assertEquals(2, count($new_product->configurable_attributes));
		foreach($new_product->configurable_attributes as $attribute_srl => $attribute_title)
		{
			$this->assertTrue(in_array($attribute_srl, array(1405, 1406)));
			$this->assertTrue(in_array($attribute_title, array("hehe", "trolo")));
		}
	}

}