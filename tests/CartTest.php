<?php
require_once "lib/Shop_Generic_Tests.class.php";
require dirname(__FILE__) . '/lib/Bootstrap.php';
require_once dirname(__FILE__) . '/../libs/repositories/CartRepository.php';

class CartTest extends Shop_Generic_Tests_DatabaseTestCase
{
    /* @var shopModel */
    protected $model;
    /* @var CartRepository */
    protected $repo;

    protected function setUp()
    {
        $this->model = getModel('shop');
        $this->repo = $this->model->getCartRepository();
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new Shop_DbUnit_ArrayDataSet(array(
            'xe_shop_cart' => array(/*
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
            */)
        ));
    }

    public function testFirstCount()
    {
        $this->assertEquals(0, $this->getConnection()->getRowCount('xe_shop_cart'), "First count");
    }

    /**
     * @expectedException Exception
     */
    public function testAddCart()
    {
        $cart = new Cart(array(
            'module_srl'    => 307,
            'member_srl'    => null,
            'guest_srl'     => 14,
            'session_id'    => null,
            'items'         => null,
            'regdate'       => '20100424171420',
            'last_update'   => '20100424192420'
        ));

        $this->repo->insertCart($cart);
        $this->assertEquals(1, $this->getConnection()->getRowCount('xe_shop_cart'), "Insert failed");
    }

    public function testGetCart()
    {
        $cart = $this->repo->getCart(307);
        $this->assertInstanceOf('Cart', $cart);
    }

}