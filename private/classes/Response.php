<?php

/**
 * Response class
 * 
 * @package    Deraemon/Response
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-20118 Deraemons
 * @license    http://emon-cms.com/license
 */
class Response {

	/**
	 * @var  array  An array of status codes and messages
	 *
	 * See http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 * for the complete and approved list, and links to the RFC's that define them
	 */
	protected $_route;
	protected $_controller_short;
	protected $_controller_full;
	protected $_action_short;
	protected $_action_prefix;
	protected $_action_full;
	protected $_default_method = 'index';
	protected $_controller_instance;

	/**
	 * Factory
	 * 
	 * 			Response::fact();
	 * 
	 * @return \Response
	 */
	public static function fact()
	{
		return new Response();
	}

	/**
	 * Constructor
	 */
	function __construct()
	{
		/*
		 * Get pathinfo from Request class
		 */
		$path = Request::pathinfo();

		/*
		 * Get route array from Router class
		 */
		$this->_route = Router::inst()->get($path);

		/*
		 * Get http method
		 */
		$http_method = Request::method();

		$action_prefix = strtolower($http_method) . '_';

		$this->_action_prefix = $action_prefix;

		/*
		 * Create controller string
		 */
		$this->_controller_short = ucfirst(Arr::get($this->_route, 'controller'));
		$this->_controller_full = 'Controller_' . $this->_controller_short;

		/*
		 * Create action string when there is not action, set default method 
		 */
		$this->_action_short = Arr::get($this->_route, 'action', $this->_default_method);
		$this->_action_full = $action_prefix . $this->_action_short;
	}

	/**
	 * Execute
	 * 
	 * @throws Http404Exception
	 */
	public function execute()
	{
		try
		{
			// Get controller file
			$controller_file = APPPATH . 'Controller' . DIRECTORY_SEPARATOR . $this->_controller_short . EXT;

			// Check controller class
			if (!($this->_controller_full AND is_readable($controller_file)))
			{
				throw new Http404Exception('Route not found controller ' . $this->_controller_full);
			}

			// If there is file require, because controller not include autoloader, that is for model
			require $controller_file;

			// Check action
			if (!($this->_action_full AND method_exists($this->_controller_full, $this->_action_full)))
			{
				throw new Http404Exception('Route not found action ' . $this->_action_full);
			}

			/*
			 * Call Controller and run Action
			 */
			$this->_controller_instance = new $this->_controller_full();

			//Set controller
			$this->_controller_instance->controller = $this->_controller_short;

			// Set action
			$this->_controller_instance->action = $this->_action_short;
			$this->_controller_instance->method = $this->_action_prefix;

			// Run action method
			$this->_controller_instance->{$this->_action_short}($this->_route);

			/*
			 * Send header
			 */

			// Get protocol
			$protocol = Request::_server('SERVER_PROTOCOL') ? Request::_server('SERVER_PROTOCOL') : 'HTTP/1.1';

			// Get status
			$status_num = $this->_controller_instance->status;
			$status_str = Config::fact('Response')->get('statas.' . $status_num);
			
			// Headers
			header($protocol . ' ' . $status_num . ' ' . $status_str);

			$headers = $this->_controller_instance->headers;
			foreach ($headers as $name => $value)
			{
				// Parse non-replace headers
				if (is_int($name) and is_array($value))
				{
					isset($value[0]) and $name = $value[0];
					isset($value[1]) and $value = $value[1];
				}

				// Create the header
				is_string($name) and $value = "{$name}: {$value}";

				// Send it
				header($value, true);
			}
			
			/*
			 * Render view
			 */
			echo $this->_controller_instance->view->render();
		}
		catch (Http404Exception $e)
		{

			echo $e->getCode() . ' ' . $e->getFile() . ' ' . $e->getFile() . ' <br>' . $e->getMessage();
//			$this->redirect('error/404', 404);
		}
		catch (Exception $e)
		{

			echo $e->getCode() . ' ' . $e->getFile() . ' ' . $e->getFile() . ' <br>' . $e->getMessage();
//			$this->redirect('error/500', 500);
		}
	}

	/**
	 * Redirect
	 * @param   string  $url     The url
	 * @param   int     $code    The redirect status code
	 * @param   string  $method  location|refresh
	 *
	 * @return  void
	 */
	public static function redirect($url = '', $code = 302, $method = 'location')
	{
		if (strpos($url, 'http://') !== false OR strpos($url, 'https://') !== false)
		{
			$location = $url;
		}
		else
		{
			$protocol = Request::_server('SERVER_PROTOCOL') ? Request::_server('SERVER_PROTOCOL') : 'HTTP/1.1';
			header($protocol . ' ' . $code . ' ' . static::$statuses[$code]);

			$location = Request::protocol() . '://' . str_replace('//', '/', Request::baseurl() . '/' . rtrim($url, '/'));
		}

		header("$method: $location");

		exit;
	}

}
