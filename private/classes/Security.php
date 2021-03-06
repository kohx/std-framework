<?php

/**
 * Security helper class.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
require_once 'AutoLoader.php';

class Security {

	/**
	 * @var  string  key name used for token storage
	 */
	public static $token_name = 'security_token';

	/**
	 * Generate and store a unique token which can be used to help prevent
	 * [CSRF](http://wikipedia.org/wiki/Cross_Site_Request_Forgery) attacks.
	 *
	 *     $token = Security::token();
	 *
	 * You can insert this token into your forms as a hidden field:
	 *
	 *     echo Form::hidden('csrf', Security::token());
	 *
	 * And then check it when using [Validation]:
	 *
	 *     $array->rules('csrf', array(
	 *         array('not_empty'),
	 *         array('Security::check'),
	 *     ));
	 *
	 * This provides a basic, but effective, method of preventing CSRF attacks.
	 *
	 * @param   boolean $new    force a new token to be generated?
	 * @return  string
	 * @uses    Session::instance
	 */
	public static function token($new = FALSE)
	{
		$session = Session::inst();
		
		// Get the current token
		$token = $session->get(Security::$token_name);

		if ($new === TRUE OR ! $token)
		{
			// Generate a new unique token
			if (function_exists('openssl_random_pseudo_bytes'))
			{
				// Generate a random pseudo bytes token if openssl_random_pseudo_bytes is available
				// This is more secure than uniqid, because uniqid relies on microtime, which is predictable
				$token = base64_encode(openssl_random_pseudo_bytes(32));
			}
			else
			{
				// Otherwise, fall back to a hashed uniqid
				$token = sha1(uniqid(NULL, TRUE));
			}

			// Store the new token
			$session->set(Security::$token_name, $token);
		}

		return $token;
	}

	/**
	 * Check that the given token matches the currently stored security token.
	 *
	 *     if(!Security::check())
	 *		or
	 *     if(!Security::check(_csrf))
	 *     {
	 *         throw new Exception('not found.');
	 *     }
	 * 
	 * @param string $token default '_csrf'
	 * @return boolean
	 */
	public static function check($token = '_csrf')
	{
		
		return Security::slow_equals(Security::token(), Request::post($token));
	}

	/**
	 * Compare two hashes in a time-invariant manner.
	 * Prevents cryptographic side-channel attacks (timing attacks, specifically)
	 * 
	 * @param string $a cryptographic hash
	 * @param string $b cryptographic hash
	 * @return boolean
	 */
	public static function slow_equals($a, $b)
	{
		$diff = strlen($a) ^ strlen($b);
		for ($i = 0; $i < strlen($a) AND $i < strlen($b); $i++)
		{
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $diff === 0;
	}

	/**
	 * Remove image tags from a string.
	 *
	 *     $str = Security::strip_image_tags($str);
	 *
	 * @param   string  $str    string to sanitize
	 * @return  string
	 */
	public static function strip_image_tags($str)
	{
		return preg_replace('#<img\s.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '$1', $str);
	}

	/**
	 * Encodes PHP tags in a string.
	 *
	 * 		$str = Security::encode_php_tags($str);
	 * 
	 * 		許可する場合は strip_tags($input, '<br>');
	 * 		http://php.net/manual/ja/function.strip-tags.php
	 *
	 * @param   string  $str    string to sanitize
	 * @return  string
	 */
	public static function encode_php_tags($str)
	{
		return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
	}

	/**
	 * Recursively sanitizes an input variable:
	 *
	 * - Strips slashes if magic quotes are enabled
	 * - Normalizes all newlines to LF
	 *
	 * @param   mixed   $value  any variable
	 * @return  mixed   sanitized variable
	 */
	public static function sanitize($value)
	{
		if (is_array($value) OR is_object($value))
		{
			foreach ($value as $key => $val)
			{
				// Recursively clean each value
				$value[$key] = self::sanitize($val);
			}
		}
		elseif (is_string($value))
		{
			// Determine if the extremely evil magic quotes are enabled
			$magic_quotes = (bool) get_magic_quotes_gpc();
			if ($magic_quotes === TRUE)
			{
				// Remove slashes added by magic quotes
				$value = stripslashes($value);
			}

			if (strpos($value, "\r") !== FALSE)
			{
				// Standardize newlines
				$value = str_replace(array("\r\n", "\r"), "\n", $value);
			}
		}

		return $value;
	}

}
