<?php

/**
 * Router class
 * 
 * @package    Deraemon/Router
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-20118 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

class Router {

	// Instance
	public static $instance;
	// routes
	protected $_route_strings = [];
	// compiled routes
	protected $_routes = [];

	/**
	 * Instanse 
	 * 
	 * @param array $routes
	 * @return \Router
	 */
	public static function inst($routes = [])
	{
		if (is_null(static::$instance))
		{
			static::$instance = new Router($routes);
		}

		return static::$instance;
	}

	/**
	 * Construct
	 * 
	 * @param array $routes
	 */
	public function __construct($routes = [])
	{
		// When has toutes
		if ($routes)
		{
			// Set routes
			foreach ($routes as $url => $route)
			{
				$controller = isset($route['controller']) ? $route['controller'] : null;
				$action = isset($route['action']) ? $route['action'] : null;
				$func = isset($route['func']) ? $route['func'] : null;

				$this->set($url, $controller, $action, $func);
			}
		}
	}

	/**
	 * Set
	 * 
	 * @param string $url
	 * @param string $controller
	 * @param string $action
	 */
	public function set($url, $controller = null, $action = null, callable $func = null)
	{
		if (strlen($url) > 1 AND substr($url, -1) === '/')
		{
			$url = rtrim($url, '/');
		}

		// Create route strings array and set url
		$this->_route_strings[$url]['url'] = $url;

		// has controller
		if (!is_null($controller))
		{
			$this->_route_strings[$url]['controller'] = $controller;
		}

		// has action
		if (!is_null($action))
		{
			$this->_route_strings[$url]['action'] = $action;
		}

		// has func
		if (!is_null($func))
		{
			$this->_route_strings[$url]['func'] = $func;
		}

		return $this;
	}

	/**
	 * Compaile route_strings
	 * 
	 * @return void
	 */
	protected function _compile()
	{
		// Iterate route strings
		foreach ($this->_route_strings as $url => $params)
		{
			// When can't get route to compailed routes
			if ($url === '')
			{
				// Set direct
				$this->_routes[$url] = $params;
			}
			else
			{
				$tokens = explode('/', ltrim($url, '/'));
				foreach ($tokens as $i => $token)
				{
					// has param
					if (0 === strpos($token, ':'))
					{
						$name = substr($token, 1);
						$token = '(?P<' . $name . '>[^/]+)';
					}
					$tokens[$i] = $token;
				}
				$pattern = '/' . implode('/', $tokens);

				// Set patarn and params to compailed routes
				$this->_routes[$pattern] = $params;
			}
		}
	}

	/**
	 * Get
	 * 
	 * @param string $pathinfo
	 * @return array $result
	 */
	public function get($pathinfo)
	{
		// Compaile route strings
		$this->_compile();

		// Declare result
		$result = [];

		// When has / on end
		if ('/' !== substr($pathinfo, 0, 1))
		{
			$pathinfo = '/' . $pathinfo;
		}

		// Iterate compailed routes
		foreach ($this->_routes as $pattern => $params)
		{
			// Set params to result
			$result = $params;

			// Declare matches
			$matches = [];

			// When has matches
			if (preg_match('#^' . $pattern . '$#', $pathinfo, $matches))
			{
				foreach ($matches as $key => $value)
				{
					// When not number set to result
					if (!is_numeric($key))
					{
						$result[$key] = $value;
					}
				}

				break;
			}
			else
			{
				// when not hit, set pathinfo to params url and pass to result
				$params['url'] = $pathinfo;
				$result = $params;
			}
		}

		// When result has func
		if (isset($result['func']))
		{
			$temp = $result;
			$func = $temp['func'];
			unset($temp['func']);

			// Run callback
			$result = call_user_func($func, $temp);

			if (!$result)
			{
				unset($temp['url']);
				$result = $temp;
			}
		}

		return $result;
	}

}
