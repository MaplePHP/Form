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

	protected $_rows = array();
	protected $_attr;
	protected $_attrArr = array();
	protected $_db = array();
	protected $_name;
	protected $_test;
	protected $_imageID;
	protected $_identifier;
	protected $_grpIdentifier;
	protected $_value;
	protected $_header;
	protected $_label;
	protected $_description;
	protected $_exclude;
	protected $_max = 0;
	protected $_validate = array();

	protected $_inpType = "text";

	// Container
	protected $_class;
	protected $_conAttr;
	protected $_conAttrArr = array();


	protected $_items = array();
	protected $_itemsDescription = array();
	protected $_config = array();
	protected $_fields = array();


	protected $_count = 0;

	protected $_nameExp; // Used to get value
	protected $_dataName;

	protected $_inst;
	protected $_level = 1;
	protected $_valueFormat;
	protected $_encrypt;

	
	function inst($inst) {
		$this->_inst = $inst;
		return $this;
	}

	function imageID($imageID = false) {
		$this->_imageID = $imageID;
		//$this->_imageID = $this->_identifier;
		return $this;
	}

	function get() {
		$this->value();
		return $this->_inst->get();
	}

	
	function attr($arr) {
		if(is_array($arr)) {
			$this->_attrArr = array_merge($this->_attrArr, $arr);
			foreach($arr as $key => $value) $this->_attr .= "{$key}=\"{$value}\" ";
		}
		return $this;
	}

	function conAttr($arr) {
		if(is_array($arr)) {
			$this->_conAttrArr = array_merge($this->_conAttrArr, $arr);
			foreach($arr as $key => $value) $this->_conAttr .= "{$key}=\"{$value}\" ";
		}
		return $this;
	}

	function setAttr() {
		$this->_attr = "";
		foreach($this->_attrArr as $key => $value) $this->_attr .= "{$key}=\"{$value}\" ";
		return $this->_attr;
	}

	function setConAttr() {
		$this->_conAttr = "";
		foreach($this->_conAttrArr as $key => $value) $this->_conAttr .= "{$key}=\"{$value}\" ";
		return $this->_conAttr;
	}


	function db($arr) {
		if(is_array($arr)) {
			$this->_db = array_merge($this->_db, $arr);
		}
		return $this;
	}

	function config($arr) {
		if(is_array($arr)) $this->_config = array_merge($this->_config, $arr);
		return $this;
	}

	function rows($arr) {
		if(is_array($arr)) $this->_rows = array_merge($this->_rows, $arr);
		return $this;
	}

	function items($arr) {
		if(is_array($arr)) $this->_items = $arr;
		return $this;
	}
	
	function itemsDescription($arr) {
		if(is_array($arr)) $this->_itemsDescription = $arr;
		return $this;
	}

	function fields($arr) {
		if(is_array($arr)) $this->_fields = $arr;
		return $this;
	}

	function class($str) {
		if($str) $this->_class = $str;
		return $this;
	}

	function max($max) {
		if($max) $this->_max = (int)$max;
		return $this;
	}

	function validate($arr) {
		if(is_array($arr)) $this->_validate = $arr;
		return $this;
	}

	function exclude($exclude) {
		$this->_exclude = explode(",", $exclude);
		return $this;
	}

	function encrypt($encrypt) {
		if($encrypt) $this->_encrypt = $encrypt;
		return $this;
	}

	/**
	 * Format value with function/call_user_func_array, FIRST ARGUMENT IN args will always be value
	 * @param  array|null $arr [ ["method" => "base64_decode", "args" => [] ], ["method" => "openssl_decrypt", "args" => ['DES-EDE3', 'key', OPENSSL_RAW_DATA]] ]
	 * @return self
	 */
	function valueFormat(?array $arr = NULL) {
		if(is_array($arr)) {
			$this->_valueFormat = $arr;
		}
		return $this;
	}


	// TEST DO NOT USE
	protected function _buildGrp() {
		$new = array();
		if(is_null($this->_value)) $this->_value = array(0);
		foreach($this->_value as $k => $a) {
			foreach($this->_fields as $name => $arr) {
				$new["{$this->_identifier},{$k},{$name}"] = $arr;
			}
		}
		return $new;
	}


	protected function _columns() {
		$out = "";
		if(is_array($this->_fields)) {
			ksort($this->_fields);
			foreach($this->_fields as $key => $array) {
				$out .= "<div class=\"col\">";
				$out .= $this->_inst->html($array);
				$out .= "</div>";
			}
		}
		return $out;
	}

	protected function _groupFeed($callback, $autoFixKey = true) {

		$out = "";
		$fields = array();
		if(!is_array($this->_value)) $this->_value = array(0);

		foreach($this->_value as $k => $a) {
			$o = "";


			foreach($this->_fields as $name => $arr) {
				$fk = ($autoFixKey) ? "{$this->_identifier},{$k},{$name}" : $name;
				$arr['imageID'] = "{$this->_grpIdentifier},{$name}";

				$fields[$fk] = $arr;
				$o .= $this->_inst->html($fields);
				unset($fields);
			}
			$out .= $callback($o, $a);
			
		}
		return $out;
	}

	protected function _group($autoFixKey = true) {
		$out = "";
		$fields = array();
		
		$o = "";
		foreach($this->_fields as $name => $arr) {
			$fk = ($autoFixKey) ? "{$this->_identifier},{$name}" : $name;
			$arr['imageID'] = "{$this->_grpIdentifier},{$name}";

			$fields[$fk] = $arr;
			$o .= $this->_inst->html($fields);
			unset($fields);
		}		
		return $o;
	}


	protected function _groupList($name, $callback) {

		$out = "";
		$fields = array();
		if(!is_array($this->_value)) $this->_value = array(0);

		foreach($this->_value as $k => $a) {
			$o = "";
			$fk = "{$name},";
			$this->name($fk);
			$out .= $callback($k, $fk, $a);
		}
		return $out;
	}

	function label($label) {
		if($label) $this->_label = $label;
		return $this;
	}

	function header($header) {
		if($header) $this->_header = $header;
		return $this;
	}

	function description($description) {
		if($description) $this->_description = $description;
		return $this;
	}
	
	function inp_type($type) {
		$this->_inpType = ($type !== false) ? $type : "text";
		return $this;
	}

	function request() {
		return $this->_inst->request;
	}

	function name($name) {
		$this->_grpIdentifier = $this->_identifier = trim($name);
		$this->_nameExp = $exp = explode(",", $this->_identifier);
		$this->_name = array_shift($exp);
		$this->_dataName = end($this->_nameExp);

		$this->_grpIdentifier = preg_replace('/(,[0-9])+/', '', $this->_grpIdentifier);
		//$this->_grpIdentifier = str_replace(",,", ",", $this->_grpIdentifier);

		


		$this->_inst->_validateArr[$this->_identifier]['id'] = ($this->_rows['id'] ?? 0);
		$this->_inst->_validateArr[$this->_identifier]['type'] = $this->_inpType;
		$this->_inst->_validateArr[$this->_identifier]['validate'] = $this->_validate;
		$this->_inst->_validateArr[$this->_identifier]['encrypt'] = $this->_encrypt;
		$this->_inst->_validateArr[$this->_identifier]['config'] = $this->_config;
		
			

		foreach($exp as $item) {
			$this->_name .= "[".htmlentities(trim($item))."]";
		}
		return $this;
	}



	function value($val = false) {
		if($val !== false) {
			$this->_value = $val;

		} elseif(is_array($this->_nameExp) && count($this->_nameExp) > 0) {

			$values = $this->_inst->values();
			if(!is_null($values)) {
				$values = (array)$values;
				$exp = $this->_nameExp;

				$first = array_shift($exp);
				if(isset($values[$first])) {
					$this->_value = $values[$first];
					if(count($exp) > 0) {
						$this->_value = $this->_json($this->_value);
						foreach($exp as $item) {
							$item = htmlentities(trim($item));
							$this->_value = isset($this->_value[$item]) ? $this->_value[$item] : $val;
						}
					}
				}
			}
		}
	

		$this->_valueFormat();


		return $this;
	}

	function values() {
		return $this->_inst->values();
	}

	private function _valueFormat() {

		if(!is_null($this->_encrypt)) {
			$this->_value = base64_decode($this->_value);
			$this->_value = openssl_decrypt($this->_value, 'DES-EDE3', 'key', OPENSSL_RAW_DATA);
		}

		if(!is_null($this->_valueFormat)) {
			foreach($this->_valueFormat as $arr) {
				$val = (is_string($this->_value) ? $this->_value : NULL);
				$arr['args'] = ($arr['args'] ?? []);
				array_unshift($arr['args'], $val);
				$this->_value = call_user_func_array($arr['method'], $arr['args']);
			}
		}		
	}

	function getEncrypt() {
		return $this->_encrypt;
	}

	function get_name() {
		return $this->_name;
	}

	function get_value() {
		return $this->_value;
	}

	function itemValue() {
		return (isset($this->_items[$this->_value])) ? $this->_items[$this->_value] : reset($this->_items);
	}

	private function _json($jsonStr) {
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
		if(is_array($this->_value)) {
			return (bool)in_array((string)$val, $this->_value);
		}
		return (bool)((string)$val === (string)$this->_value);
	}
}
