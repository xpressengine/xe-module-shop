<?php

require_once dirname(__FILE__) . '/../lib/Shop_SeleniumTestCase.php';

class AdminDashboardTest extends Shop_SeleniumTestCase
{
	protected $mid = 'magazin';

	protected function setUp()
	{
		$this->setBrowser('firefox');
		$this->setBrowserUrl("http://www.example.com");
	}

	public function testTitle()
	{
		$act = 'dispShopToolDashboard';

		$this->open($this->xe_root_url . '?act=' . $act . '&mid=' . $this->mid . '&vid=' . $this->vid);
		$this->assertTitle("Corina's shop - admin");
	}

}

?>