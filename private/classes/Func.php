<?php

/**
 * Template Functions
 *
 * @package    Deraemon/Tamplate Functions
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2018 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

class Func {

	public static function e($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	public static function l($string, $limit = 100, $end_char = null, $preserve_words = false)
	{
		$result = self::limit_chars($string, $limit, $end_char, $preserve_words);

		echo htmlspecialchars($result);
	}

	public static function p($string, $br = true)
	{
		$result = self::auto_p($string, $br);

		echo $result;
	}

	public static function lp($string, $limit = 100, $end_char = null, $preserve_words = false, $br = true)
	{
		$limited = self::limit_chars($string, $limit, $end_char, $preserve_words);
		$result = self::auto_p($limited, $br);

		echo $result;
	}

	/**
	 * Limits a phrase to a given number of characters.
	 *
	 *     $text = Text::limit_chars($text);
	 *
	 * @param   string  $string            phrase to limit characters of
	 * @param   integer $limit          number of characters to limit to
	 * @param   string  $end_char       end character or entity
	 * @param   boolean $preserve_words enable or disable the preservation of words while limiting
	 * @return  string
	 * @uses    UTF8::strlen
	 */
	public static function limit_chars($string, $limit = 100, $end_char = null, $preserve_words = false)
	{
		$end_char = ($end_char === null) ? '…' : $end_char;

		$limit = (int) $limit;

		if (trim($string) === '' OR strlen($string) <= $limit)
			return $string;

		if ($limit <= 0)
			return $end_char;

		if ($preserve_words === false)
			return rtrim(substr($string, 0, $limit)) . $end_char;

		// Don't preserve words. The limit is considered the top limit.
		// No strings with a length longer than $limit should be returned.
		if (!preg_match('/^.{0,' . $limit . '}\s/us', $string, $matches))
			return $end_char;

		return rtrim($matches[0]) . ((strlen($matches[0]) === strlen($string)) ? '' : $end_char);
	}

	/**
	 * Automatically applies "p" and "br" markup to text.
	 * Basically [nl2br](http://php.net/nl2br) on steroids.
	 *
	 *     echo Text::auto_p($text);
	 *
	 * [!!] This method is not foolproof since it uses regex to parse HTML.
	 *
	 * @param   string  $string    subject
	 * @param   boolean $br     convert single linebreaks to <br />
	 * @return  string
	 */
	public static function auto_p($string, $br = true)
	{
		// Trim whitespace
		if (($string = trim($string)) === '')
			return '';

		// Standardize newlines
		$string = str_replace(array("\r\n", "\r"), "\n", $string);

		// Trim whitespace on each line
		$string = preg_replace('~^[ \t]+~m', '', $string);
		$string = preg_replace('~[ \t]+$~m', '', $string);

		// The following regexes only need to be executed if the string contains html
		if ($html_found = (strpos($string, '<') !== false))
		{
			// Elements that should not be surrounded by p tags
			$no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';

			// Put at least two linebreaks before and after $no_p elements
			$string = preg_replace('~^<' . $no_p . '[^>]*+>~im', "\n$0", $string);
			$string = preg_replace('~</' . $no_p . '\s*+>$~im', "$0\n", $string);
		}

		// Do the <p> magic!
		$string = '<p>' . trim($string) . '</p>';
		$string = preg_replace('~\n{2,}~', "</p>\n\n<p>", $string);

		// The following regexes only need to be executed if the string contains html
		if ($html_found !== false)
		{
			// Remove p tags around $no_p elements
			$string = preg_replace('~<p>(?=</?' . $no_p . '[^>]*+>)~i', '', $string);
			$string = preg_replace('~(</?' . $no_p . '[^>]*+>)</p>~i', '$1', $string);
		}

		// Convert single linebreaks to <br />
		if ($br === true)
		{
			$string = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $string);
		}

		return $string;
	}

	public static function token($name = '_csrf')
	{

		$token = Security::token();
		echo '<input type="hidden" name="' . $name . '" value="' . $token . '" />';
	}

	public static function rest($method)
	{

		echo '<input type="hidden" name="_method" value="' . $method . '" />';
	}

}
