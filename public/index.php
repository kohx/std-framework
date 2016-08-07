<?php

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 *
 */
define('EXT', '.php');

/**
 * Paths
 */
// Set the full path to the docroot
define('DOCROOT', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR);

// Define the absolute paths for configured directories
define('PRIPATH', DOCROOT . 'private' . DIRECTORY_SEPARATOR);

define('APPPATH', PRIPATH . 'app' . DIRECTORY_SEPARATOR);

define('PUBPATH', DOCROOT . 'public' . DIRECTORY_SEPARATOR);

define('VENPATH', DOCROOT . 'vender' . DIRECTORY_SEPARATOR);

/**
 * Auto loarder
 */
require_once PRIPATH . 'classes' . DIRECTORY_SEPARATOR . 'AutoLoader.php';

/**
 * Bootstrap the application
 */
include APPPATH . 'bootstrap' . EXT;


/**
 * Sanitize all request variables
 */
$_GET = Security::sanitize($_GET);
$_POST = Security::sanitize($_POST);
$_COOKIE = Security::sanitize($_COOKIE);

/**
 * Render respose
 */
Response::fact()->execute();
