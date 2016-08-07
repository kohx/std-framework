<?php

/**
 * Template class ( Wavebar )
 *
 * @package    Deraemon/Tamplate
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2018 Deraemons
 * @license    http://emon-cms.com
 */
require_once 'AutoLoader.php';

class Wavebar {

	protected $_type;
	protected $_thing;
	protected $_things_path;
	protected $_wraps_path;
	protected $_parts_path;
	protected $_wrap_thing_part_func;
	protected $_variables = [];

	public static function fact($segment, $things_path = null, $_wraps_path = null, $_parts_path = null)
	{
		return new Wavebar($segment, $things_path, $_wraps_path, $_parts_path);
	}

	function __construct($segment, $things_path = null, $wraps_path = null, $parts_path = null)
	{
		/*
		 * Set params
		 */
		$config = Config::fact('wavebar');

		// Get config type
		$this->_type = $config->get('type', 'file');

		// Get config tpl things path
		$this->_things_path = $things_path ? : $config->get('tpl.things');

		// Get config tpl things path		
		$this->_wraps_path = $wraps_path ? : $config->get('tpl.wraps');

		// Get config tpl things path		
		$this->_parts_path = $parts_path ? : $config->get('tpl.parts');

		/*
		 * Create template
		 */
		$this->_thing = $this->_get_things($segment);

		$wrap_thing = $this->_search_wrap($this->_thing);

		$wrap_thing_part = $this->_search_part($wrap_thing);

		$this->_wrap_thing_part_func = $this->_search_wb($wrap_thing_part);
	}

	/**
	 * Search wrap and thing into wrap
	 * @param string $thing
	 * @return string
	 */
	protected function _search_wrap($thing)
	{
		$matches = [];
		while (preg_match("/{\|@(.[^{\||\|}]*)\|}/", $thing, $matches))  //"/{{>(.[^{}]*)}}/"
		{
			list($key, $segment) = $matches;

			$wrap = $this->_getWrap($segment);

			$replacde = str_replace($key, '', $thing);

			$thing = str_replace($key, $replacde, $wrap);
		}

		return $thing;
	}

	protected function _search_part($sentence)
	{
		$matches = [];
		preg_match_all("/{\|>(.[^{\||\|}]*)\|}/", $sentence, $matches);

		list($keys, $segments) = $matches;

		$replaces = [];
		foreach ($segments as $segment)
		{
			$replaces[] = $this->_get_part($segment);
		}

		return str_replace($keys, $replaces, $sentence);
	}

	protected function _search_wb($sentence)
	{
		$matches = [];
		preg_match_all("/{\|(.[^{\||\|}]*)\|}/", $sentence, $matches, PREG_SET_ORDER);

		$wbs = [];
		foreach ($matches as $matche)
		{
			list($key, $pure_string) = $matche;
			$string = trim($pure_string);
			$replace = '';

			$flag_ignore = strpos($string, '!') === 0;
			$flag_func = strpos($string, '(') !== false;
			$flag_php_start = $string == '?';
			$flag_php_end = $string == '?/';
			$flag_each_end = $string == '*/';
			$flag_else = $string == '#-';
			$flag_if_end = in_array($string, ['#/', '^/']);
			$flag_direct = strpos($string, '&') === 0;
			$flag_if = strpos($string, '#') === 0;
			$flag_not = strpos($string, '^') === 0;
			$flag_each = strpos($string, '*') === 0;

			if ($flag_ignore)
			{
				$replace = '';
			}
			elseif ($flag_php_start)
			{
				$replace = '<?php ';
			}
			elseif ($flag_php_end)
			{
				$replace = ' ?>';
			}
			elseif ($flag_each_end)
			{
				$replace = '<?php }} ?>';
			}
			elseif ($flag_else)
			{
				$replace = '<?php  } else { ?>';
			}
			elseif ($flag_if_end)
			{
				$replace = '<?php } ?>';
			}
			elseif ($flag_direct)
			{
				$replace = '<?php ' . $this->_build_echo($string) . ' ?>';
			}
			elseif ($flag_func)
			{
				$replace = '<?php ' . $this->_build_func($string) . ' ?>';
			}
			elseif ($flag_if)
			{
				$replace = '<?php ' . $this->_build_if($string) . ' ?>';
			}
			elseif ($flag_not)
			{
				$replace = '<?php ' . $this->_build_if($string, true) . ' ?>';
			}
			elseif ($flag_each)
			{
				$replace = '<?php ' . $this->_build_each($string) . ' ?>';
			}
			else
			{
				$replace = '<?php ' . $this->_build_echo($string) . ' ?>';
			}

			$wbs[$key] = $replace;
		}

		return str_replace(array_keys($wbs), array_values($wbs), $sentence);
	}

	//--------------------------------------------------------------------------
	/**
	 * dot string change to array
	 * 
	 * 'user.name.last'  ->  $user['name']['last']
	 * @param string $string
	 * @return string
	 */
	protected function _to_arr($string)
	{
		$result = '$';

		$temps = explode('.', $string);
		$f = true;
		foreach ($temps as $temp)
		{
			if ($f)
			{
				$result .= $temp;
				$f = false;
			}
			else
			{
				$result .= '[\'' . $temp . '\']';
			}
		}

		return $result;
	}

	/**
	 * object to array
	 * 
	 * @param ArrayObject $obj
	 * @return type
	 */
	protected function _obj2arr($obj)
	{
		return json_decode(json_encode($obj), 1);
	}

	/**
	 * wavebar param adapt to php param
	 * 
	 * @param string $string
	 * @return string
	 */
	protected function _adapt_param($string)
	{
		if($string === '')
		{
			return '';
		}
		
		$params = explode(',', str_replace(',,', ',', str_replace(array('[', ']'), array('[,', ',]'), trim($string, ','))));
		
		foreach ($params as &$param)
		{
			$param = trim($param);
			
			if ($param == '[')
			{
				$param = $param;
			}
			elseif ($param == ']')
			{
				$param = $param . ',';
			}
			elseif (strtolower($param) == 'null')
			{
				$param = 'null' . ',';
			}
			elseif (strtolower($param) == 'true')
			{
				$param = 'true' . ',';
			}
			elseif (strtolower($param) == 'false')
			{
				$param = 'false' . ',';
			}
			elseif (is_numeric($param))
			{
				$param = $param . ',';
			}
			elseif (strpos($param, '\'') !== false)
			{
				$param = $param . ',';
			}
			else
			{
				$param = $this->_to_arr($param) . ',';
			}
		}
		unset($param);

		return trim(implode('', $params), ',');
	}

	/**
	 * wavebar param string rebuild to php param
	 * 
	 * @param string $string
	 * @return string
	 */
	protected function _rebuild_param($string)
	{
		$pre_params = explode(',', str_replace(',,', ',', str_replace(array('[', ']'), array(',[,', ',],'), trim($string, ','))));

		$params = [];
		foreach ($pre_params as &$pre_param)
		{
			$pre_param = trim($pre_param);

			if (strpos($pre_param, '=') !== false AND substr($pre_param, -1) !== '=')
			{
				$prm = explode('=', $pre_param);
				$params[] = trim(reset($prm)) . ' =';
				$params[] = trim(end($prm));
			}
			else
			{
				$params[] = $pre_param;
			}
		}

		foreach ($params as &$param)
		{
			if (strpos($param, '=') !== false)
			{
				$param = '$' . $param . ' ';
			}
			elseif ($param == '[')
			{
				$param = $param;
			}
			elseif ($param == ']')
			{
				$param = $param . ',';
			}
			elseif (strtolower($param) == 'null')
			{
				$param = 'null' . ',';
			}
			elseif (strtolower($param) == 'true')
			{
				$param = 'true' . ',';
			}
			elseif (strtolower($param) == 'false')
			{
				$param = 'false' . ',';
			}
			elseif (is_numeric($param))
			{
				$param = $param . ',';
			}
			elseif (strpos($param, '\'') !== false)
			{
				$param = $param . ',';
			}
			else
			{
				$param = $this->_to_arr($param) . ',';
			}
		}
		unset($param);
		
		return trim(implode('', $params), ',');
	}

	/**
	 * Build htmlspecialchars
	 * 
	 * @param type $string
	 * @return type
	 */
	protected function _build_echo($string)
	{
		if (stripos($string, '&') === 0)
		{
			$dot_str = trim(substr($string, 1));
			$variable = $this->_to_arr($dot_str);

			$result = "echo isset({$variable}) ? {$variable} : '';";
		}
		else
		{
			$dot_str = trim($string);
			$variable = $this->_to_arr($dot_str);

			$result = "echo Func::e(isset({$variable}) ? {$variable} : '');";
		}

		return $result;
	}

	/**
	 * Build Function
	 * 
	 * @param type $string
	 * @return type
	 */
	protected function _build_func($string)
	{
		$varable = null;
		$mathod = null;
		$result = '';

		$brace_pos = strpos($string, '(');
		$head = trim(substr($string, 0, $brace_pos));
		$foot = trim(substr($string, $brace_pos + 1, -1));

		if (strpos($head, '=') !== false)
		{
			list($pure_var, $pure_mathod) = explode('=', $head);
			$varable = '$' . trim($pure_var);
			$mathod = trim($pure_mathod);
			$result = "{$varable} = ";
		}
		else
		{
			$mathod = trim($head);
		}

		if (strpos($foot, '=') !== false)
		{
			// Coustom params
			$params = $this->_rebuild_param($foot);
		}
		else
		{
			// Usually params
			$params = $this->_adapt_param($foot);
		}

		$result .= "Func::{$mathod}({$params});";

		return $result;
	}

	/**
	 * Build IF
	 * 
	 * @param string $string
	 * @return string
	 */
	protected function _build_if($string, $not = false)// '#user.name.last'  -> if($user['name']['last']){
	{
		$result = '';
		$flag = $not ? '!' : '';

		if (strpos($string, ':') !== false)
		{
			list($pure_parent, $pure_child) = explode(':', $string);
			$parent = trim($pure_parent);
			$child = trim($pure_child);

			$dot_str = trim(substr($parent, 1));
			$parent_var = $this->_to_arr($dot_str);
			$child_var = '$' . $child;
			$result .= "{$child_var} = {$parent_var}; ";
			$result .= "if({$flag}{$child_var}){";
		}
		else
		{
			$dot_str = trim(substr($string, 1));
			$parent_var = $this->_to_arr($dot_str);
			$result .= "if({$flag}{$parent_var}){";
		}

		return $result;
	}

	/**
	 * Build Foreach
	 * 
	 * *users:user -> if($users){foreach($user){
	 * @param string $string
	 * @return string
	 */
	protected function _build_each($string) //*users:user
	{
		$result = '';

		list($pure_parent, $pure_child) = explode(':', $string);
		$parent = trim($pure_parent);
		$child = trim($pure_child);

		$dot_str = trim(substr($parent, 1));

		$parent_var = $this->_to_arr($dot_str);
		$child_var = '$' . $child;

		$result .= "if({$parent_var}){ foreach ({$parent_var} as {$child_var}){";

		return $result;
	}

	//--------------------------------------------------------------------------

	/**
	 * Get things
	 * 
	 * @param type $segment
	 * @return string
	 */
	protected function _get_things($segment)
	{
		if ($this->_type == 'file')
		{
			$things_dir = APPPATH . $this->_things_path;

			$thing = file_get_contents($things_dir . $segment . '.php');
		}
		else
		{
			$thing = Model_Thing::value($segment);
		}

		return $thing;
	}

	/**
	 * Get wraps
	 * 
	 * @param string $segment
	 * @return string
	 */
	protected function _getWrap($segment)
	{
		if ($this->_type == 'file')
		{
			$wraps_dir = APPPATH . $this->_wraps_path;

			$wrap = file_get_contents($wraps_dir . $segment . '.php');
		}
		else
		{
			$wrap = Model_Wrap::value($segment);
		}

		return $wrap;
	}

	/**
	 * Get part
	 * 
	 * @param string $segment
	 * @return string
	 */
	protected function _get_part($segment)
	{
		if ($this->_type == 'file')
		{
			$parts_dir = APPPATH . $this->_parts_path;

			$part = file_get_contents($parts_dir . $segment . '.php');
		}
		else
		{
			$part = Model_Part::value($segment);
		}

		return $part;
	}

	//--------------------------------------------------------------------------
	/**
	 * Set variables
	 * 
	 * @param string $key
	 * @param mix $value
	 * @return \Wavebar
	 */
	public function set($key, $value = null)
	{
		if (is_array($key))
		{
			foreach ($key as $name => $value)
			{
				$this->_variables[$name] = $value;
			}
		}
		else
		{
			$this->_variables[$key] = $value;
		}

		return $this;
	}

	public function bind($key, &$value = null)
	{
		if (is_array($key))
		{
			foreach ($key as $name => &$value)
			{
				$this->_variables[$name] = &$value;
			}
		}
		else
		{
			$this->_variables[$key] = &$value;
		}

		return $this;
	}

	public function get_content()
	{
		return $this->_wrap_thing_part_func;
	}

	/**
	 * Render
	 * 
	 * @return string
	 */
	public function render()
	{
		$variables = $this->_obj2arr($this->_variables);
		extract($variables);

		$fail = false;

		// If want to show builded code opnen this comment
//		 Debug::v($this->_wrap_thing_part_func);

		ob_start();

		if (eval('?>' . $this->_wrap_thing_part_func) === false)
		{
			$fail = true;
		}

		$result = ob_get_clean();

		if ($fail)
		{
			throw new RuntimeException(sprintf("Evaluation failed: %s", $result));
		}
		
		return $result;
	}

}
