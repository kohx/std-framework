<?php

/**
 * Cookie class.
 *
 * @package    Deraemon/Cookie
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

class Cookie {

	public static $_salt = NULL;
	public static $_expire = 0;
	public static $_path = '/';
	public static $_domain = NULL;
	public static $_secure = FALSE;
	public static $_httponly = FALSE;

	/**
	 *     Cookie::set('theme', 'red');
	 *
	 * @param   string  $name       name of cookie
	 * @param   string  $value      value of cookie
	 * @param   integer $lifetime   lifetime in seconds
	 * @return  boolean
	 * @uses    Cookie::salt
	 */
	public static function set($name, $value, $lifetime = NULL)
	{
		// Get config
		$config = Config::fact('cookie');

		// Set lifetime
		if ($lifetime === NULL)
		{
			$lifetime = $config->get('lifetime');
		}

		if ($lifetime !== 0)
		{
			// The expiration is expected to be a UNIX timestamp
			$lifetime += self::_time();
		}

		// Add the salt to the cookie value
		$value = static::salt($name, $value) . '~' . $value;

		$_COOKIE[$name] = $value;
		return setcookie($name, $value, $lifetime, static::$_path, static::$_domain, static::$_secure, static::$_httponly);
	}

	/**
	 *     $theme = Cookie::get('theme', 'blue');
	 *
	 * @param   string  $key        cookie name
	 * @param   mixed   $default    default value to return
	 * @return  string
	 */
	public static function get($key, $default = NULL)
	{
		if (!isset($_COOKIE[$key]))
		{
			// The cookie does not exist
			return $default;
		}

		// Get the cookie value
		$cookie_value = $_COOKIE[$key];

		// Get cookie solt string length
		$split = strlen(Cookie::salt($key, NULL));

		if (isset($cookie_value[$split]) AND $cookie_value[$split] === '~')
		{
			// Separate the salt and the value
			list ($hash, $value) = explode('~', $cookie_value, 2);

			if (Security::slow_equals(Cookie::salt($key, $value), $hash))
			{
				// Cookie signature is valid
				return $value;
			}

			// The cookie signature is invalid, delete it
			static::delete($key);
		}

		return $default;
	}

	/**
	 *     Cookie::delete('theme');
	 *
	 * @param   string  $name   cookie name
	 * @return  boolean
	 */
	public static function delete($name)
	{
		// Remove the cookie
		unset($_COOKIE[$name]);

		// Nullify the cookie and make it expire
		return setcookie($name, NULL, -86400, Cookie::$_path, Cookie::$_domain, Cookie::$_secure, Cookie::$_httponly);
	}

	/**
	 *			$salt = Cookie::salt('theme', 'red');
	 *
	 * @param   string $name name of cookie
	 * @param   string $value value of cookie
	 *
	 * @throws Exception if Cookie::$salt is not configured
	 * @return  string
	 */
	public static function salt($name, $value)
	{
		if (!static::$_salt)
		{
			static::$_salt = Config::fact('cookie')->get('salt');
		}

		// Require a valid salt
		if (!static::$_salt)
		{
			throw new Exception('A valid cookie salt is required. Please set salt in cookie config');
		}

		// Determine the user agent
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

		return hash_hmac('sha1', $agent . $name . $value . static::$_salt, static::$_salt);
	}

	/**
	 * Proxy for the native time function - to allow mocking of time-related logic in unit tests
	 *
	 * @return int
	 * @see    time
	 */
	protected static function _time()
	{
		return time();
	}

}
