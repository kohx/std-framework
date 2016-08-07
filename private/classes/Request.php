<?php

/**
 * Request class
 *
 * @package    Deraemon/Request
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-20118 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

class Request {

	public static function _server($index = null, $default = null)
	{
		return (func_num_args() === 0) ? $_SERVER : Arr::get($_SERVER, strtoupper($index), $default);
	}

	/**
	 * Return's the protocol that the request was made with
	 *
	 * @return  string
	 */
	public static function protocol()
	{
		return (empty(static::_server('HTTPS')) ? 'http' : 'https');
	}

	/**
	 * Base path
	 *
	 * @return string
	 */
	public static function basepath()
	{
		$script_name = self::_server('SCRIPT_NAME');
		$request_uri = self::_server('REQUEST_URI');
		$basepath = '';

		// use index.php
		if (strpos($request_uri, $script_name) === 0)
		{
			$basepath = $script_name;
		}
		// not use index.php
		elseif (strpos($request_uri, dirname($script_name)) === 0)
		{
			$basepath = rtrim(dirname($script_name), '/');
		}

		return $basepath;
	}

	/**
	 * Pathinfo
	 * 
	 * @return stiring
	 */
	public static function pathinfo()
	{
		$base_url = self::basepath();
		$request_uri = self::_server('REQUEST_URI');

		// cat off query string
		if (($pos = strpos($request_uri, '?')) !== false)
		{
			$request_uri = substr($request_uri, 0, $pos);
		}

		// cut off baseurl
		$pathinfo = '/' . trim(substr($request_uri, strlen($base_url)), '/');

		return $pathinfo;
	}

	public static function baseurl($protocol = false)
	{
		$result = self::_server('SERVER_NAME') . self::basepath();
		return ($protocol) ? self::protocol() . '://' . $result : $result;
	}

	/**
	 * Query string
	 *
	 * @return  string
	 */
	public static function query_string($default = '')
	{
		return static::_server('QUERY_STRING', $default);
	}
	
	/**
	 * Merges the current GET parameters with an array of new or overloaded
	 * parameters and returns the resulting query string.
	 *
	 *     // Returns "?sort=title&limit=10" combined with any existing GET values
	 *     $query = URL::query(array('sort' => 'title', 'limit' => 10));
	 *
	 * Typically you would use this when you are sorting query results,
	 * or something similar.
	 *
	 * [!!] Parameters with a NULL value are left out.
	 *
	 * @param   array    $params   Array of GET parameters
	 * @param   boolean  $use_get  Include current request GET parameters
	 * @return  string
	 */
	public static function query(array $params = NULL, $use_get = TRUE)
	{
		if ($use_get)
		{
			if ($params === NULL)
			{
				// Use only the current parameters
				$params = $_GET;
			}
			else
			{
				// Merge the current and new parameters
				$params = Arr::merge($_GET, $params);
			}
		}

		if (empty($params))
		{
			// No query parameters
			return '';
		}

		// Note: http_build_query returns an empty string for a params array with only NULL values
		$query = http_build_query($params, '', '&');

		// Don't prepend '?' to an empty string
		return ($query === '') ? '' : ('?' . $query);
	}

	/**
	 * JAX request or not
	 *
	 * @return  bool
	 */
	public static function is_ajax()
	{
		return (static::_server('HTTP_X_REQUESTED_WITH') !== null) and strtolower(static::_server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
	}

	/**
	 * Get referrer
	 * 
	 *			$referrer = Request::referrer();
	 * 
	 * @param string $default When return in none get default
	 * @return string 
	 */
	public static function referrer($default = null)
	{
		return static::_server('HTTP_REFERER', $default);
	}

	public static function uri($str, $protocol = null)
	{
		$baseurl = static::baseurl($protocol);
		
		return "{$baseurl}/$str";
	}
	
	public static function current($protocol = null)
	{
		$current = static::_server('HTTP_HOST') . static::_server('REQUEST_URI');
		return ($protocol) ? self::protocol() . '://' . $current : $current;
	}

	/**
	 * User agent
	 * 
	 * @return string
	 */
	public static function user_agent()
	{
		$user_agent = self::_server('HTTP_USER_AGENT');
		
		return $user_agent;
	}

	/**
	 * accept_type
	 */
	public static function accept_type($type = NULL)
	{
		return self::_server('HTTP_ACCEPT');
	}

	/**
	 * accept_lang
	 */
	public static function accept_lang()
	{
		$locale = Locale::acceptFromHttp(self::_server('HTTP_ACCEPT_LANGUAGE'));
		return $locale;
	}

	/**
	 * accept encoding
	 */
	public static function accept_encoding()
	{
		return self::_server('HTTP_ACCEPT_ENCODING');
	}

	/**
	 * Method
	 * 
	 *			Request::method();			// get method name
	 *			Request::method('post');	// is post, return bool
	 * 
	 *			if use put and delete, write tag 
	 *			<input type="hidden" name="_method" value="put" />
	 *			can use 'POST', 'PUT', 'DELETE'.
	 * 
	 * @param POST|GET $method
	 * @param GET $default
	 * @return bool|string
	 */
	public static function method($method = null, $default = 'GET')
	{
		// Get method
		$request_method = self::_server('REQUEST_METHOD', $default);
		
		// Post has method
		if($request_method === 'POST')
		{
			$request_method = strtoupper(self::post('_method', 'post'));
			
			if(!in_array($request_method, ['POST', 'PUT', 'DELETE']))
			{
				$request_method = 'POST';
			}
		}

		// Has not method
		if (is_null($method))
		{
			return $request_method;
		}
		else
		{
			return (bool) ($request_method === strtoupper($method));
		}
	}

	/**
	 * Post
	 * 
	 * @param string $index
	 * @param string $default
	 * @return string|array
	 */
	public static function post($index = null, $default = null)
	{
		$post = $_POST;

		return (func_num_args() === 0) ? $post : Arr::get($post, $index, $default);
	}

	public static function get($index = null, $default = null)
	{
		$get = $_GET;

		return (is_null($index)) ? $get : Arr::get($get, $index, $default);
	}

	/**
	 * Fetch an item from the FILE array
	 *
	 * @param   string  $index The index key
	 * @param   string  $keyname When index file are plural, set keyname pulus index number
	 * @return  string|array
	 */
	public static function file($index, $keyname = 'id')
	{
		$files = $_FILES;
		$error = false;

		$file = Arr::get($files, $index);

		$error_count = count(Arr::get($file, 'error'));

		if ($error_count === 1)
		{
			return $file;
		}
		elseif ($error_count >= 1)
		{
			$files = Arr::rotete($file, $keyname);

			$error = false;
			foreach ($files as $file)
			{
				$error = !(isset($file['error']) AND is_int($file['error']));
			}

			if ($error === false)
			{
				return $files;
			}
		}
	}

}
