<?php

/**
 * Description of model
 *
 * @author okuda
 */

class Model {

	protected static $_table;

	public static function value($value)
	{		
		$query = DB::fact(DB::SELECT)
				->select('value')
				->table(static::$_table);

		if (is_numeric($value))
		{
			$query->where('id', '=', $value);
		}
		else
		{
			$query->where('segment', '=', $value);
		}
		
		$result = $query->execute();

		return reset($result)['value'];
	}

}
