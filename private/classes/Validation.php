<?php

/**
 * validation class
 *
 * @package    Deraemon/Validation
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2016 Deraemons
 * @license    http://emon-cms.com/license
 */
require_once 'AutoLoader.php';

class Validation {

	protected $_data = [];
	protected $_fields = [];
	protected $_file = 'validation';
	protected $_translate = true;
	protected $_errors = [];

	/**
	 * Factory
	 * 			
	 * 			$validation = Validation::fact($post);
	 * 
	 * @param array $data
	 * @return \Validation
	 */
	public static function fact(array $data)
	{
		return new Validation($data);
	}

	/**
	 * Constructor
	 * 
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		// Keep original data
		$this->_data = $data;
		
		// Get file from config
		$this->_file = Config::fact('validation')->get('file');

		// Build field array
		foreach ($data as $field => $value)
		{
			$this->_fields[$field] = [
				'value' => is_null($value) ? '' : $value,
				'name' => $field,
				'label' => '',
				'rules' => [],
				'error' => '',
			];
		}
	}

	/**
	 * Addition label
	 * 
	 * 			$validation->label('name', __('name'))
	 * 
	 * @param string $field
	 * @param string $label
	 * @return \Validation
	 */
	public function label($field, $label)
	{
		// Set the label for this field
		$this->_fields[$field]['label'] = $label;

		return $this;
	}

	/**
	 * Addition labels using an array
	 * 
	 * 			$validation->labels(['
	 * 				[name', __('name')],
	 * 				[email', __('email')],
	 * 				[age', '年齢'],
	 * 			]);
	 * 
	 * @param array $labels
	 * @return \Validation
	 */
	public function labels(array $labels)
	{
		foreach ($labels as $field => $label)
		{
			$this->_fields[$field]['label'] = $label;
		}

		return $this;
	}

	/**
	 * Add rule
	 * 
	 * 	:validation is validation datas..
	 *  :value is check value. 
	 * 
	 * 			$validation
	 * 			->rule('name', 'not_empty')
	 * 			->rule('age', 'numeric')
	 * 			->rule('age', 'alpha')
	 * 			->rule('name', 'alpha_dash')
	 * 			->rule('name', 'alpha_numeric')
	 * 			->rule('star', 'color')
	 * 			->rule('birthday', 'date')
	 * 			->rule('number', 'credit_card')
	 * 			->rule('number', 'decimal', [':value', 2])
	 * 			->rule('number', 'digit')
	 * 			->rule('yourmail', 'email')
	 * 			->rule('yourmail', 'email_domain')
	 * 			->rule('aaa', 'equals', 'bbb')
	 * 			->rule('name', 'exact_length', [':value', 100])
	 * 			->rule('name', 'exact_length')
	 * 			->rule('name', 'in_array', [1, 2]);
	 * 			->rule('num', 'ip');
	 * 			->rule('name', 'matches', [':validation', 'username', 'confirm', __(confirm)]);
	 * 			->rule('name', 'min_length', [':value', 10]);
	 * 			->rule('name', 'max_length', [':value', 20]);
	 * 			->rule('number', 'phone');
	 * 			->rule('number', 'range', [':value', 1, 99]);
	 * 			->rule('name', 'regex', [':value', ['/*asd/']]);
	 * 			->rule('address', 'url');
	 * 			->rule('email', ['Model_Home', 'tvalid'])
	 * 			->rule('age', 'Model_Home::tvalid')
	 * 			->rule('name', function($a, $b, $c)
	 * 				{
	 * 					return false;
	 * 				}, [1, 2, 3], 'original')
	 * 
	 * @param string $field
	 * @param mix $rule
	 * @param array $params
	 * @param array $invalid for closure
	 * @return \Validation
	 */
	public function rule($field, $rule, array $params = null, $invalid = null)
	{
		if ($params === NULL)
		{
			// Default to array(':value')
			$params = [':value'];
		}

		$this->_fields[$field]['rules'][] = ['method' => $rule, 'params' => $params, 'invalid' => $invalid];

		return $this;
	}

	/**
	 * Add rules using an array
	 * 
	 * 			->rules('username', [
	 * 									['min_length', [':value', 4]],
	 * 									['max_length', [':value', 10]],
	 * 								]);
	 *
	 * @param   string  $field  field name
	 * @param   array   $rules  list of callbacks
	 * @return  \Validation
	 */
	public function rules($field, array $rules)
	{
		foreach ($rules as $rule)
		{
			$this->_fields[$field]['rules'][] = ['method' => Arr::get($rule, 0), 'params' => Arr::get($rule, 1, [':value']), 'invalid' => Arr::get($rule, 2)];
		}

		return $this;
	}

	/**
	 * Executes all validation rules. This should
	 * typically be called within an if/else block.
	 *
	 *     if ($validation->check())
	 *     {
	 *          // The data is valid, do something here
	 *     }
	 *
	 * @return  boolean
	 *
	 * foreach ($rules);
	 */
	public function check()
	{
		// Iterate fields
		foreach ($this->_fields as &$field)
		{
			// Set label
			if (!$field['label'])
			{
				$field['label'] = $field['name'];
			}
			
			// Set value to rule top
			foreach ($field['rules'] as &$rule)
			{
				// Replace param string
				foreach ($rule['params'] as &$param)
				{
					if ($param == ':value')
					{
						$param = $field['value'];
					}

					if ($param == ':validation')
					{
						$param = $this->_data;
					}
				}
			}
		}
		unset($field, $rule);

		// check each valid
		foreach ($this->_fields as &$field)
		{

			foreach ($field['rules'] as $rule)
			{
				$method = $rule['method'];
				$params = $rule['params'];
				$invalid = $rule['invalid'];

				// not empty or hos not value continue
				if (!($method === 'not_empty' OR $field['value'] !== ''))
				{
					break;
				}

				// Callback from here
				if (is_array($method))
				{
					// Allows rule('field', array(':model', 'some_rule'));
					if (is_string($method[0]) AND method_exists($method[0], $method[1]))
					{
						// This is an array callback, the method name is the error name
						if (!call_user_func_array($method, $params))
						{
							$this->_errors[] = [
								'name' => $field['name'],
								'label' => $field['label'],
								'params' => $params,
								'invalid' => $invalid ?: $method[0] . '->' . $method[1],
							];

							break;
						}
					}
				}
				elseif (!is_string($method))
				{
					// This is a lambda function, there is no error name (errors must be added manually)
					if (!call_user_func_array($method, $params))
					{
						$this->_errors[] = [
							'name' => $field['name'],
							'label' => $field['label'],
							'params' => $params,
							'invalid' => $invalid,
						];

						break;
					}
				}
				elseif (method_exists('Valid', $method))
				{
					// Call static::$rule($this[$data], $param, ...)
					if (!call_user_func_array(['Valid', $method], $params))
					{
						$this->_errors[] = [
							'name' => $field['name'],
							'label' => $field['label'],
							'params' => $params,
							'invalid' =>  $invalid ?: $method,
						];

						break;
					}
				}
				elseif (strpos($method, '::') === false)
				{
					// Call $function($this[$data], $param, ...)
					if (!call_user_func_array($method, $params))
					{
						$this->_errors[] = [
							'field' => $field['name'],
							'label' => $field['label'],
							'params' => $params,
							'invalid' =>  $invalid ?: $method,
						];

						break;
					}
				}
				else
				{
					// Split the class and method of the rule
					$class_method = explode('::', $method, 2);

					// Call $function($this[$data], $param, ...)
					if (!call_user_func_array($class_method, $params))
					{
						$this->_errors[] = [
							'name' => $field['name'],
							'label' => $field['label'],
							'params' => $params,
							'invalid' =>  $invalid ?: $method,
						];

						break;
					}
				}
			}
		}

		return count($this->_errors) === 0 ? true : false;
	}

	/**
	 * Get errors
	 * 
	 * 			   $validation->errors()
	 * 			or $validation->errors('validation2')
	 * 			or $validation->errors('validation2', false)
	 * 
	 * @return array
	 */
	public function errors($file = null, $translate = true)
	{
		// Set file
		if ($file)
		{
			$this->_file = $file;
		}

		// Set translate
		$this->_translate = $translate;

		// Declare messages
		$messages = [];

		// Iterate errors
		foreach ($this->_errors as &$error)
		{
			// Get valid message
			$string = Message::bring($this->_file, $error['invalid'], $error['invalid']);

			// Declare params
			$params = [];

			// Set label
			$params[':label'] = $error['label'];

			// Build param and set
			$i = 1;
			foreach ($error['params'] as $param)
			{
				$params[':param' . $i] = is_array($param) ? 'array' : $param;

				$i++;
			}

			// If 
			if ($translate)
			{
				// taranslate
				$messages[] = __($string, $params);
			}
			else
			{
				// not translate
				$messages[] = empty($params) ? $string : str_replace(array_keys($params), array_values($params), $string);
			}
		}

		return $messages;
	}
	
	
}
