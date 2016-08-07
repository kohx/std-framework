<?php

/**
 * Config class
 *
 * @package    Deraemon/Config
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

class Config {

	protected $_config_dir;
	protected $_values;

	/**
	 * Factory
	 * 
	 * @param string $file
	 * @return \Config
	 */
	public static function fact($file)
	{
		return new Config($file);
	}

	/**
	 * Constructers
	 * 
	 * @param Config
	 */
	public function __construct($file)
	{
		$this->config_dir = APPPATH . 'config/';

		$this->_values = include $this->config_dir . $file . '.php';
	}

	/**
	 * Get config data
	 * 
	 * @param string $key_str
	 * @param string $default
	 * @return string
	 */
	public function get($key_str = null, $default = null)
	{
		if ($key_str)
		{
			$result = Arr::path($this->_values, $key_str, $default);
		}
		else
		{
			$result = $this->_values;
		}

		return $result;
	}

}
