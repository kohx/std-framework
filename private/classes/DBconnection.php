<?php

/**
 * DBconnection class
 *
 * @package    Deraemon/DBconnection
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

class DBconnection {

	public static $instance;
	protected $_name = 'default';
	protected $_connections = [];

	public static function inst($name = 'default', $params = [])
	{
		// Make instance
		if (!isset(static::$instance))
		{
			static::$instance = new DBconnection($name, $params);
		}

		return static::$instance;
	}

	/**
	 * Construct
	 * 
	 * 		$params = [
	 * 			'dbtype' => '',
	 * 			'dbname' => '',
	 * 			'port' => '',
	 * 			'charset' => '',
	 * 			'filepath' => '',
	 * 			'username' => '',
	 * 			'passwd' => '',
	 * 			];
	 * 
	 * @param string $name
	 * @param array $params
	 * @return \DBconnection
	 */
	public function __construct($name = 'default', $params = [])
	{
		$this->_name = $name;

		if (!$params)
		{
			$params = Config::fact('db')->get('default');
		}

		$dbtype = Arr::get($params, 'dbtype');
		$dbhost = Arr::get($params, 'dbhost');
		$dbname = Arr::get($params, 'dbname');
		$port = Arr::get($params, 'port', 3306);
		$charset = Arr::get($params, 'charset', 'utf8');
		$username = Arr::get($params, 'username');
		$passwd = Arr::get($params, 'passwd');
		$filepath = Arr::get($params, 'filepath');

		switch ($dbtype)
		{
			case 'mysql':
				$dns = "mysql:host={$dbhost};dbname={$dbname};port={$port};charset={$charset};";
				break;

			case 'pgsql':
				$dns = "pgsql:dbname={$dbname} host={$dbhost} port={$port}";
				break;

			case 'sqlite':
				$dns = "sqlite:{$filepath}";
				break;

			case 'sqlite2':
				$dns = "sqlite:{$filepath}";
				break;

			default:
				break;
		}

		try
		{
			$connection = new PDO($dns, $username, $passwd);
			$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$connection->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
			$connection->setAttribute(PDO::ATTR_PERSISTENT, true);

			$this->_connections[$this->_name] = $connection;
		}
		catch (PDOException $e)
		{
			echo 'Connection failed: ' . $e->getMessage();
		}

		return $this;
	}

	public function get($name = 'default')
	{
		return Arr::get($this->_connections, $name, false);
	}

	/**
	 * Destruct
	 */
	public function __destruct()
	{
		foreach ($this->_connections as $connection)
		{
			unset($connection);
		}
	}

}
