<?php

/**
 * Base class for tests using a database
 */

class TestAgainstDatabase extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = FALSE;
	protected $backupStaticAttributes = FALSE;
	protected $preserveGlobalState = FALSE;

	/**
	 * Prepare runtime context - tell DB class about current db connection info
	 */
	protected function setUp() {
		$oContext = &Context::getInstance();

		$db_info = include dirname(__FILE__).'/db.config.php';

		$db = new stdClass();
		$db->master_db = $db_info;
		$db->slave_db = array($db_info);
		$oContext->setDbInfo($db);

		// remove cache dir
		FileHandler::removeDir( _XE_PATH_ . 'files/cache');

		DB::getParser(true);
	}

	/**
	 * Free resources - reset static DB and QueryParser
	 */
	protected function tearDown() {
		unset($GLOBALS['__DB__']);
	}
}
?>
