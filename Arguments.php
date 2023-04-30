<?php 
/**
 * @Package: 	PHP Fuse - Form builder engine
 * @Author: 	Daniel Ronkainen
 * @Licence: 	The MIT License (MIT), Copyright © Daniel Ronkainen
 				Don't delete this comment, its part of the license.
 * @Version: 	1.0.0
 */

namespace PHPFuse\Form;

class Arguments {

	protected $rows = array();
	protected $attr;
	protected $attrArr = array();
	protected $db = array();
	protected $name;
	
	protected $identifier;
	protected $grpIdentifier;
	protected $value;
	protected $header;
	protected $label;
	protected $description;
	protected $exclude;
	protected $max = 0;
	protected $validate = array();

	protected $inpType = "text";

	// Container
	protected $class;
	protected $conAttr;
	protected $conAttrArr = array();


	protected $items = array();
	protected $itemsDescription = array();
	protected $config = array();
	protected $fields = array();


	protected $count = 0;

	protected $nameExp; // Used to get value
	protected $dataName;

	protected $inst;
	protected $level = 1;
	protected $valueFormat;
	protected $encrypt;
	protected $_inst;


	/**
	 * DEPRECATED
	 * protected $_imageID;
	 */

	/*
	function imageID($imageID = false) {
		$this->_imageID = $imageID;
		//$this->_imageID = $this->identifier;
		return $this;
	}
	 */

	
	function inst($inst) {
		$this->_inst = $inst;
		return $this;
	}


	function get() {
		$this->value();
		return $this->_inst->get();
	}

	
	function attr($arr) {
		if(is_array($arr)) {
			$this->attrArr = array_merge($this->attrArr, $arr);
			foreach($arr as $key => $value) $this->attr .= "{$key}=\"{$value}\" ";
		}
		return $this;
	}

	function conAttr($arr) {
		if(is_array($arr)) {
			$this->conAttrArr = array_merge($this->conAttrArr, $arr);
			foreach($arr as $key => $value) $this->conAttr .= "{$key}=\"{$value}\" ";
		}
		return $this;
	}

	function setAttr() {
		$this->attr = "";
		foreach($this->attrArr as $key => $value) $this->attr .= "{$key}=\"{$value}\" ";
		return $this->attr;
	}

	function setConAttr() {
		$this->conAttr = "";
		foreach($this->conAttrArr as $key => $value) $this->conAttr .= "{$key}=\"{$value}\" ";
		return $this->conAttr;
	}


	function db($arr) {
		if(is_array($arr)) {
			$this->db = array_merge($this->db, $arr);
		}
		return $this;
	}

	function config($arr) {
		if(is_array($arr)) $this->config = array_merge($this->config, $arr);
		return $this;
	}

	function rows($arr) {
		if(is_array($arr)) $this->rows = array_merge($this->rows, $arr);
		return $this;
	}

	function items($arr) {
		if(is_array($arr)) $this->items = $arr;
		return $this;
	}
	
	function itemsDescription($arr) {
		if(is_array($arr)) $this->itemsDescription = $arr;
		return $this;
	}

	function fields($arr) {
		if(is_array($arr)) $this->fields = $arr;
		return $this;
	}

	function class($str) {
		if($str) $this->class = $str;
		return $this;
	}

	function max($max) {
		if($max) $this->max = (int)$max;
		return $this;
	}

	function validate($arr) {
		if(is_array($arr)) $this->validate = $arr;
		return $this;
	}

	function exclude($exclude) {
		$this->exclude = explode(",", $exclude);
		return $this;
	}

	function encrypt($encrypt) {
		if($encrypt) $this->encrypt = $encrypt;
		return $this;
	}

	/**
	 * Format value with function/call_user_func_array, FIRST ARGUMENT IN args will always be value
	 * @param  array|null $arr [ ["method" => "base64_decode", "args" => [] ], ["method" => "openssl_decrypt", "args" => ['DES-EDE3', 'key', OPENSSL_RAW_DATA]] ]
	 * @return self
	 */
	function valueFormat(?array $arr = NULL) {
		if(is_array($arr)) {
			$this->valueFormat = $arr;
		}
		return $this;
	}


	// TEST DO NOT USE
	protected function buildGrp() {
		$new = array();
		if(is_null($this->value)) $this->value = array(0);
		foreach($this->value as $k => $a) {
			foreach($this->fields as $name => $arr) {
				$new["{$this->identifier},{$k},{$name}"] = $arr;
			}
		}
		return $new;
	}


	protected function columns() {
		$out = "";
		if(is_array($this->fields)) {
			ksort($this->fields);
			foreach($this->fields as $key => $array) {
				$out .= "<div class=\"col\">";
				$out .= $this->_inst->html($array);
				$out .= "</div>";
			}
		}
		return $out;
	}

	protected function groupFeed($callback, $autoFixKey = true) {

		$out = "";
		$fields = array();
		if(!is_array($this->value)) $this->value = array(0);

		foreach($this->value as $k => $a) {
			$o = "";
			foreach($this->fields as $name => $arr) {
				$fk = ($autoFixKey) ? "{$this->identifier},{$k},{$name}" : $name;
				$arr['imageID'] = "{$this->grpIdentifier},{$name}";

				$fields[$fk] = $arr;
				$o .= $this->_inst->html($fields);
				unset($fields);
			}
			$out .= $callback($o, $a);
			
		}
		return $out;
	}

	protected function group($autoFixKey = true) {
		$out = "";
		$fields = array();
		
		$o = "";
		foreach($this->fields as $name => $arr) {
			$fk = ($autoFixKey) ? "{$this->identifier},{$name}" : $name;
			$arr['imageID'] = "{$this->grpIdentifier},{$name}";

			$fields[$fk] = $arr;
			$o .= $this->_inst->html($fields);
			unset($fields);
		}		
		return $o;
	}


	protected function groupList($name, $callback) {

		$out = "";
		$fields = array();
		if(!is_array($this->value)) $this->value = array(0);

		foreach($this->value as $k => $a) {
			$o = "";
			$fk = "{$name},";
			$this->name($fk);
			$out .= $callback($k, $fk, $a);
		}
		return $out;
	}

	function label($label) {
		if($label) $this->label = $label;
		return $this;
	}

	function header($header) {
		if($header) $this->header = $header;
		return $this;
	}

	function description($description) {
		if($description) $this->description = $description;
		return $this;
	}
	
	function inp_type($type) {
		$this->inpType = ($type !== false) ? $type : "text";
		return $this;
	}

	function request() {
		return $this->_inst->request;
	}

	function name($name) {
		$this->grpIdentifier = $this->identifier = trim($name);
		$this->nameExp = $exp = explode(",", $this->identifier);
		$this->name = array_shift($exp);
		$this->dataName = end($this->nameExp);
		$this->grpIdentifier = preg_replace('/(,[0-9])+/', '', $this->grpIdentifier);

		$this->_inst->validateArr[$this->identifier]['id'] = ($this->rows['id'] ?? 0);
		$this->_inst->validateArr[$this->identifier]['type'] = $this->inpType;
		$this->_inst->validateArr[$this->identifier]['validate'] = $this->validate;
		$this->_inst->validateArr[$this->identifier]['encrypt'] = $this->encrypt;
		$this->_inst->validateArr[$this->identifier]['config'] = $this->config;

		foreach($exp as $item) {
			$this->name .= "[".htmlentities(trim($item))."]";
		}
		return $this;
	}



	function value($val = false) {
		if($val !== false) {
			$this->value = $val;

		} elseif(is_array($this->nameExp) && count($this->nameExp) > 0) {

			$values = $this->_inst->values();
			if(!is_null($values)) {
				$values = (array)$values;
				$exp = $this->nameExp;

				$first = array_shift($exp);
				if(isset($values[$first])) {
					$this->value = $values[$first];
					if(count($exp) > 0) {
						$this->value = $this->json($this->value);
						foreach($exp as $item) {
							$item = htmlentities(trim($item));
							$this->value = isset($this->value[$item]) ? $this->value[$item] : $val;
						}
					}
				}
			}
		}

		$this->setValueFormat();

		return $this;
	}

	function values() {
		return $this->_inst->values();
	}

	private function setValueFormat() {

		if(!is_null($this->encrypt)) {
			$this->value = base64_decode($this->value);
			$this->value = openssl_decrypt($this->value, 'DES-EDE3', 'key', OPENSSL_RAW_DATA);
		}

		if(!is_null($this->valueFormat)) {
			foreach($this->valueFormat as $arr) {
				$val = (is_string($this->value) ? $this->value : NULL);
				$arr['args'] = ($arr['args'] ?? []);
				array_unshift($arr['args'], $val);
				$this->value = call_user_func_array($arr['method'], $arr['args']);
			}
		}		
	}

	function getEncrypt() {
		return $this->encrypt;
	}

	function get_name() {
		return $this->name;
	}

	function get_value() {
		return $this->value;
	}

	function itemValue() {
		return (isset($this->items[$this->value])) ? $this->items[$this->value] : reset($this->items);
	}

	private function json($jsonStr) {
		if(is_string($jsonStr)) {
			$array = false;
			if(function_exists("json_decode_data")) {
				$array = json_decode_data($jsonStr);
			
			} else {
				$array = json_decode($jsonStr, true);
				if(!$array) throw new \Exception("JSON ERROR CODE: ".json_last_error(), 1);	
			}
			if($array) return $array;
		}
		return $jsonStr;
	}


	protected function isChecked($val): bool
	{
		if(is_array($this->value)) {
			return (bool)in_array((string)$val, $this->value);
		}
		return (bool)((string)$val === (string)$this->value);
	}
}
