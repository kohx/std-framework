<?php

/**
 * Get core
 */
$core = Config::fact('core');

/**
 * error_reporting(E_ALL | E_STRICT);
 */
error_reporting(-1);
ini_set('display_errors', 'on');
ini_set('output_buffering', 'on');
//
ini_set('allow_url_fopen', 'off');
ini_set('allow_url_include', 'off');
ini_set('expose_php', 'off');
ini_set('log_errors', 'on');
ini_set('error_log', 'c:/wamp/logs/php_error.log');
ini_set('error_log', '');
ini_set('disable_functions', 'phpinfo, ');
ini_set('enable_dl', 'off');
ini_set('register_globals', 'off');

ini_set('register_globals', 'UTF-8');
ini_set('file_uploads', 'on');
ini_set('session.bug_compat_42', 'off');
ini_set('session.bug_compat_warn', 'off');
ini_set('session.hash_function', 1);
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_lifetime', 0);
ini_set('session.use_trans_sid', 0);
ini_set('session.save_path', 'c:/wamp/tmp');
ini_set('session.auto_start', 0);
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.entropy_file', '');
ini_set('session.entropy_length', 32);

/**
 * Char set
 */
mb_language($core->get('language'));
mb_internal_encoding($core->get('internal_encoding'));

/**
 * Language
 */
I18n::load($core->get('lang'));

/**
 * Set the default time zone.
 *
 * @link http://www.php.net/manual/timezones
 */
date_default_timezone_set($core->get('date_default_timezone_set'));

/**
 * Set the default locale.
 *
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, $core->get('setlocale_LC_ALL'));

/**
 * Make router incstance
 */
$router = Router::inst();

/**
 * Set routes
 * 
 *			set('[url]', '[controller]', '[acrion]', [function]);
 *			$router->set('/', 'home', 'index');
 *			$router->set('/home/:action/:id', 'home', null, function($params){  });
 */
// Base url
$router->set('/', 'home');

// User edit
$router->set('/user/edit', 'user', 'edit');

// User :id
$router->set('/user/:id', 'user', 'show');

// Item :action
$router->set('/item/:action', 'item');

// Home :action :id
$router->set('/home/:action/:id', 'home', null, function($params)
{
	$route = [
		'controller' => $params['controller'],
		'action' => $params['action'],
		'id' => $params['id'],
	];
	return $route;
});

// :controller index
$router->set('/:controller');

// :controller :action :id
$router->set('/:controller/:action/:id');

// :controller :action
$router->set('/:controller/:action');

// not found
$router->set('/error', 404);
