<?php
require_once "lib/Shop_Generic_Tests.class.php";
require dirname(__FILE__) . '/lib/Bootstrap.php';
require_once dirname(__FILE__) . '/../libs/repositories/GuestRepository.php';

class GuestTest extends Shop_Generic_Tests_DatabaseTestCase
{
    /* @var shopModel */
    protected $model;
    /* @var GuestRepository */
    protected $repo;

    protected function setUp()
    {
        $this->model = getModel('shop');
        $this->repo = $this->model->getGuestRepository();
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new Shop_DbUnit_ArrayDataSet(array(
            'xe_shop_guests' => array()
        ));
    }

}