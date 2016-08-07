<?php

/**
 * Database connection class
 *
 * @package    Deraemon/Database
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

// Here:: task 
// has value item change to prepare statment for select and delete
// default "=" set to where

class DB {

//$insert = Db::fact(Db::INSERT)
//		->table('contents')
//		->set('id', 2)
//		->set('segment', 'about')
//		->set('value', 'bbb')
//		->set('created_at', Date::format())
//		->execute();
//$update = Db::fact(Db::UPDATE)
//		->table('contents')
//		->where('id', '=', 2)
//		->set('updated_at', Date::format())
//		->execute();
//$delete = Db::fact(Db::DELETE)
//		->table('contents')
//		->where('id', '=', 2)
//		->execute();
//$select = Db::fact(Db::SELECT)
//		->table('contents')
//		->where('id', '=', 2)
//		->execute();

	private $_host;
	private $_dbname;
	private $_dsn;
	private $_username;
	private $_passwd;
	private $_encode = 'utf8';
	private $_port = null;
	private $_db;
	private $_fetchmode = PDO::FETCH_ASSOC;
	//
	private $_type;
	private $_select;
	private $_join = '';
	private $_where = array();
	private $_order_by;
	private $_limit;
	private $_as_object;
	//
	private $_key_value = array();
	private $_keys = array();
	private $_values = array();
	//
	private $_table;
	private $_query;
	private $_results = null;

	const INSERT = 'insert';
	const SELECT = 'select';
	const UPDATE = 'update';
	const DELETE = 'delete';

	/* Constracter___________________________________________________________ */

	/**
	 * fact
	 * 
	 * @param array $param
	 * @return \DB
	 */
	public static function fact(array $param = [])
	{
		return new DB($param);
	}

	/**
	 * construct
	 * 
	 * @param array $param
	 * 
	 * 		array(
	 * 			'host'     => '',
	 * 			'dbname'   => '',
	 * 			'username' => '',
	 * 			'passwd'   => '',
	 * 			);
	 */
	public function __construct(array $param = [])
	{

		if (!$param)
		{
			$param = Config::fact('db')->get('config');
		}

		$this->_host = $param['host'];
		$this->_dbname = $param['dbname'];
		$this->_username = $param['username'];
		$this->_passwd = $param['passwd'];

		if (isset($param['port']))
		{
			$this->_port = $param['port'];
		}

		if (isset($param['encode']))
		{
			$this->_encode = $param['encode'];
		}


		$this->_dsn = 'mysql:host=' . $this->_host . ';dbname=' . $this->_dbname;

		if ($this->_port)
		{
			$this->_dsn .= ';port=' . $this->_port;
		}

		if ($this->_encode)
		{
			$this->_dsn .= ';encode=' . $this->_encode;
		}

		$this->_db = $this->conect();
	}

	/**
	 * conect
	 * 
	 * @return \PDO
	 */
	public function conect()
	{
		Debug::v($this->_dsn);
		try
		{
			$db = new PDO($this->_dsn, $this->_username, $this->_passwd);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
			$db->setAttribute(PDO::ATTR_PERSISTENT, true);
			//$db->exec('set names ' . $this->_encode);
		}
		catch (PDOException $e)
		{
			echo 'Connection failed: ' . $e->getMessage();
		}

		return $db;
	}

	/* magic mathod ---------------------------------------------------------- */

	public function __invoke($str = null)
	{
		return 'array';
	}

	public function __toString()
	{

		return 'array';
	}

	/* Setter Getter_________________________________________________________ */

	/**
	 * get query
	 * @return string
	 */
	public function get_query()
	{

		return $this->_query;
	}

	/* transaction___________________________________________________________ */

	/**
	 * begin
	 */
	public function begin()
	{

		$this->_db->beginTransaction();
	}

	/**
	 * commit
	 */
	public function commit()
	{

		$this->_db->commit();
	}

	/**
	 * rollback
	 */
	public function rollback()
	{

		$this->_db->rollBack();
	}

	/* sql___________________________________________________________________ */

	/**
	 * sql
	 * 
	 * @param string $query
	 */
	public function sql($query)
	{

		$this->_db->query($query)->execute();
		$this->_query = $query;
	}

	/* Select________________________________________________________________ */

	/**
	 * select
	 * 
	 * $db->select() ------------------------------ select * 
	 * $db->select('*') --------------------------- select * 
	 * $db->select('id', 'name') ------------------ select id, name 
	 * $db->select(array('id', 'user_id'), 'id') -- select id as user_id, name
	 * 
	 * @return \DB
	 */
	public function select($columns = null)
	{
		$this->_type = 'select';

		$this->_select = 'select ';
		$args = func_get_args();

		if (!$args)
		{

			$this->_select .= '*';
		}
		else
		{

			$arr = array();
			foreach ($args as $arg)
			{
				if (is_array($arg))
				{
					$arr[] = $arg[0] . ' as ' . $arg[1];
				}
				else
				{
					$arr[] = $arg;
				}
			}
			$this->_select .= implode(', ', $arr);
		}

		return $this;
	}

	/**
	 * from
	 * 
	 * @param string $table
	 * @return \DB
	 */
	public function from($table)
	{
		$this->_table = $table;

		return $this;
	}

	/**
	 * 
	 * @param string $table
	 * @return \DB
	 */
	public function table($table)
	{
		$this->_table = $table;

		return $this;
	}

	/* Join__________________________________________________________________ */

	/**
	 * join 
	 * 
	 * @param string $table
	 * @param string $type
	 * @return \DB
	 */
	public function join($table, $type = null)
	{

		if ($type)
		{
			$this->_join .= 'join ' . $table;
		}
		else
		{
			$this->_join .= $type . ' join ' . $table;
		}

		return $this;
	}

	/**
	 * on
	 * 
	 * @param string $colmun1
	 * @param string $op
	 * @param string $colmun2
	 * @return \DB
	 */
	public function on($colmun1, $op, $colmun2)
	{

		$this->_join .= ' on ' . $colmun1 . ' ' . $op . ' ' . $colmun2;

		return $this;
	}

	/* Where_________________________________________________________________ */

	/**
	 * where
	 * 
	 * @param string $column
	 * @param string $op
	 * @param string $value
	 * @return \DB
	 */
	public function where($column, $op, $value)
	{
		$value = is_numeric($value) ? $value : "'$value'";
		if (!count($this->_where))
		{
			$this->_where[] = 'where ' . $column . ' ' . $op . ' ' . $value;
		}
		else
		{
			$this->_where[] = 'where ' . $column . ' ' . $op . ' ' . $value;
		}

		return $this;
	}

	/**
	 * and_where
	 * 
	 * @param string $column
	 * @param string $op
	 * @param string $value
	 * @return \DB
	 */
	public function and_where($column, $op, $value)
	{

		$this->_where[] = $column . 'and ' . $op . ' ' . $value;

		return $this;
	}

	/**
	 * or_where
	 * 
	 * @param string $column
	 * @param string $op
	 * @param string $value
	 * @return \DB
	 */
	public function or_where($column, $op, $value)
	{

		$this->_where[] = 'or ' . $column . ' ' . $op . ' ' . $value;

		return $this;
	}

	/**
	 * where_open
	 * 
	 * @return \DB
	 */
	public function where_open()
	{

		$this->_where[] = '(';

		return $this;
	}

	/**
	 * and_where_open
	 * 
	 * @return \DB
	 */
	public function and_where_open()
	{

		$this->_where[] = '(';

		return $this;
	}

	/**
	 * or_where_open
	 * 
	 * @return \DB
	 */
	public function or_where_open()
	{

		$this->_where[] = 'or (';

		return $this;
	}

	/**
	 * where_close
	 * 
	 * @return \DB
	 */
	public function where_close()
	{

		$this->_where[] = ')';

		return $this;
	}

	/**
	 * and_where_close
	 * 
	 * @return \DB
	 */
	public function and_where_close()
	{

		$this->_where[] = ')';

		return $this;
	}

	/**
	 * or_where_close
	 * 
	 * @return \DB
	 */
	public function or_where_close()
	{

		$this->_where[] = ')';

		return $this;
	}

	/* Limit_________________________________________________________________ */

	/**
	 * limit
	 * 
	 * 	->limit(3)
	 * 	->limit(4, 2)
	 * 
	 * @param string $limit
	 * @param string $offset
	 * @return \DB
	 */
	public function limit($limit = null, $offset = null)
	{

		if ($limit AND $offset)
		{
			$this->_limit = 'limit ' . $limit . ', ' . $offset;
		}
		elseif ($limit AND ! $offset)
		{
			$this->_limit = 'limit ' . $limit;
		}

		return $this;
	}

	/* Order by______________________________________________________________ */

	/**
	 * order_by
	 * 
	 * _by('id desc')
	 * _by('orders.id desc', 'name asc')
	 * 
	 * @param type $column_direction
	 * @return \DB
	 */
	public function order_by($column_direction = null)
	{
		$args = func_get_args();

		$this->_order_by = 'order by ';

		$arr = array();
		foreach ($args as $arg)
		{
			$arr[] = $arg;
		}

		$this->_order_by .= implode(', ', $arr);

		return $this;
	}

	/**
	 * as_object
	 * 
	 * @return \DB
	 */
	public function as_object()
	{
		$this->_as_object = true;

		$this->_fetchmode = PDO::FETCH_OBJ;

		return $this;
	}

	/* Insert________________________________________________________________ */

	/**
	 * insert
	 * @param string $param
	 */
	public function insert($table = null)
	{

		$this->_type = 'insert';

		if ($table)
		{
			$this->_table = $table;
		}

		return $this;
	}

	/**
	 * set
	 * 
	 * @param type $key
	 * @param type $value
	 * @return \DB
	 */
	public function set($key, $value)
	{

		$this->_keys[] = $key;
		$this->_values[] = $value;
		$this->_key_value[$key] = $value;

		return $this;
	}

	/* Update________________________________________________________________ */

	/**
	 * update
	 * 
	 * @param string $table
	 * @return \DB
	 */
	public function update($table = null)
	{

		$this->_type = 'update';

		if ($table)
		{
			$this->_table = $table;
		}

		return $this;
	}

	/* Delete________________________________________________________________ */

	/**
	 * delete
	 * 
	 * @param string $table
	 * @return \DB
	 */
	public function delete($table = null)
	{

		$this->_type = 'delete';

		if ($table)
		{
			$this->_table = $table;
		}

		return $this;
	}

	/* exec__________________________________________________________________ */

	/**
	 * select_fetch
	 * 
	 * @return mix
	 */
	public function select_fetch()
	{
		if (!$this->_select)
		{
			$this->_select = 'select *';
		}

		$query = $this->_select . ' from ' . $this->_table;

		if ($this->_join)
		{
			$query .= ' ' . $this->_join;
		}

		if ($this->_where)
		{
			foreach ($this->_where as $value)
			{
				$query .= ' ' . $value;
			}
		}

		if ($this->_order_by)
		{
			$query .= ' ' . $this->_order_by;
		}

		if ($this->_limit)
		{
			$query .= ' ' . $this->_limit;
		}

		$this->_query = $query;

		$result = $this->_db->query($query)->fetchAll($this->_fetchmode);

		return $result;
	}

	/**
	 * insert_execute
	 * 
	 * @return bool
	 */
	public function insert_execute()
	{
		$query = 'insert into ' . $this->_table . '(' . implode(',', $this->_keys) . ') values (:' . implode(',:', $this->_keys) . ')';
		$stt = $this->_db->prepare($query);

		for ($i = 0; $i < count($this->_keys); $i++)
		{
			$stt->bindValue(':' . $this->_keys[$i], $this->_values[$i]);
		}
		$this->_query = $query;

		$result = $stt->execute();

		return $result;
	}

	/**
	 * update_execute
	 * 
	 * @return bool
	 */
	public function update_execute()
	{

		$query = 'update ' . $this->_table . ' set';

		foreach ($this->_key_value as $key => $value)
		{
			$query .= ' ' . $key . ' = :' . $key . ',';
		}

		$query = substr($query, 0, -1);

		if ($this->_where)
		{
			foreach ($this->_where as $value)
			{
				$query .= ' ' . $value;
			}
		}

		$stt = $this->_db->prepare($query);

		foreach ($this->_key_value as $key => $value)
		{
			$stt->bindValue(':' . $key, $value);
		}
		$this->_query = $query;
		$result = $stt->execute();

		return $result;
	}

	/**
	 * delete_execute
	 * 
	 * @return bool
	 */
	public function delete_execute()
	{
		$query = 'delete from ' . $this->_table;

		if ($this->_where)
		{
			foreach ($this->_where as $value)
			{
				$query .= ' ' . $value;
			}
		}

		$this->_query = $query;

		$result = $this->_db->query($query)->execute();

		return $result;
	}

	/**
	 * execute
	 * 
	 * @return mix
	 */
	public function execute()
	{
		$result = '';

		try
		{
			switch ($this->_type)
			{
				case 'insert':
					$result = $this->insert_execute();
					break;

				case 'select':
					$result = $this->select_fetch();
					break;

				case 'update':
					$result = $this->update_execute();
					break;

				case 'delete':
					$result = $this->delete_execute();
					break;

				default:
					break;
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			echo 'SQL: ' . $this->_query;
		}

		$this->reset();

		$this->_results = $result;

		return $this;
	}

	/**
	 * Return all of the rows in the result as an array.
	 *
	 *     // Indexed array of all rows
	 *     $rows = $result->as_array();
	 *
	 *     // Associative array of rows by "id"
	 *     $rows = $result->as_array('id');
	 *
	 *     // Associative array of rows, "id" => "name"
	 *     $rows = $result->as_array('id', 'name');
	 *
	 * @param   string  $key    column for associative keys
	 * @param   string  $value  column for values
	 * @return  array
	 */
	public function as_array($key = NULL, $value = NULL)
	{
		$results = array();

		if ($key === NULL AND $value === NULL)
		{
			// Indexed rows

			foreach ($this->_results as $row)
			{
				$results[] = $row;
			}
		}
		elseif ($key === NULL)
		{
			// Indexed columns

			if ($this->_as_object)
			{
				foreach ($this->_results as $row)
				{
					$results[] = $row->$value;
				}
			}
			else
			{
				foreach ($this->_results as $row)
				{
					$results[] = $row[$value];
				}
			}
		}
		elseif ($value === NULL)
		{
			// Associative rows

			if ($this->_as_object)
			{
				foreach ($this->_results as $row)
				{
					$results[$row->$key] = $row;
				}
			}
			else
			{
				foreach ($this->_results as $row)
				{
					$results[$row[$key]] = $row;
				}
			}
		}
		else
		{
			// Associative columns

			if ($this->_as_object)
			{
				foreach ($this->_results as $row)
				{
					$results[$row->$key] = $row->$value;
				}
			}
			else
			{
				foreach ($this->_results as $row)
				{
					$results[$row[$key]] = $row[$value];
				}
			}
		}

		return $results;
	}

	/**
	 * Return the named column from the current row.
	 *
	 *     // Get the "id" value
	 *     $id = $result->get('id');
	 *
	 * @param   string  $name     column to get
	 * @param   mixed   $default  default value if the column does not exist
	 * @return  mixed
	 */
	public function get($name, $default = NULL)
	{
		$row = reset($this->_results);

		if ($this->_as_object)
		{
			if (isset($row->$name))
				return $row->$name;
		}
		else
		{
			if (isset($row[$name]))
				return $row[$name];
		}

		return $default;
	}

	/**
	 * Implements [Countable::count], returns the total number of rows.
	 *
	 *     echo count($result);
	 *
	 * @return  integer
	 */
	public function count()
	{
		return $this->_results;
	}

	/**
	 * reset
	 */
	public function reset()
	{
		$this->_type = null;
		$this->_select = null;
		$this->_join = null;
		$this->_where = array();
		$this->_order_by = null;
		$this->_limit = null;
		$this->_as_object = null;
		$this->_key_value = array();
		$this->_keys = array();
		$this->_values = array();
	}

	/**
	 * close
	 */
	public function close()
	{
		$this->_db = null;
	}

}
