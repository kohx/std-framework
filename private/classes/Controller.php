<?php

/**
 * Controll class
 *
 * @package    Deraemon/Controll
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
abstract class Controller {

	public $controller;
	public $action;
	public $method;
	public $headers = [];
	public $status = 200;
	public $view = null;

	/**
	 * __call
	 * 
	 * @param string $name
	 * @param array $arguments
	 * @return action class instance
	 */
	public function __call($name, $arguments)
	{
		$action_func = $this->method . $name;
		$this->before();
		if (method_exists($this, $action_func))
		{
			$this->$action_func($arguments);
		}
		$this->after();
	}

	/**
	 * Beforf
	 */
	public function before()
	{

		// before any methods
	}

	/**
	 * After
	 */
	public function after()
	{
		// This instanse pass to Response
		return $this;

		// fter any methods
	}

	/**
	 * get route controller
	 * 
	 * 			$this->get_controller();
	 * 
	 * @return string
	 */
	public function get_controller()
	{
		return strtolower($this->controller);
	}

	/**
	 * get route action
	 * 
	 * 			$this->get_action();
	 * 
	 * @return string
	 */
	public function get_action()
	{
		return $this->action;
	}

	/**
	 * Adds a header to the queue
	 * 
	 * 			$this->set_header('Content-Type', 'application/json; charset=utf-8');
	 * 			//header("Content-Type: application/json; charset=utf-8");
	 *
	 * 			$this->set_header('Content-Type', 'text/xml');
	 * 			$this->set_header('Content-Type', 'text/csv; charset=Shift_JIS');
	 *
	 * 			$this->set_header('Content-Disposition', 'attachment; filename=hoge.csv');
	 * 			$this->set_header('Content-Type', 'image/png');
	 *
	 * @param   string  The header name
	 * @param   string  The header value
	 * @param   string  Whether to replace existing value for the header, will never overwrite/be overwritten when false
	 *
	 * @return  Response
	 */
	public function set_header($name, $value, $replace = true)
	{
		if ($replace)
		{
			$this->headers[$name] = $value;
		}
		else
		{
			$this->headers[] = array($name, $value);
		}

		return $this;
	}

	/**
	 * Sets the response status code
	 *
	 * @param   string  $status  The status code
	 *
	 * @return  Response
	 */
	public function set_status($status = 200)
	{
		$this->status = $status;
		return $this;
	}

}
