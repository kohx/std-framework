<?php

/**
 * Date class
 *
 * @package    Deraemon/Date
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

class Date {

	// Second amounts for various time increments
	const YEAR = 31556926;
	const MONTH = 2629744;
	const WEEK = 604800;
	const DAY = 86400;
	const HOUR = 3600;
	const MINUTE = 60;

	public static function format($str = 'now', $format = 'Y-m-d')
	{
		$date = new DateTime($str);
		return $date->format($format);
	}

}
