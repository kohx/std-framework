<?php

/**
 * Database session class
 *
 * 		CREATE TABLE IF NOT EXISTS `sessions` (
 * 			`id` varchar(32) NOT NULL,
 * 			`access` int(10) unsigned DEFAULT NULL,
 * 			`data` text,
 * 			PRIMARY KEY (`id`)
 * 		) ENGINE=InnoDB ;
 * 
 * @package    Deraemon/Session
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 * 
 */
require_once 'AutoLoader.php';

class Session implements SessionHandlerInterface, SessionIdInterface {

	/**
	 * @var string Session table neme
	 */
	protected static $_tablename = 'sessions';

	/**
	 * @var int Session cookie lifetime
	 */
	protected $_lifetime = 0;

	/*
	 * @var int Session garbage collection max lifetime
	 * セッションの最大延長時間
	 */
	protected $_maxlifetime = 604800; // 1weeek

	/*
	 * @var boolean Encripted data
	 */
	protected $_encrypted = false;

	/*
	 * @var string Session name
	 */
	protected $_name;

	/*
	 * proparty
	 */
	public static $instance;
	protected $_database = null;
	protected $columns = [
		'id' => 'session_id',
		'access' => 'last_active',
		'data' => 'contents',
	];

	/**
	 * @return Session
	 */
	public static function inst()
	{
		// Set Session garbage collection probability
		ini_set('session.gc_probability', 1);
		ini_set('session.gc_divisor', 1);

		// Make instance
		if (!isset(static::$instance))
		{
			static::$instance = new Session();
		}

		return static::$instance;
	}

	/**
	 * Constructer
	 */
	public function __construct()
	{
		$config = Config::fact('session');

		// Set session name
		if ($config->get('name'))
		{
			$this->_name = $config->get('name');
			session_name($this->_name);
		}

		// Set lifetime
		if ($config->get('lifetime'))
		{
			$this->_lifetime = (int) $config->get('lifetime');
		}

		// Set maxlifetime
		if ($config->get('maxlifetime'))
		{
			$this->_maxlifetime = (int) $config->get('maxlifetime');
		}

		// Set encrypted
		if ($config->get('encrypted'))
		{
			$this->_encrypted = (int) $config->get('encrypted');
		}

		// Set gc maxlifetime
		ini_set('session.gc_maxlifetime', $this->_maxlifetime);

		// Set handler to overide SESSION
		session_set_save_handler(
				array($this, "open"), array($this, "close"), array($this, "read"), array($this, "write"), array($this, "destroy"), array($this, "gc")
		);

		// prevents unexpected effects when using objects as save handlers.
		register_shutdown_function('session_write_close');

		// Sync up the session cookie with Cookie parameters
		session_set_cookie_params($this->_lifetime, Cookie::$_path, Cookie::$_domain, Cookie::$_secure, true);

		// Do not allow PHP to send Cache-Control headers
		session_cache_limiter(false);

		// Start the session
		session_start();

		// regenerates the session and delete the old one. 
		// It also generates a new encryption key in the database.
		session_regenerate_id(true);
	}

	/**
	 * Open
	 * open - データベースを開く
	 * $save_path, $session_name
	 */
	public function open($save_path, $session_name)
	{
		// Open database
		$this->_database = DB::fact()->table(static::$_tablename);

		return true;
	}

	/**
	 * Read
	 *  read - セッションID をキーにしてデータを読み込んで string で返す
	 * 
	 * @param int $session_id
	 * @return string
	 */
	public function read($session_id)
	{
		$results = null;

		// If session id
		if ($session_id OR $session_id == Cookie::get($this->_name))
		{
			$query = $this->_database
					->select($this->columns['data'])
					->where($this->columns['id'], $session_id)
					->execute();
			

			if ($query->count())
			{
				$results = $query->get($this->columns['data']);
			}
		}

		// If encrypted
		if ($this->_encrypted)
		{
			// Encrypt the data using the default key
			$results = Encrypt::inst()->decode($results);
		}

		// Retaun 
		return $results;
	}

	/**
	 * Write
	 * write - セッションID とデータが渡されるので素直に保存する
	 * 
	 * @param int $session_id
	 * @param mix $session_data
	 * @return bool
	 */
	public function write($session_id, $session_data)
	{
		// If encrypted
		if ($this->_encrypted)
		{
			// Encrypt the data using the default key
			$session_data = Encrypt::inst()->encode($session_data);
		}

		// Insert database
		$query = $this->_database
				->select()
				->where($this->columns['id'], $session_id)
				->one()
		;

		if ($query->count())
		{
			$results = $this->_database
					->where($this->columns['id'], '=', $session_id)
					->update([
						$this->columns['access'], time(),
						$this->columns['data'], $session_data,
					]);
		}
		else
		{
			$results = $this->_database
					->insert([
						$this->columns['id'] => $session_id,
						$this->columns['access'] => time(),
						$this->columns['data'] => $session_data,
					]);
		}

		// Write 
		session_write_close();

		return $results ? true : false;
	}

	/**
	 * Destroy
	 * destroy - セッションID をキーにして値をストレージから削除
	 * 
	 * @param int $session_id
	 * @return bool
	 */
	public function destroy($session_id)
	{
		$result = $this->_database
				->where($this->columns['id'], '=', $session_id)
				->delete();

		return $result ? true : false;
	}

	/**
	 * Close
	 * close - データベースを閉じる
	 */
	public function close()
	{
		$this->_database = null;

		return true;
	}

	/**
	 * Gc - Garbage Collection
	 * gc - 期限切れのセッションデータを削除
	 * 
	 * @param int $max
	 * @return bool
	 */
	public function gc($max)
	{
		// Calculate what is to be deemed old
		$old = time() - $max;

		$result = $this->_database
				->where($this->columns['access'], '<', $old)
				->delete();

		return $result ? true : false;
	}

	/**
	 * Create sid
	 * 
	 * @return int
	 */
	public function create_sid()
	{
		session_regenerate_id(true);

		return session_id();
	}

	/**
	 * Destructer
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Regenerate
	 */
	public function regenerate()
	{
		$this->create_sid();
	}

	/**
	 * Set a variable in the session array.
	 * 
	 * @param type $key
	 * @param type $value
	 * @return \Session
	 */
	public function set($key, $value)
	{
		$_SESSION[$key] = $value;

		return $this;
	}

	/**
	 * Set a variable by reference.
	 *
	 *     $session->bind('foo', $foo);
	 *
	 * @param   string  $key    variable name
	 * @param   mixed   $value  referenced value
	 * @return  $this
	 */
	public function bind($key, & $value)
	{
		$_SESSION[$key] = & $value;

		return $this;
	}

	/**
	 * Get a variable from the session array.
	 *
	 *     $foo = $session->get('foo');
	 *
	 * @param   string  $key        variable name
	 * @param   mixed   $default    default value to return
	 * @return  mixed
	 */
	public function get($key, $default = null)
	{
		$result = isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
		return $result;
	}

	/**
	 * Get and delete a variable from the session array.
	 *
	 *     $bar = $session->get_once('bar');
	 *
	 * @param   string  $key        variable name
	 * @param   mixed   $default    default value to return
	 * @return  mixed
	 */
	public function get_once($key, $default = NULL)
	{
		$result = isset($_SESSION[$key]) ? $_SESSION[$key] : $default;

		unset($_SESSION[$key]);

		return $result;
	}

	/**
	 * Removes a variable in the session array.
	 *
	 *     $session->delete('foo');
	 *
	 * @param   string  $key,...    variable name
	 * @return  $this
	 */
	public function delete($key)
	{
		$args = func_get_args();

		foreach ($args as $key)
		{
			unset($_SESSION[$key]);
		}

		return $this;
	}
	
	/**
	 * Restart the session.
	 *
	 *     $success = $session->restart();
	 *
	 * @return  boolean
	 */
	public function restart()
	{
		$_SESSION = array();
		
		return true;
	}

}
