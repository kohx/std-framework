<?php

/**
 * Message class
 *
 * @package    Deraemon/Message
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

class Message {

	/**
	 * message from file
	 * 
	 *			   Message::bring('default'); Get all line
	 *			or Message::bring('default', 'success');
	 *			or Message::bring('default', 'success', 'default');
	 *			or Message::bring('default', 'success', 'default', false);
	 * 
	 * @staticvar array $messages
	 * @param string $file
	 * @param string $path
	 * @param string $default
	 * @param  bool $translate
	 * @return string
	 */
	public static function bring($file, $path = null, $default = null)
	{
		// Declare messages static valiable
		static $messages = [];

		if (!isset($messages[$file]))
		{
			// Create a new message list
			$messages[$file] = [];

			// Get message directory path
			$file_path = APPPATH . 'message' . DIRECTORY_SEPARATOR . $file . EXT;

			// If there is file, include and set into file name of message
			if (is_file($file_path))
			{
				$messages[$file] = include $file_path;
			}
		}

		if ($path === NULL)
		{
			// Return all of the messages
			return $messages[$file];
		}
		else
		{
			// Get a message using the path
			return Arr::path($messages[$file], $path, $default);
		}
	}

}
