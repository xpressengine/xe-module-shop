<?php
require_once "lib/Shop_Generic_Tests.class.php";
require dirname(__FILE__) . '/lib/Bootstrap.php';
require_once dirname(__FILE__) . '/../libs/repositories/CartRepository.php';

class CartTest extends Shop_Generic_Tests_DatabaseTestCase
{
    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new Shop_DbUnit_ArrayDataSet(array(
            'xe_shop_cart' => array(
                array(
                    'cart_srl'      => 1,
                    'module_srl'    => 307,
                    'member_srl'    => 7,
                    'guest_srl'     => null,
                    'session_id'    => null,
                    'items'         => null,
                    'regdate'       => '20100424171523',
                    'last_update'   => '20100424191527'
                ),
                array(
                    'cart_srl'      => 2,
                    'module_srl'    => 307,
                    'member_srl'    => null,
                    'guest_srl'     => 14,
                    'session_id'    => null,
                    'items'         => null,
                    'regdate'       => '20100424171526',
                    'last_update'   => '20100424191528'
                )
            )
        ));
    }

    public function testAddEntry()
    {
        $this->assertEquals(2, $this->getConnection()->getRowCount('xe_shop_cart'), "First count");
        $model = getModel('shop');
        /* @var shopModel $model */
        $model->getCartRepository();
        $cart = new Cart(array(
            'cart_srl'      => 3,
            'module_srl'    => 307,
            'member_srl'    => null,
            'guest_srl'     => 14,
            'session_id'    => null,
            'items'         => null,
            'regdate'       => '20100424171420',
            'last_update'   => '20100424192420'
        ));
        $cart->save();
        $this->assertEquals(2, $this->getConnection()->getRowCount('xe_shop_attributes'), "Insert failed");
    }

}