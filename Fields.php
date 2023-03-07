<?php 
/**
 * @Package: 	PHP Fuse - Form builder
 * @Author: 	Daniel Ronkainen
 * @Licence: 	The MIT License (MIT), Copyright © Daniel Ronkainen
 				Don't delete this comment, its part of the license.
 * @Version: 	2.2.1
 */
namespace Form;

class Fields {

	
	private $_form;
	private $_type;
	private $_args = array();
	private $_inpArr = array();
	private $_values = array();
	private $_group;
	private $_buildArr;
	private $_factory;
	public $_validateArr = array();

	static private $_inst;

	function __construct(Templates\Fields $fields, ?array $factoryArr = NULL) {
		self::$_inst = $this;
		$this->_fields = $fields;
		if(!is_null($factoryArr)) {
			foreach($factoryArr as $key => $obj) $this->_factory[$key] = $obj;
		}
	}

	static function _inst() {
		return self::$_inst;
	}

	function factory(string $key) {
		return $this->_factory[$key];
	}

	function type() {
		return $this->_type;
	}

	function getForm() {		
		return $this->_fields;
	}

	function __call($a, $b) {
		// Reset build instance
		if(!is_null($this->_type)) {
			$class = get_class($this->_fields);
			$this->_fields = new $class();
		}

		$this->_fields->inst($this);
		$this->_type = $a;
		$this->_args = $b;
		return $this->_fields;
	}

	function values() {
		return $this->_values;
	}

	function setValues($values) {
		$values = (array)$values;
		foreach($values as $k => $val) {
			if(is_array($val) || strlen($val)) $this->_values[$k] = $val;
		}
		return $this;
	}

	function getFields($key) {
		return ($this->_inpArr[$key] ?? false);
	}

	function add($name, $fields) {
		$this->_inpArr[$name] = $fields;
		return $this;
	}

	function merge($name, $fields) {
		if(empty($this->_inpArr[$name])) $this->_inpArr[$name] = [];
		$this->_inpArr[$name] = array_merge($this->_inpArr[$name], $fields);
		return $this;
	}


	function addToCarrot(string $name, string $carrotName, array $fields, ?string $newForm = NULL) {
		$newArr = Array();

		foreach($this->_inpArr[$name] as $key => $arr) {
			if($carrotName === $key) {
				if(is_null($newForm)) {
					$newArr = array_merge($newArr, $fields);
				} else {
					unset($this->_inpArr[$name][$key]);
				}
			} else {
				$newArr[$key] = $arr;
			}
		}

		if(!is_null($newForm)) {
			$this->_inpArr[$newForm] = $fields;
		} else {
			$this->_inpArr[$name] = $newArr;
		}

		return $this;
	}

	
	function get() {
		if(!is_null($this->_type)) {
			$get = call_user_func_array([$this->_fields, $this->_type], $this->_args);
			return $get;
		}
	}

	function moveItemToForm(string $currentForm, string $newForm, array $keys) {
		$newArr = Array();
		foreach($this->_inpArr[$currentForm] as $key => $arr) {
			if(in_array($key, $keys)) {
				unset($this->_inpArr[$currentForm][$key]);
				$this->_inpArr[$newForm][$key] = $arr;
			}
		}

		return $this;
	}

	function mergeAfter($name, $key, $fields) {
		if(empty($this->_inpArr[$name])) $this->_inpArr[$name] = [];

		$newArr = array();
		foreach($this->_inpArr[$name] as $k => $row) {
			$newArr[$k] = $row;
			if($key === $k) {
				$nk = key($fields);
				$newArr[$nk] = $fields[$nk];
			}

		}
		$this->_inpArr[$name] = $newArr;
	

		return $this;
	}

	function mergeBefore($name, $key, $fields) {
		if(empty($this->_inpArr[$name])) $this->_inpArr[$name] = [];

		$newArr = array();
		foreach($this->_inpArr[$name] as $k => $row) {
			
			if($key === $k) {
				$nk = key($fields);
				$newArr[$nk] = $fields[$nk];
			}
			$newArr[$k] = $row;

		}
		$this->_inpArr[$name] = $newArr;
	

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
		return $this->_inpArr;
	}

	function setInpArr(array $arr) {
		$this->_inpArr = $arr;
		return $this;
	}

	function unset($name) {
		unset($this->_inpArr[$name]);
	}

	function delete($name, $key) {
		if(is_array($key)) {
			$this->_findDelete($this->_inpArr[$name], $key);
		} else {
			if(isset($this->_inpArr[$name][$key])) unset($this->_inpArr[$name][$key]);
		}
		return $this;
	}

	function prepend($name, $fields) {
		$this->_inpArr[$name] = array_merge($fields, $this->_inpArr[$name]);
		return $this;
	}

	function append($fields) {
		$this->_inpArr[$name] = array_merge($this->_inpArr[$name], $fields);
		return $this;
	}

	function replace(array $arr, array $args) {
		if(count($args) > 0) $arr = array_replace_recursive($arr, $args);
		return $arr;
	}

	private function _inputArray($array = false) {
		if(!is_array($array)) $array = $this->_inpArr;

		$get = array();
		foreach($array as $a1) {

			foreach($a1 as $k => $a2) {
				if(isset($a2['type'])) {					
					if(isset($a2['fields'])) {
						switch($a2['type']) {
							case "group":

								$this->_inputNestArray($a2['fields'], $get, $k);
							break;
							case "columns": case "checkShowFormPart":
								
								foreach($a2['fields'] as $row) {
									$this->_inputNestArray($row, $get, false);
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

	private function _inputNestArray($array, &$get, $key) {
		foreach($array as $k => $row) {
			if(isset($row['type'])) {
				$k1 = ($key) ? $key.",{$k}" : $k;
				if(isset($row['fields'])) {
					$this->_inputNestArray($row['fields'], $get, $k1);
				} else {
					$get[$k1] = $row;
				}
			}
		}
	}
	
	function data($key = false) {
		$arr = $this->_inputArray();
		return ($key !== false) ? (isset($arr[$key]) ? $arr[$key] : false) : $arr;
	}

	function validateArr($key = false) {
		$arr = $this->_validateArr;
		return ($key !== false) ? (isset($arr[$key]) ? $arr[$key] : false) : $arr;
	}


	function form($key) {
		return ($this->_buildArr[$key] ?? false);
	}

	function build($callback = false) {
		$this->_validateArr = array();
		foreach($this->_inpArr as $key => $array) {
			$this->_buildArr[$key] = $this->html($array, $callback);
		}
	}

	function html($inpArr = false, $callback = false) {
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
				$imageID = (isset($arr['imageID'])) ? $arr['imageID'] : false;
				$max = (isset($arr['max'])) ? (int)$arr['max'] : 0;
				$validate = (isset($arr['validate'])) ? $arr['validate'] : array();


				$valueFormat = (isset($arr['valueFormat'])) ? $arr['valueFormat'] : NULL;
				$encrypt = (isset($arr['encrypt'])) ? $arr['encrypt'] : NULL;

				$args = $field->rows($arr)->header($header)->label($label)->description($description)->encrypt($encrypt)->validate($validate)->imageID($imageID)->config($config)->name($name)
				->items($items)->itemsDescription($itemsDescription)->inp_type($inpType)->value($value)->fields($fields)
				->attr($attr)->db($db)->exclude($exclude)->class($class)->valueFormat($valueFormat)->conAttr($conAttr)->max($max);

				if($callback) $callback($args);
				$out .= $args->get();
			}
		}

		return $out;
	}
}
