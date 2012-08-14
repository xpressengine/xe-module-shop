<?php

if(!defined('__DEBUG__')) define('__DEBUG__', 1);
if(!defined('__XE__')) define('__XE__', TRUE);
if(!defined('__ZBXE__')) define('__ZBXE__', TRUE);
if(!defined('_XE_PATH_')) define('_XE_PATH_', realpath(dirname(__FILE__) . '/../../../../') . '/');

require_once(_XE_PATH_ . 'config/config.inc.php');

// Delete any cache files
FileHandler::removeDir(_XE_PATH_ . 'files/cache');

$oContext = Context::getInstance();
Context::setLangType('en');

// Load common language files, for the error messages to be displayed
Context::loadLang(_XE_PATH_.'common/lang/');

/* End of file Bootstrap.php */
/* Location: ./modules/shop/tests/lib/Bootstrap.php */
