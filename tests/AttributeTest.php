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

}