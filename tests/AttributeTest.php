<?php
require_once "lib/Shop_Generic_Tests.class.php";
require dirname(__FILE__) . '/lib/Bootstrap.php';
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
                    'title'         => 'hehe',
                    'type'          => 'select',
                    'required'      => 'Y',
                    'status'        => 'Y',
                    'values'        => 'sa|b|cas',
                    'default_value' => 'sa',
                    'regdate'       => '2010-04-24 17:15:23',
                    'last_update'   => '2010-04-24 19:15:23'
                ),
                array(
                    'attribute_srl' => 1406,
                    'member_srl'    => 7,
                    'title'         => 'trolo',
                    'type'          => 'select',
                    'required'      => 'Y',
                    'status'        => 'Y',
                    'values'        => 'be|he|he2',
                    'default_value' => 'he',
                    'regdate'       => '2010-04-24 12:15:13',
                    'last_update'   => '2010-04-24 14:14:12'
                )
            )
        ));
    }

    public function testAddEntry()
    {
        $this->assertEquals(2, $this->getConnection()->getRowCount('xe_shop_attributes'), "First count");
        $model = getModel('shop');
        $model = $model->getAttributesModel();
        $attribute = new Attribute((object) array(
            'attribute_srl' => 1412,
            'member_srl'    => 7,
            'title'         => 'yoyo',
            'type'          => 'select',
            'required'      => 'Y',
            'status'        => 'Y',
            'values'        => 'a|b|c',
            'default_value' => 'c',
            'regdate'       => '2010-04-24 12:15:13',
            'last_update'   => '2010-04-24 14:14:12'
        ));
        $attribute->save();
        $this->assertEquals(2, $this->getConnection()->getRowCount('xe_shop_attributes'), "Insert failed");
    }

}