<?php

/**
 * This is the Phing command line launcher. It starts up the system evironment
 * tests for all important paths and properties and kicks of the main command-
 * line entry point of phing located in phing.Phing
 * @version $Id$
 */

/*
 * use composer if available.
 * move test logic to test/bootstrap.php
 */
if (is_readable('composer.json')) {
	phing_composer::load_autoload_file('composer.json');
} elseif (is_readable('../composer.json')) {
	/**
	 * handle autoloader if phing is executed under test folder!
	 */
    phing_composer::load_autoload_file('../composer.json');
 }

/**
 * load always phing composer autoload last.
 * If we don't do it, global installation of phing crash in
 * projects that have phing dependencies.
 */
phing_composer::load_autoload_file(getenv('PHING_HOME') . '/composer.json');


// Set any INI options for PHP
// ---------------------------

/* set include paths */
set_include_path(
            dirname(__FILE__) . '/../classes' .
            PATH_SEPARATOR .
            get_include_path()
        );

require_once 'phing/Phing.php';

/**
* Code from Symfony/Component/Console/Output/StreamOutput.php
*/
function hasColorSupport()
{
    if (DIRECTORY_SEPARATOR == '\\') {
        return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI');
    }
    return function_exists('posix_isatty') && @posix_isatty(STDOUT);
}

// default logger
if (!in_array('-logger', $argv) && hasColorSupport()) {
    array_splice($argv, 1, 0, array('-logger', 'phing.listener.AnsiColorLogger'));
}

try {

    /* Setup Phing environment */
    Phing::startup();

    // Set phing.home property to the value from environment
    // (this may be NULL, but that's not a big problem.)
    Phing::setProperty('phing.home', getenv('PHING_HOME'));
    // Grab and clean up the CLI arguments
    $args = isset($argv) ? $argv : $_SERVER['argv']; // $_SERVER['argv'] seems to not work (sometimes?) when argv is registered
    array_shift($args); // 1st arg is script name, so drop it

    // Invoke the commandline entry point
    Phing::fire($args);

    // Invoke any shutdown routines.
    Phing::shutdown();

} catch (ConfigurationException $x) {

    Phing::printMessage($x);
    exit(-1); // This was convention previously for configuration errors.

} catch (Exception $x) {

    // Assume the message was already printed as part of the build and
    // exit with non-0 error code.

    exit(1);

}

class phing_composer
{
    /**
     * @var array
     */
    static $autoloadFiles = [];
    /**
     * load composer autoloader
     * @param string $composerFile
     */
    static function load_autoload_file($composerFile)
    {
        $dir = realpath(dirname($composerFile));
        $composer = json_decode(file_get_contents($composerFile), true);
        if (is_array($composer) &&
            isset($composer['config']['vendor-dir'])
        ) {
            $autoloadDir = $dir . '/' . $composer['config']['vendor-dir'];
        } else {
            $autoloadDir = $dir . '/vendor';
        }



        $autoloadFile = realpath($autoloadDir . '/autoload.php');

        /**
         * check if we loaded the same autoload file previously
         * in projects with phing as dependence
         */
        if (isset(static::$autoloadFiles[$autoloadFile])) {
            return;
        }

        if (is_readable($autoloadFile)) {
            require_once $autoloadFile;

            /**
             * store loaded file, to prevent to load twice
             * composer autoloader in projects with phing as
             * dependence
             */
            static::$autoloadFiles[$autoloadFile] = true;

            /**
             * check if defined when we load two composer files,
             * first project file
             * second global phing composer
             */
            if (!defined('PHING_COMPOSER_VENDOR_DIR')) {
                define('PHING_COMPOSER_VENDOR_DIR', $autoloadDir);
            }

            if (defined('PHING_COMPOSER_AUTOLOAD_FILE')) {
                define('PHING_COMPOSER_AUTOLOAD_FILE', $autoloadFile);
            }
        }
    }

}