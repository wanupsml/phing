<?php
defined('PHING_TEST_BASE') || define('PHING_TEST_BASE', dirname(__FILE__));
set_include_path(
    realpath(dirname(__FILE__) . '/../classes') . PATH_SEPARATOR .
    realpath(dirname(__FILE__) . '/classes') . PATH_SEPARATOR .
    realpath(dirname(__FILE__) . '/../vendor/pear/http_request2') . PATH_SEPARATOR .
    get_include_path()  // trunk version of phing classes should take precedence
);

/**
 * Load composer autoload. This ubication is only related to test purposes.
 */
$autoloadFile = dirname(__FILE__) . '/../vendor/autoload.php';
if (is_readable($autoloadFile))
{
	require_once ($autoloadFile);
}
unset($autoloadFile);

/**
 * create tmp folder for test pourposes
 */
if (!file_exists(PHING_TEST_BASE . '/tmp'))
{
	mkdir(PHING_TEST_BASE . '/tmp');
}


require_once(dirname(__FILE__) . '/classes/phing/BuildFileTest.php');
require_once('phing/Phing.php');

// Use composers autoload.php if available
if (file_exists(dirname(__FILE__) . '/../vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/../vendor/autoload.php';
} elseif (file_exists(dirname(__FILE__) . '/../../../autoload.php')) {
    require_once dirname(__FILE__) . '/../../../autoload.php';
}

Phing::setProperty('phing.home', realpath(dirname(__FILE__) . '/../'));
Phing::startup();

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
