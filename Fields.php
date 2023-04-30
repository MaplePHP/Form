<?php 
/**
 * @Package: 	PHP Fuse - Form builder
 * @Author: 	Daniel Ronkainen
 * @Licence: 	The MIT License (MIT), Copyright © Daniel Ronkainen
 				Don't delete this comment, its part of the license.
 * @Version: 	2.2.1
 */
namespace PHPFuse\Form;

use PHPFuse\Form\Interfaces\FormFieldsInterface;

class Fields {

	
	private $form;
	private $fields;
	private $type;
	private $args = array();
	private $inpArr = array();
	private $values = array();
	private $group;
	private $buildArr;
	private $factory;
	public $validateArr = array();

	static private $_inst;

	function __construct(FormFieldsInterface $fields, ?array $factoryArr = NULL) {
		self::$_inst = $this;
		$this->fields = $fields;
		if(!is_null($factoryArr)) {
			foreach($factoryArr as $key => $obj) $this->factory[$key] = $obj;
		}
	}

	
	function factory(string $key) {
		return $this->factory[$key];
	}

	function type() {
		return $this->type;
	}

	function values() {
		return $this->values;
	}

	function getForm() {		
		return $this->fields;
	}

	function getData() {
		return $this->inputArray();
	}

	function __call($a, $b) {
		// Reset build instance
		if(!is_null($this->type)) {
			$class = get_class($this->fields);
			$this->fields = new $class();
		}

		$this->fields->inst($this);
		$this->type = $a;
		$this->args = $b;
		return $this->fields;
	}

	

	function setValues($values) {
		$values = (array)$values;
		foreach($values as $k => $val) {
			if(is_array($val) || strlen($val)) $this->values[$k] = $val;
		}
		return $this;
	}

	function getFields($key) {
		return ($this->inpArr[$key] ?? false);
	}

	function add($name, $fields) {
		$this->inpArr[$name] = $fields;
		return $this;
	}

	function merge($name, $fields) {
		if(empty($this->inpArr[$name])) $this->inpArr[$name] = [];
		$this->inpArr[$name] = array_merge($this->inpArr[$name], $fields);
		return $this;
	}


	function addToCarrot(string $name, string $carrotName, array $fields, ?string $newForm = NULL) {
		$newArr = Array();

		foreach($this->inpArr[$name] as $key => $arr) {
			if($carrotName === $key) {
				if(is_null($newForm)) {
					$newArr = array_merge($newArr, $fields);
				} else {
					unset($this->inpArr[$name][$key]);
				}
			} else {
				$newArr[$key] = $arr;
			}
		}

		if(!is_null($newForm)) {
			$this->inpArr[$newForm] = $fields;
		} else {
			$this->inpArr[$name] = $newArr;
		}

		return $this;
	}

	
	function get() {
		if(!is_null($this->type)) {
			$get = call_user_func_array([$this->fields, $this->type], $this->args);
			return $get;
		}
	}

	function moveItemToForm(string $currentForm, string $newForm, array $keys) {
		$newArr = Array();
		foreach($this->inpArr[$currentForm] as $key => $arr) {
			if(in_array($key, $keys)) {
				unset($this->inpArr[$currentForm][$key]);
				$this->inpArr[$newForm][$key] = $arr;
			}
		}

		return $this;
	}

	function mergeAfter($name, $key, $fields) {
		if(empty($this->inpArr[$name])) $this->inpArr[$name] = [];

		$newArr = array();
		foreach($this->inpArr[$name] as $k => $row) {
			$newArr[$k] = $row;
			if($key === $k) {
				$nk = key($fields);
				$newArr[$nk] = $fields[$nk];
			}

		}
		$this->inpArr[$name] = $newArr;
	

		return $this;
	}

	function mergeBefore($name, $key, $fields) {
		if(empty($this->inpArr[$name])) $this->inpArr[$name] = [];

		$newArr = array();
		foreach($this->inpArr[$name] as $k => $row) {
			
			if($key === $k) {
				$nk = key($fields);
				$newArr[$nk] = $fields[$nk];
			}
			$newArr[$k] = $row;

		}
		$this->inpArr[$name] = $newArr;
	

		return $this;
	}

	private function _findDelete(&$array, $key) {
		$k = array_shift($key);
		if(isset($array[$k])) {
			if(count($key) > 0) {
				$this->_findDelete($array[$k], $key);
			} else {
				unset($array[$k]);
			}
		}
	}
	
	function inpArr() {
		return $this->inpArr;
	}

	function setInpArr(array $arr) {
		$this->inpArr = $arr;
		return $this;
	}

	function unset($name) {
		unset($this->inpArr[$name]);
	}

	function delete($name, $key) {
		if(is_array($key)) {
			$this->_findDelete($this->inpArr[$name], $key);
		} else {
			if(isset($this->inpArr[$name][$key])) unset($this->inpArr[$name][$key]);
		}
		return $this;
	}

	function prepend($name, $fields) {
		$this->inpArr[$name] = array_merge($fields, $this->inpArr[$name]);
		return $this;
	}

	function append($fields) {
		$this->inpArr[$name] = array_merge($this->inpArr[$name], $fields);
		return $this;
	}

	function replace(array $arr, array $args) {
		if(count($args) > 0) $arr = array_replace_recursive($arr, $args);
		return $arr;
	}

	
	function data(?string $key = NULL): array|string 
	{
		$arr = $this->inputArray();
		return (!is_null($key)) ? (isset($arr[$key]) ? $arr[$key] : false) : $arr;
	}

	function validateArr(?string $key = NULL): array|string 
	{
		$arr = $this->validateArr;
		return (!is_null($key)) ? (isset($arr[$key]) ? $arr[$key] : false) : $arr;
	}

	/**
	 * Build all form data
	 * @param  callable|null $callback [description]
	 * @return [type]                  [description]
	 */
	public function build(?callable $callback = NULL) 
	{
		// Reset validate arr, it will be re-built
		$this->validateArr = array();
		foreach($this->inpArr as $key => $array) {
			$this->buildArr[$key] = $this->html($array, $callback);
		}
	}

	/**
	 * Get built form
	 * @param  string $key form key
	 * @return string|NULL
	 */
	function form(string $key): ?string
	{
		return ($this->buildArr[$key] ?? NULL);
	}

	/**
	 * Build HTML
	 * @param  array $inpArr
	 * @param  callable $callback
	 * @return string
	 */
	protected function html(array $inpArr, ?callable $callback = NULL): string 
	{
		$out = "";
		foreach($inpArr as $name => $arr) {
			if(isset($arr['type'])) {
				$field = $this->{$arr['type']}();
				$value = (isset($arr['value'])) ? $arr['value'] : false;
				$attr = (isset($arr['attr'])) ? $arr['attr'] : false;
				$db = (isset($arr['db'])) ? $arr['db'] : false;
				$header = (isset($arr['header'])) ? $arr['header'] : false;
				$label = (isset($arr['label'])) ? $arr['label'] : false;
				$description = (isset($arr['description'])) ? $arr['description'] : false;
				$inpType = (isset($arr['inp-type'])) ? $arr['inp-type'] : false;
				$config = (isset($arr['config'])) ? $arr['config'] : array();
				$items = (isset($arr['items'])) ? $arr['items'] : array();
				$itemsDescription = (isset($arr['items-description'])) ? $arr['items-description'] : array();

				
				$fields = (isset($arr['fields'])) ? $arr['fields'] : array();
				$class = (isset($arr['class'])) ? $arr['class'] : false;
				$conAttr = (isset($arr['conAttr'])) ? $arr['conAttr'] : false;
				$exclude = (isset($arr['exclude'])) ? $arr['exclude'] : false;
				//$imageID = (isset($arr['imageID'])) ? $arr['imageID'] : false;
				$max = (isset($arr['max'])) ? (int)$arr['max'] : 0;
				$validate = (isset($arr['validate'])) ? $arr['validate'] : array();


				$valueFormat = (isset($arr['valueFormat'])) ? $arr['valueFormat'] : NULL;
				$encrypt = (isset($arr['encrypt'])) ? $arr['encrypt'] : NULL;

				//->imageID($imageID)
				$args = $field->rows($arr)->header($header)->label($label)->description($description)->encrypt($encrypt)->validate($validate)->config($config)->name($name)
				->items($items)->itemsDescription($itemsDescription)->inp_type($inpType)->value($value)->fields($fields)
				->attr($attr)->db($db)->exclude($exclude)->class($class)->valueFormat($valueFormat)->conAttr($conAttr)->max($max);

				if(!is_null($callback)) $callback($args);
				$out .= $args->get();
			}
		}

		return $out;
	}

	private function inputArray(?array $array = NULL): array
	{
		if(!is_array($array)) $array = $this->inpArr;

		$get = array();
		foreach($array as $a1) {
			foreach($a1 as $k => $a2) {
				if(isset($a2['type'])) {					
					if(isset($a2['fields'])) {
						switch($a2['type']) {
							case "group":
								$this->inputNestArray($a2['fields'], $get, $k);
							break;
							case "columns": case "checkShowFormPart":
								foreach($a2['fields'] as $row) {
									$this->inputNestArray($row, $get, false);
								}
							break;
						}

					} else {
						$get[$k] = $a2;
					}
				}
			}
		}
		return $get;
	}

	private function inputNestArray(array $array, array &$get, string $key): void 
	{
		foreach($array as $k => $row) {
			if(isset($row['type'])) {
				$k1 = ($key) ? $key.",{$k}" : $k;
				if(isset($row['fields'])) {
					$this->inputNestArray($row['fields'], $get, $k1);
				} else {
					$get[$k1] = $row;
				}
			}
		}
	}


}
