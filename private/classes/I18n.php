<?php

/**
 * Internationalization (I18n) class.
 *
 *     // Display a translated message
 *     echo __('Hello, world');
 *
 *     // With parameter replacement
 *     echo __('Hello, :user', array(':user' => $username));
 *
 * @package    Deraemon/I18n
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
// Here:: これを使ってるか各クラスをチェックする
class I18n {

	/**
	 * @var string target language: en-us, es-es, zh-cn, etc
	 */
	public static $lang = 'en-us';

	/**
	 * @var  array  cache of loaded languages
	 */
	protected static $_loaded = array();

	/**
	 * 
	 * @param string $lang
	 * @return string
	 */
	public static function normalize($lang)
	{
		return strtolower(str_replace(array(' ', '_'), '-', $lang));
	}

	/**
	 * Get and set the target language.
	 *
	 *     // Get the current language
	 *     $lang = I18n::lang();
	 *
	 *     // Change the current language to Spanish
	 *     I18n::lang('es-es');
	 *
	 * @param   string  $lang   new language setting
	 * @return  string
	 */
	public static function lang($lang = NULL)
	{
		if ($lang)
		{
			// Normalize the language
			static::$lang = static::normalize($lang);
		}

		return static::$lang;
	}

	/**
	 * $hello = I18n::get('Hello friends, my name is :name');
	 *
	 * @param   string  $string text to translate
	 * @param   string  $lang   target language
	 * @return  string
	 */
	public static function get($string, $lang = NULL)
	{
		if (!$lang)
		{
			// Use the global target language
			$lang = I18n::$lang;
		}

		// Load the translation table for this language
		$table = I18n::load($lang);

		// Return the translated string if it exists
		return isset($table[$string]) ? $table[$string] : $string;
	}

	/**
	 * Returns the translation table for a given language.
	 *
	 *     $messages = I18n::load('ja');
	 *
	 * @param   string  $lang   language to load
	 * @return  array
	 */
	public static function load($lang)
	{
		$target_lang = static::normalize($lang);

		// If aleady loaded return that;
		if (isset(I18n::$_loaded[$target_lang]))
		{
			return I18n::$_loaded[$target_lang];
		}

		$file = APPPATH . 'lang' . DIRECTORY_SEPARATOR . $lang . EXT;

		if (is_file($file))
		{
			static::$_loaded[$target_lang] = include $file;
			static::$lang = $target_lang;
		}
	}

}

if (!function_exists('__'))
{

	/**
	 * translation/internationalization function. The PHP function
	 *
	 * @uses    I18n::get
	 * @param   string  $string text to translate
	 * @param   array   $values values to replace in the translated text
	 * @param   string  $lang   source language
	 * @return  string
	 */
	function __($string, array $values = [], $lang = 'en')
	{
		if (I18n::normalize($lang) !== I18n::$lang)
		{
			// The message and target languages are different
			// Get the translation for this message
			$string = I18n::get($string);
		}
				
		return empty($values) ? $string : str_replace(array_keys($values), array_values($values), $string);
	}

}
