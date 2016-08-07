<?php

/**
* AutoLoader class
*
* 
* @package    Deraemon/AutoLoader
* @category   Base
* @author     kohx by Deraemons
* @copyright  (c) 2015-2016 Deraemons
* @license    http://emon-cms.com/license
* 
*/

class AutoLoader {

	// Directory list
	private static $dirs = [];

	/**
	 * spl_autoload_register でこのメソッドを登録
	 * @param  string $class 名前空間など含んだクラス名
	 * @return bool 成功すればtrue
	 */
	public static function load_class($class)
	{
		// for child dirs
		$class_paths = explode('_', $class);

		// Create dirs
		$directories = self::directories();

		// When use sub directory
		if (count($class_paths) > 1)
		{
			// Get class name
			$class = ucfirst(array_pop($class_paths));

			// Iterate path
			foreach (self::directories() as $dir)
			{
				$directories[] = $dir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $class_paths);
			}
		}

		foreach ($directories as $directory)
		{
			// 名前空間や疑似名前空間をここでパースして適切なファイルパスにする			
			$file = $directory . DIRECTORY_SEPARATOR . $class . EXT;
			
			if (is_readable($file))
			{
				require $file;

				return true;
			}
		}
	}

	/**
	 * Set directories to dirs
	 * 
	 * @return array $dirs
	 */
	private static function directories()
	{
		if (empty(self::$dirs))
		{
			self::$dirs[] = PRIPATH . 'classes';
			self::$dirs[] = PRIPATH . 'vendor';
			self::$dirs[] = APPPATH . 'Model';
			self::$dirs[] = APPPATH . 'Table';
		}

		return self::$dirs;
	}

}

// Run auto load
spl_autoload_register(array('AutoLoader', 'load_class'));
