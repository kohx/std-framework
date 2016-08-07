<?php

/**
 * Notice class
 *
 * @package    Deraemon/Notice
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2018 Deraemons
 * @license    http://emon-cms.com
 */
require_once 'AutoLoader.php';

class Notice {

	//types
	const ERROR = 'error';
	const WARNING = 'warning';
	const VALIDATION = 'validation';
	const INFO = 'information';
	const SUCCESS = 'success';

	/**
	 * Add
	 * 
	 *			Notice::add(Notice::SUCCESS, __('success'));
	 *			Notice::add(Notice::VALIDATION, __('title'), $validation->errors());
	 * 
	 * @param string $type
	 * @param string $title
	 * @param Validation or array $messages
	 */
	public static function add($type, $title, $messages = array())
	{
		// Message list array
		$message_list = [];

		/*
		 * Set messages and translate
		 */
		if ($messages instanceof Validation)
		{
			foreach ($messages->error() as $message)
			{
				$message_list[] = $message->get_message();
			}
		}

		if (is_array($messages))
		{
			foreach ($messages as $message)
			{
				$message_list[] = $message;
			}
		}

		if (is_string($messages))
		{
			$message_list[] = $messages;
		}

		// Get session
		$notice = Session::inst()->get('notice', array());

		$notice[] = array(
			'type' => $type,
			'title' => $title,
			'messages' => $message_list,
		);

		// Set session
		Session::inst()->set('notice', $notice);
	}

	/**
	 * Clear
	 * 
	 * @param string $type
	 */
	public static function clear($type = NULL)
	{
		$notices = Session::get('notices');

		if ($type === NULL)
		{
			$notices = array();
		}
		else
		{
			foreach ($notices as $key => $value)
			{
				if ($value['type'] == $type)
				{
					unset($notices[$key]);
				}
			}
		}

		Session::set('notices', $notices);
	}

	/**
	 * Render
	 * 
	 * 			   $notice = Notice::render();
	 * 			or $notice = Notice::render(Notice::SUCCESS); Render only choiced type
	 * 
	 * @param string $type
	 * @return object View
	 */
	public static function render($type = NULL)
	{
		// Get notice from session
		$notice = Session::inst()->get('notice', array());

		$rendereds = array();

		// Filter type and delete it
		if (!$type)
		{
			$rendereds = $notice;
			
			// Delete notice from session
			Session::inst()->delete('notice');
		}
		else
		{
			// Delete this type from session notice
			foreach ($notice as $key => $value)
			{
				if ($value['type'] == $type)
				{
					$rendereds[] = $value;
					unset($notice[$key]);
				}
			}
		}
		
		$results = '';

		// Render each notice and set results
		foreach ($rendereds as $rendered)
		{
			$results .= static::_build($rendered);
		}

		return $results;
	}

	/**
	 * Build html
	 * 
	 * @param array $params
	 * @return type
	 */
	protected static function _build($params)
	{		
		$config = Config::fact('notice');
		$view = Wavebar::fact($config->get('file'), $config->get('path'))
				->set('title', Arr::get($params, 'title'))
				->set('type', Arr::get($params, 'type'))
				->set('messages', Arr::get($params, 'messages'))
				->render();
				
		return $view;
	}

}
