<?php

abstract class Shop_SeleniumTestCase extends PHPUnit_Extensions_SeleniumTestCase
{
	protected $captureScreenshotOnFailure = TRUE;
	protected $screenshotPath = '/../logs/screenshots';
	protected $screenshotUrl = 'http://localhost/screenshots';
	protected $xe_root_url;
	protected $mid = 'shop';

	public function __construct()
	{
		parent::__construct();
		$this->screenshotUrl = $GLOBALS['XE_ROOT_URL'] . '/modules/shop/tests/logs/screenshots';
		$this->screenshotPath = dirname(__FILE__) . '/../logs/screenshots';
		$this->xe_root_url = $GLOBALS['XE_ROOT_URL'];
		$this->vid = $GLOBALS['XE_SHOP_VID'];
	}

}

?>