<?php

/**
 * using the [Mcrypt](http://php.net/mcrypt)
 *
 * Key: secret passphrase that is used for encoding and decoding
 * Cipher:  http://php.net/mcrypt.ciphers
 * Mode: http://php.net/mcrypt.constants
 *
 * @package    Deraemon/Email
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

class Encrypt {

	public static $default = 'default';
	public static $instances = array();
	protected static $_rand = MCRYPT_DEV_URANDOM;
	protected $_key;
	protected $_mode;
	protected $_cipher;
	protected $_iv_size;

	/**
	 *     $encrypt = Encrypt::instance();
	 *
	 * @param   string  $name   configuration group name
	 * @return  Encrypt
	 */
	public static function inst($name = NULL)
	{
		if ($name === NULL)
		{
			// Use the default instance name
			$name = static::$default;
		}

		if (!isset(static::$instances[$name]))
		{
			// Load the configuration data
			$config = Config::fact('encrypt')->get($name);

			if (!isset($config['key']))
			{
				// No default encryption key is provided!
				throw new Exception('No encryption key is defined in the encryption configuration');
			}

			if (!isset($config['mode']))
			{
				// Add the default mode
				$config['mode'] = MCRYPT_MODE_NOFB;
			}

			if (!isset($config['cipher']))
			{
				// Add the default cipher
				$config['cipher'] = MCRYPT_RIJNDAEL_128;
			}

			// Create a new instance
			static::$instances[$name] = new Encrypt($config['key'], $config['mode'], $config['cipher']);
		}

		return static::$instances[$name];
	}

	/**
	 * Creates a new mcrypt wrapper.
	 *
	 * @param   string  $key    encryption key
	 * @param   string  $mode   mcrypt mode
	 * @param   string  $cipher mcrypt cipher
	 */
	public function __construct($key, $mode, $cipher)
	{
		// Find the max length of the key, based on cipher and mode
		$size = mcrypt_get_key_size($cipher, $mode);

		if (isset($key[$size]))
		{
			// Shorten the key to the maximum size
			$key = substr($key, 0, $size);
		}
		else if (version_compare(PHP_VERSION, '5.6.0', '>='))
		{
			$key = $this->_normalize_key($key, $cipher, $mode);
		}

		// Store the key, mode, and cipher
		$this->_key = $key;
		$this->_mode = $mode;
		$this->_cipher = $cipher;

		// Store the IV size
		$this->_iv_size = mcrypt_get_iv_size($this->_cipher, $this->_mode);
	}

	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *
	 *     $data = $encrypt->encode($data);
	 *
	 * @param   string  $data
	 * @return  string
	 */
	public function encode($data)
	{
		// Get an initialization vector
		$iv = $this->_create_iv();

		// Encrypt the data using the configured options and generated iv
		$data_encrypted = mcrypt_encrypt($this->_cipher, $this->_key, $data, $this->_mode, $iv);

		// Use base64 encoding to convert to a string
		return base64_encode($iv . $data_encrypted);
	}

	/**
	 *     $data = $encrypt->decode($data);
	 *
	 * @param   string  $data   encoded string to be decrypted
	 * @return  FALSE   if decryption fails
	 * @return  string
	 */
	public function decode($data_encode64)
	{
		// Convert the data back to binary
		$data_encode = base64_decode($data_encode64, TRUE);

		if (!$data_encode)
		{
			// Invalid base64 data
			return FALSE;
		}

		// Extract the initialization vector from the data
		$iv = substr($data_encode, 0, $this->_iv_size);

		if ($this->_iv_size !== strlen($iv))
		{
			// The iv is not the expected size
			return FALSE;
		}

		// Remove the iv from the data
		$data_encode_univ = substr($data_encode, $this->_iv_size);

		// Return the decrypted data, trimming the \0 padding bytes from the end of the data
		return rtrim(mcrypt_decrypt($this->_cipher, $this->_key, $data_encode_univ, $this->_mode, $iv), "\0");
	}

	/**
	 * Proxy for the mcrypt_create_iv function - to allow mocking and testing against KAT vectors
	 *
	 * @return string the initialization vector or FALSE on error
	 */
	protected function _create_iv()
	{
		/*
		 * Silently use MCRYPT_DEV_URANDOM when the chosen random number generator
		 * is not one of those that are considered secure.
		 *
		 * Also sets Encrypt::$_rand to MCRYPT_DEV_URANDOM when it's not already set
		 */
		if ((Encrypt::$_rand !== MCRYPT_DEV_URANDOM) AND ( Encrypt::$_rand !== MCRYPT_DEV_RANDOM))
		{
			Encrypt::$_rand = MCRYPT_DEV_URANDOM;
		}

		// Create a random initialization vector of the proper size for the current cipher
		return mcrypt_create_iv($this->_iv_size, Encrypt::$_rand);
	}

	/**
	 * Normalize key for PHP 5.6 for backwards compatibility
	 *
	 * This method is a shim to make PHP 5.6 behave in a B/C way for
	 * legacy key padding when shorter-than-supported keys are used
	 *
	 * @param   string  $key    encryption key
	 * @param   string  $cipher mcrypt cipher
	 * @param   string  $mode   mcrypt mode
	 */
	protected function _normalize_key($key, $cipher, $mode)
	{
		// open the cipher
		$td = mcrypt_module_open($cipher, '', $mode, '');

		// loop through the supported key sizes
		foreach (mcrypt_enc_get_supported_key_sizes($td) as $supported)
		{
			// if key is short, needs padding
			if (strlen($key) <= $supported)
			{
				return str_pad($key, $supported, "\0");
			}
		}

		// at this point key must be greater than max supported size, shorten it
		return substr($key, 0, mcrypt_get_key_size($cipher, $mode));
	}

}
