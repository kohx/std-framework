<?php

/**
 * Database connection class
 *
 * @package    Deraemon/Database
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 * 
 * Database tables have to  have 'id' column.
 * ex)
 * CREATE TABLE IF NOT EXISTS `users` (
 *  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 *  `username` varchar(45) DEFAULT NULL,
 *  `displayname` varchar(45) DEFAULT NULL,
 *  `created_at` datetime DEFAULT NULL,
 *  `updated_at` datetime DEFAULT NULL,
 *  PRIMARY KEY (`id`)
 *  ENGINE=Inodb AUTO_INCREMENT=205 DEFAULT CHARSET=utf8;
 * 
 */
require_once 'AutoLoader.php';

// Here:: task 

class DB {

	protected $_connection;
	protected $_fetchmode = PDO::FETCH_ASSOC;
	//
	protected $_selects = [];
	protected $_table;
	protected $_joins = [];
	protected $_left_joins = [];
	protected $_wheres = [];
	protected $_where_first = true;
	protected $_limit;
	protected $_offset;
	protected $_orders = [];
	protected $_groups = [];
	protected $_havings = [];
	//
	protected $_values = [];
	//
	protected $_as_object;
	//
	protected $_query = '';
	protected $_results = [];

	/**
	 * Factory
	 * 
	 * 			$params = [
	 * 				'dbtype' => '',
	 * 				'dbname' => '',
	 * 				'port' => '',
	 * 				'charset' => '',
	 * 				'filepath' => '',
	 * 				'username' => '',
	 * 				'passwd' => '',
	 * 			];
	 * 
	 * 			$db = DB::fuct('default', $params);
	 * 
	 * @param DBconnection $conection
	 * @return \DB
	 */
	public static function fact($name = 'default', $params = [])
	{

		return new DB($name, $params);
	}

	/**
	 * Construct
	 * 
	 * @param DBconnection $conection
	 */
	public function __construct($name = 'default', $params = [])
	{

		$this->_connection = DBconnection::inst($name, $params)->get();
	}

	/**
	 * As object
	 * 
	 * 			$db->as_object();
	 * 
	 * @return \DB
	 */
	public function as_object()
	{
		$this->_as_object = true;

		$this->_fetchmode = PDO::FETCH_OBJ;

		return $this;
	}

	/**
	 * Transaction begin
	 * 
	 * 			$db->beginTransaction();
	 */
	public function begin()
	{

		$this->_connection->beginTransaction();
	}

	/**
	 * Transaction commit
	 * 
	 * 			$db->commit();
	 */
	public function commit()
	{

		$this->_connection->commit();
	}

	/**
	 * Transaction rollback
	 * 
	 * 			$db->rollback();
	 */
	public function rollback()
	{

		$this->_connection->rollBack();
	}

	/**
	 * sql
	 * 
	 * 			$db->query("SELECT * FROM users ORDER BY no ASC");
	 * 
	 * @param string $query
	 */
	public function sql($query)
	{

		$this->_connection->query($query)->execute();
		$this->_query = $query . ';';
	}

	/**
	 * from
	 * 
	 * 			$db->from('users);
	 * 
	 * @param string $table
	 * @return \DB
	 */
	public function from($table)
	{
		$conv = self::conv($table);
		$this->_table = $conv;

		return $this;
	}

	/**
	 * table
	 * 
	 * 			$db->table('users);
	 * 
	 * @param string $table
	 * @return \DB
	 */
	public function table($table)
	{
		$this->_table = $table;

		return $this;
	}

	/**
	 * join 
	 * 
	 * 			$db->table('users)->join('details', 'user.id', 'details.user_id');
	 * 
	 * @param string $table
	 * @param string $type
	 * @return \DB
	 */
	public function join($table, $colmun1, $op, $colmun2)
	{
		$this->_joins[] = 'join ' . $table . ' on ' . $colmun1 . ' ' . $op . ' ' . $colmun2;

		return $this;
	}

	/**
	 * leftjoin 
	 * 
	 * 				$db->table('users)->left_join('details', 'user.id', 'details.user_id');
	 * 
	 * @param string $table
	 * @param string $type
	 * @return \DB
	 */
	public function left_join($table, $colmun1, $op, $colmun2)
	{
		$this->_left_joins[] = 'left join ' . $table . ' on ' . $colmun1 . ' ' . $op . ' ' . $colmun2;

		return $this;
	}

	/**
	 * 
	 * @param type $column
	 * @param string $op
	 * @param mix $value
	 * @return string
	 */
	protected function _where_base($column, $op, $value = null)
	{

		if (is_null($value))
		{
			$value = $op;
			$op = '=';
			$convertion = self::conv($value);
			return "{$column} {$op} {$convertion}";
		}
		elseif (strtolower($op) === 'in')
		{
			$convertions = [];

			foreach ($value as $v)
			{
				$convertions[] = self::conv($v);
			}

			$convertions_str = implode(', ', $convertions);

			return "{$column} {$op} ({$convertions_str})";
		}
		elseif (strtolower($op) === 'between')
		{
			$convertion_start = self::conv(reset($value));
			$convertion_end = self::conv(end($value));

			return "{$column} {$op} {$convertion_start} and $convertion_end";
		}
		else
		{
			$convertion = self::conv($value);

			return "{$column} {$op} {$convertion}";
		}
	}

	/**
	 * where
	 * 
	 * 			$db->where('id', $id);							WHERE id = 2
	 * 			$db->where('id', '=', $id);						WHERE id = 2
	 * 			$db->where('id', 'in', [$a, $b, $c]);			WHERE id IN ('aaa', 'bbb', 'ccc')
	 * 			$db->where('id', 'between', [$start, $end]);	WHERE id between 2 and 5
	 * 			$db->where('id', 'not', 'null');				WHERE id not null
	 * 			$db->where('id', 'like', '%string%');			WHERE id like %between%
	 * 
	 * @param string $column
	 * @param string $op
	 * @param mix $value
	 * @return \DB
	 */
	public function where($column, $op, $value = null)
	{
		$func = $this->_where_first ? 'where' : 'and';
		$this->_where_first = false;

		$this->_wheres[] = $func . ' ' . $this->_where_base($column, $op, $value);

		return $this;
	}

	/**
	 * and where
	 * 
	 * @param string $column
	 * @param string $op
	 * @param string $value
	 * @return \DB
	 */
	public function and_where($column, $op, $value = null)
	{

		$this->_wheres[] = 'and' . ' ' . $this->_where_base($column, $op, $value);

		return $this;
	}

	/**
	 * or where
	 * 
	 * @param string $column
	 * @param string $op
	 * @param string $value
	 * @return \DB
	 */
	public function or_where($column, $op, $value = null)
	{

		$this->_wheres[] = 'or' . ' ' . $this->_where_base($column, $op, $value);

		return $this;
	}

	/**
	 * where open
	 * 
	 * @return \DB
	 */
	public function where_open()
	{

		$this->_wheres[] = '(';

		return $this;
	}

	/**
	 * and where open
	 * 
	 * @return \DB
	 */
	public function and_where_open()
	{

		$this->_wheres[] = '(';

		return $this;
	}

	/**
	 * or where open
	 * 
	 * @return \DB
	 */
	public function or_where_open()
	{

		$this->_wheres[] = 'or (';

		return $this;
	}

	/**
	 * where close
	 * 
	 * @return \DB
	 */
	public function where_close()
	{

		$this->_wheres[] = ')';

		return $this;
	}

	/**
	 * and where close
	 * 
	 * @return \DB
	 */
	public function and_where_close()
	{

		$this->_wheres[] = ')';

		return $this;
	}

	/**
	 * or where close
	 * 
	 * @return \DB
	 */
	public function or_where_close()
	{

		$this->_wheres[] = ')';

		return $this;
	}

	/**
	 * limit
	 * 
	 * 			$db->limit(3)
	 * 			$db->limit(4, 2)
	 * 
	 * @param string $limit
	 * @param string $offset
	 * @return \DB
	 */
	public function limit($limit = null, $offset = null)
	{

		if ($limit AND $offset)
		{
			$convertion_limit = self::conv($limit);
			$this->_limit = 'limit ' . $convertion_limit;

			$convertion_offset = self::conv($offset);
			$this->_offset = 'offset' . $convertion_offset;
		}
		elseif ($limit AND ! $offset)
		{
			$convertion = self::conv($limit);
			$this->_limit = 'limit ' . $convertion;
		}

		return $this;
	}

	/**
	 * Offset
	 * 
	 * 			$db->offset(4);
	 * 
	 * @param string $limit
	 * @param string $offset
	 * @return \DB
	 */
	public function offset($offset = null)
	{
		$convertion = self::conv($offset);
		$this->_offset = 'offset ' . $convertion;

		return $this;
	}

	/**
	 * Order by
	 * 
	 * 			$db->order('id')
	 * 			$db->order('orders, 'asc')
	 * 			$db->order('orders, 'desc')
	 * 
	 * @param type $column_direction
	 * @return \DB
	 */
	public function order_by($column, $direction = 'asc')
	{

		$this->_orders[] = "{$column} $direction";

		return $this;
	}

	/**
	 * Group by
	 * 
	 * 			   $db->group_by('id');
	 * 
	 * @param string $column
	 * @return \DB
	 */
	public function group_by($column)
	{
		$this->_groups[] = "group by {$column}";

		return $this;
	}

	/**
	 * Having
	 * 
	 * 			   $db->having('id', '2');
	 * 			or $db->having('id', '=', '2');
	 * 
	 * @param type $column
	 * @param string $op
	 * @param type $value
	 * @return \DB
	 */
	public function having($column, $op, $value = null)
	{
		if (is_null($value))
		{
			$value = $op;
			$op = '=';
		}

		$convertion = self::conv($value);
		$this->_havings[] = "having {$column} {$op} {$convertion}";

		return $this;
	}

	// select___________________________________________________________________

	/**
	 * select
	 * 
	 * 			$db->join('details', 'user.id', 'details.user_id')
	 * 				->where('id', 'in', [1,2,3,4,5])
	 * 				->order('id')
	 * 				->->select();
	 * 
	 * 			$db->select() ------------------------------ select * 
	 * 			$db->select('*') --------------------------- select * 
	 * 			$db->select('id', 'name') ------------------ select id, name 
	 * 			$db->select(array('id', 'user_id'), 'id') -- select id as user_id, name
	 * 
	 * @return \DB
	 */
	public function select(...$params)
	{
		/*
		 * Set select
		 */
		// When not have params
		if (!$params)
		{
			$this->_selects[] = '*';
		}
		else
		{
			foreach ($params as $param)
			{
				// When param has as
				if (is_array($param))
				{
					list($column, $as) = $param;

					$this->_selects[] = "{$column} as {$as}";
				}
				// When jast param
				else
				{
					$this->_selects[] = $param;
				}
			}
		}

		/*
		 * Do select
		 */
		// Set select
		$query = 'select ';

		// Set select columns
		$query .= implode(', ', $this->_selects);

		// Set from table
		$query .= ' from ' . $this->_table;

		// If has joins set these
		if ($this->_joins)
		{
			$query .= ' ' . implode(' ', $this->_joins);
		}

		// If has wheres set these
		if ($this->_wheres)
		{
			$query .= ' ' . implode(' ', $this->_wheres);
		}

		// If has groups set these
		if ($this->_groups)
		{
			$query .= " group by " . implode(', ', $this->_query);
		}

		//  If has havings set these
		if ($this->_havings)
		{
			$query .= ' ' . implode(' ', $this->_havings);
		}

		// If has orders set these
		if ($this->_orders)
		{
			$query .= ' order by ' . implode(', ', $this->_orders);
		}

		// If has limit set it
		if ($this->_limit)
		{
			$query .= ' limit ' . $this->_limit;
		}

		// If has offset set it
		if ($this->_offset)
		{
			$query .= ' offset ' . $this->_offset;
		}

		// Prepare
		$stt = $this->_connection->prepare($query);

		// Bind values
		foreach ($this->_values as $key => $value)
		{
			$type = is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR;

			$stt->bindValue($key, $value, $type);
		}

		$stt->execute();

		// Set to this results
		$this->_results = $stt->fetchAll($this->_fetchmode);

		// Make sql query 
		$this->_query = str_replace(array_keys($this->_values), array_values($this->_values), $query) . ';';

		$this->reset();

		return $this;
	}

	/**
	 * Return all of the rows in the result as an array.
	 *
	 * 			$db = DB::fact()
	 * 				->table('users')
	 * 				->select();
	 * 
	 * 			// Indexed array of all rows
	 * 			$rows = $db->get();
	 *
	 * 			// Associative array of rows by "id"
	 * 			$rows = $db->get('id');
	 *
	 * 			// Associative array of rows, "id" => "name"
	 * 			$rows = $db->get('id', 'name');
	 *
	 * @param   string  $key    column for associative keys
	 * @param   string  $value  column for values
	 * @return  array
	 */
	public function get($key = NULL, $value = NULL)
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
	 * Get one row from selected row
	 * 
	 * 			$db->one();
	 * 			$db->one('id');
	 * 			$db->one('username');
	 * 
	 * @param string $key
	 * @return mix
	 */
	public function one($key = null)
	{
		$result = reset($this->_results);

		if (is_null($key))
		{
			return $result;
		}

		return Arr::get($result, $key);
	}

	/**
	 * Selected row ount 
	 * 
	 * 			$count = $db->count();
	 * 
	 * @return int
	 */
	public function count()
	{

		return count($this->_results);
	}

	// Insert___________________________________________________________________

	/**
	 * Inserts
	 * INSERT INTO db_name.tbl_name (col_name1, col_name2, ...) 
	 * VALUES (value1, value2, ...)
	 * VALUES (value1, value2, ...);
	 * 
	 * 			$id = $db->table('users')
	 * 				->insert([
	 * 							['username' => 'user1', 'email' => 'user1@mail.com'],
	 * 						]);
	 * 
	 * 			$id = $db->table('users')
	 * 						->insert([
	 * 							['username' => 'user1', 'email' => 'user1@mail.com'],
	 * 							['username' => 'user2', 'email' => 'user2@mail.com'],
	 * 						]);
	 * 
	 * 			// You can use like this too.
	 * 			$id = $db->table('users')
	 * 						->insert(
	 * 						['username', 'email'],
	 * 						[
	 * 							['user1', 'user1@mail.com'],
	 * 							['user2', 'user2@mail.com'],
	 * 						]);
	 * 
	 * @param array $datas Array in array insert data.
	 * @return array effected ids
	 */
	public function insert($key_value, ...$params)
	{
		$ids = [];

		if (!$params)
		{
			$keys = array_keys(reset($key_value));
			$datas = array_values($key_value);

			foreach ($datas as &$data)
			{
				$data = array_values($data);
			}
			unset($data);
		}
		else
		{
			$keys = $key_value;
			$datas = array_values(reset($params));
		}

		foreach ($datas as $data)
		{
			$convertions = [];

			foreach ($data as $value)
			{
				$convertions[] = self::conv($value);
			}

			$keys_str = implode(', ', $keys);
			$conversions_str = implode(', ', $convertions);

			// Build query
			$query = "insert into {$this->_table} ({$keys_str}) values ({$conversions_str})";

			// Prepared
			$stt = $this->_connection->prepare($query);

			// Bind values
			foreach ($this->_values as $k => $v)
			{
				$type = is_numeric($v) ? PDO::PARAM_INT : PDO::PARAM_STR;

				$stt->bindValue($k, $v, $type);
			}

			$stt->execute();

			// For pgsql
			$sequence_object = Config::fact('db')->get('sequence_object');

			// Set inserted id to _results
			$ids[] = $this->_connection->lastInsertId($sequence_object);

			// Make sql query 
			$this->_query .= str_replace(array_keys($this->_values), array_values($this->_values), $query) . ";\n";

			// Reset this values
			$this->_values = [];
		}

		$this->reset();

		return $ids;
	}

	// Update___________________________________________________________________

	/**
	 * update
	 * 
	 * UPDATE tbl_name SET col_name1=expr1 , col_name2=expr2 ... WHERE column;
	 * 
	 * 			$db->table('users')
	 * 				->where('id', 1)
	 * 				->update(['votes' => 1]);
	 * 
	 * @param array $data
	 * @return array effected ids
	 */
	public function update($data)
	{
		/*
		 * Select for effected
		 */

		// Select for effected	
		$select_query = "select id from {$this->_table}";

		// where
		if ($this->_wheres)
		{
			$select_query .= ' ' . implode(' ', $this->_wheres);
		}

		$select_stt = $this->_connection->prepare($select_query);

		foreach ($this->_values as $k => $v)
		{
			$type = is_numeric($v) ? PDO::PARAM_INT : PDO::PARAM_STR;

			$select_stt->bindValue($k, $v, $type);
		}

		$select_stt->execute();
		$selecte_fetch = $select_stt->fetchAll(PDO::FETCH_ASSOC);
		$ids = Arr::pluck($selecte_fetch, 'id');

		/*
		 * Do delete 
		 */
		$sets = [];

		foreach ($data as $key => $value)
		{
			$conversion = self::conv($value);
			$sets[] = $key . ' = ' . $conversion;
		}

		$sets_str = implode(', ', $sets);

		$query = "update {$this->_table} set {$sets_str}";

		// where
		if ($this->_wheres)
		{

			$query .= ' ' . implode(' ', $this->_wheres);
		}

		$stt = $this->_connection->prepare($query);

		foreach ($this->_values as $k => $v)
		{
			$type = is_numeric($v) ? PDO::PARAM_INT : PDO::PARAM_STR;

			$stt->bindValue($k, $v, $type);
		}

		$stt->execute();

		// If has not effected row
		if (!$stt->rowCount())
		{
			$ids = [];
		}

		// Build sql querys
		$this->_query = str_replace(array_keys($this->_values), array_values($this->_values), $query) . ';';

		$this->reset();
		return $ids;
	}

	// Delete __________________________________________________________________

	/**
	 * delete
	 * 
	 * DELETE FROM tbl_name WHERE column;
	 * 
	 * 			db->table('users')
	 * 				->where('id', 1)
	 * 				->delete();
	 *  
	 * @return bool
	 * @return array effected ids
	 */
	public function delete($id = null)
	{
		//Here:: edit use id
		/*
		 * Select for effected	
		 */
		$select_query = "select id from {$this->_table}";

		// where
		if ($this->_wheres)
		{
			$select_query .= ' ' . implode(' ', $this->_wheres);
		}

		$select_stt = $this->_connection->prepare($select_query);

		foreach ($this->_values as $k => $v)
		{
			$type = is_numeric($v) ? PDO::PARAM_INT : PDO::PARAM_STR;

			$select_stt->bindValue($k, $v, $type);
		}

		$select_stt->execute();
		$selecte_fetch = $select_stt->fetchAll(PDO::FETCH_ASSOC);

		$ids = Arr::pluck($selecte_fetch, 'id');

		/*
		 * Do delete 
		 */
		$query = "delete from {$this->_table}";

		// where
		if ($this->_wheres)
		{

			$query .= ' ' . implode(' ', $this->_wheres);
		}

		$stt = $this->_connection->prepare($query);

		foreach ($this->_values as $key => $value)
		{
			$type = is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR;

			$stt->bindValue($key, $value, $type);
		}

		$stt->execute();

		// If has not effected row
		if (!$stt->rowCount())
		{
			$ids = [];
		}

		// Build sql query
		$this->_query = str_replace(array_keys($this->_values), array_values($this->_values), $query) . ';';

		$this->reset();
		return $ids;
	}
	
	//__________________________________________________________________________

	/**
	 * get query
	 * @return string
	 */
	public function get_query()
	{

		return $this->_query;
	}

	/**
	 * Close
	 */
	public function close()
	{
		$this->_connection = null;
	}

	/**
	 * reset
	 */
	public function reset()
	{
		$this->_selects = [];
		$this->_table = null;
		$this->_joins = [];
		$this->_left_joins = [];
		$this->_wheres = [];
		$this->_where_first = true;
		$this->_limit = null;
		$this->_offset = null;
		$this->_orders = [];
		$this->_groups = [];
		$this->_havings = [];
		$this->_values = [];
		$this->_as_object;
//		$this->_query;
//		$this->_results = null;
	}

	/**
	 * Conversions
	 * 
	 * @param string $value
	 * @return string
	 */
	public function conv($value)
	{
		$leng = count($this->_values);
		$conversion = ':conv' . $leng;

		$this->_values[$conversion] = $value;

		return $conversion;
	}

}
