<?php

require_once dirname(__FILE__) . '/../lib/Shop_SeleniumTestCase.php';

class HomepageTest extends Shop_SeleniumTestCase
{
	protected function setUp()
	{
		$this->setBrowser('firefox');
		$this->setBrowserUrl($this->xe_root_url);
	}

	public function testTitle()
	{
		$this->open($this->xe_root_url . '?mid=' . $this->mid . '&vid=' . $this->vid);
		$this->assertTitle("XE Shop Demo");
	}

}

?>