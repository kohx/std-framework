<?php

/**
 * Upload helper class for working with uploaded files and [Validation].
 *
 *     $array = Validation::factory($_FILES);
 *
 * [!!] Remember to define your form with "enctype=multipart/form-data" or file
 * uploading will not work!
 *
 * The following configuration properties can be set:
 *
 * - [Upload::$remove_spaces]
 * - [Upload::$default_directory]
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
require_once 'AutoLoader.php';

//define ('UPLOAD_ERR_OK', 0);
//define ('UPLOAD_ERR_INI_SIZE', 1);

class Upload {

	/**
	 * Save an uploaded file to a new location. If no filename is provided,
	 * the original filename will be used, with a unique prefix added.
	 *
	 * This method should be used after validating the $_FILES array:
	 *
	 *     if ($array->check())
	 *     {
	 *         // Upload is valid, save it
	 *            Upload::save($array['file']);
	 *         or Upload::save($array['file'], directory, fimename);
	 *     }
	 *
	 * @param   array   $file       uploaded file data
	 * @param   string  $directory  new directory
	 * @param   string  $filename   new filename
	 * @param   integer $chmod      chmod mask
	 * @return  string  on success, full path to new file
	 * @return  false   on failure
	 */
	public static function save(array $file, $directory, $filename = null, $chmod = 0644)
	{
		if (!isset($file['tmp_name']) OR ! is_uploaded_file($file['tmp_name']))
		{
			// Ignore corrupted uploads
			return false;
		}

		// Set filename
		if (is_null($filename))
		{
			// Use the default filename, with a timestamp pre-pended
			$ext = File::ext_by_mime($file['type']);
			$filename = uniqid() . '.' . $ext;
		}

		if (!file_exists($directory) OR ! is_dir($directory))
		{
			mkdir($directory, 0644, true);
		}

		// Make the filename into a complete path
		$filename = $directory . DIRECTORY_SEPARATOR . $filename;

		if (move_uploaded_file($file['tmp_name'], $filename))
		{
			if ($chmod !== false)
			{
				// Set permissions on filename
				chmod($filename, $chmod);
			}

			// Return new file path
			return $filename;
		}

		return false;
	}

	/**
	 * Tests if upload data is valid, even if no file was uploaded. If you
	 * _do_ require a file to be uploaded, add the [Upload::not_empty] rule
	 * before this rule.
	 *
	 *     $array->rule('file', 'Upload::valid')
	 *
	 * @param   array   $file   $_FILES item
	 * @return  bool
	 */
	public static function valid($file)
	{
		return (isset($file['error'])
				AND isset($file['name'])
				AND isset($file['type'])
				AND isset($file['tmp_name'])
				AND isset($file['size']));
	}

	/**
	 * Tests if a successful upload has been made.
	 *
	 *     $array->rule('file', 'Upload::not_empty');
	 *
	 * @param   array   $file   $_FILES item
	 * @return  bool
	 */
	public static function not_empty(array $file)
	{
		return (
				isset($file['error'])
				AND isset($file['tmp_name'])
				AND $file['error'] === UPLOAD_ERR_OK
				AND is_uploaded_file($file['tmp_name'])
				);
	}

	/**
	 * Test if an uploaded file is an allowed file type, by extension.
	 *
	 *     $array->rule('file', 'Upload::type', [':value', 'jpg, png, gif']);
	 *
	 * @param   array   $file       $_FILES item
	 * @param   string   $allowed_string    allowed file extensions
	 * @return  bool
	 */
	public static function type(array $file, $allowed_string)
	{
		$allowed = array_map('trim', explode(',', $allowed_string));

		if ($file['error'] !== UPLOAD_ERR_OK)
		{
			return true;
		}

		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		return in_array($ext, $allowed);
	}

	/**
	 * Validation rule to test if an uploaded file is allowed by file size.
	 * File sizes are defined as: SB, where S is the size (1, 8.5, 300, etc.)
	 * and B is the byte unit (K, MiB, GB, etc.). All valid byte units are
	 * defined in Num::$byte_units
	 *
	 *     $array->rule('file', 'Upload::size', array(':value', '1M'))
	 *     $array->rule('file', 'Upload::size', array(':value', '2.5KiB'))
	 *
	 * @   array   $file   $_FILES item
	 * @param   string  $size   maximum file size allowed
	 * @return  bool
	 */
	public static function size(array $file, $size)
	{
		if ($file['error'] === UPLOAD_ERR_INI_SIZE)
		{
			// Upload is larger than PHP allowed size (upload_max_filesize)
			return false;
		}

		if ($file['error'] !== UPLOAD_ERR_OK)
		{
			// The upload failed, no size to check
			return true;
		}

		// Convert the provided size to bytes for comparison
		$size = Num::bytes($size);

		// Test that the file is under or equal to the max size
		return ($file['size'] <= $size);
	}

	/**
	 * Validation rule to test if an upload is an image and, optionally, is the correct size.
	 *
	 *     // The "image" file must be an image
	 *     $array->rule('image', 'Upload::image')
	 *
	 *     // The "photo" file has a maximum size of 640x480 pixels
	 *     $array->rule('photo', 'Upload::image', array(':value', 640, 480));
	 *
	 *     // The "image" file must be exactly 100x100 pixels
	 *     $array->rule('image', 'Upload::image', array(':value', 100, 100, true));
	 *
	 *
	 * @param   array   $file       $_FILES item
	 * @param   integer $max_width  maximum width of image
	 * @param   integer $max_height maximum height of image
	 * @param   boolean $exact      match width and height exactly?
	 * @return  boolean
	 */
	public static function image(array $file, $max_width = null, $max_height = null, $exact = false)
	{
		if (Upload::not_empty($file))
		{
			try
			{
				// Get the width and height from the uploaded image
				list($width, $height) = getimagesize($file['tmp_name']);
			}
			catch (ErrorException $e)
			{
				// Ignore read errors
			}

			if (empty($width) OR empty($height))
			{
				// Cannot get image size, cannot validate
				return false;
			}

			if (!$max_width)
			{
				// No limit, use the image width
				$max_width = $width;
			}

			if (!$max_height)
			{
				// No limit, use the image height
				$max_height = $height;
			}

			if ($exact)
			{
				// Check if dimensions match exactly
				return ($width === $max_width AND $height === $max_height);
			}
			else
			{
				// Check if size is within maximum dimensions
				return ($width <= $max_width AND $height <= $max_height);
			}
		}

		return true;
	}

}
