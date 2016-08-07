<?php
/**
 * Debug
 * 
 * @package    Deraemon/Debug
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
class Debug {

	public static function v($value)
	{
		$bt = debug_backtrace();
		$file = $bt[0]['file'];
		$line = $bt[0]['line'];
		
		echo "$file\n$line\n";
		var_dump($value);
	}
	public static function p($value)
	{
		$bt = debug_backtrace();
		$file = $bt[0]['file'];
		$line = $bt[0]['line'];
		
		echo "$file\n$line\n";
		echo '<pre>';
		print_r($value);
		echo '</pre>';
	}

}
