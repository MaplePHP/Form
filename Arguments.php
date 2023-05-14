<?php 
/**
 * @Package: 	PHPFuse - Form builder engine
 * @Author: 	Daniel Ronkainen
 * @Licence: 	The MIT License (MIT), Copyright © Daniel Ronkainen
 				Don't delete this comment, its part of the license.
 */

namespace PHPFuse\Form;

class Arguments {

	protected $rows = array();
	protected $fieldType;
	protected $attr;
	protected $attrArr = array();
	protected $name;
	
	protected $identifier;
	protected $grpIdentifier;
	protected $value;
	protected $header;
	protected $label;
	protected $description;
	protected $exclude;
	protected $validate = array();

	// Container
	protected $class;
	protected $conAttr;
	protected $conAttrArr = array();


	protected $items = array();
	protected $config = array();
	protected $fields = array();


	protected $count = 0;

	protected $nameExp; // Used to get value
	protected $dataName;

	protected $inst;
	protected $level = 1;
	protected $_inst;
	
	
	function inst($inst) {
		$this->_inst = $inst;
		return $this;
	}

	function getFieldType() {
		return $this->fieldType;
	}

	function getName() {
		return $this->name;
	}

	function getValue() {
		return $this->value;
	}
	

	function get() {
		$this->value();
		return $this->_inst->get();
	}

	function fieldType(string $fieldType) {
		$this->fieldType = $fieldType;
		return $this;
	}
	
	function attr($arr) {
		if(is_array($arr)) {
			$this->attrArr = array_merge($this->attrArr, $arr);
			$this->setAttr();
		}
		return $this;
	}

	function setAttr() {
		$this->attr = "";
		foreach($this->attrArr as $key => $value) $this->attr .= "{$key}=\"{$value}\" ";
		return $this->attr;
	}

	function conAttr($arr) {
		if(is_array($arr)) {
			$this->conAttrArr = array_merge($this->conAttrArr, $arr);
			//$this->setConAttr();
		}
		return $this;
	}

	function getConAttr() {
		$this->conAttr = "";
		foreach($this->conAttrArr as $key => $value) $this->conAttr .= "{$key}=\"{$value}\" ";
		return $this->conAttr;
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

	function fields($arr) {
		if(is_array($arr)) $this->fields = $arr;
		return $this;
	}

	function validate($arr) {
		if(is_array($arr)) $this->validate = $arr;
		return $this;
	}

	function label($label) {
		if($label) $this->label = $label;
		return $this;
	}

	function description($description) {
		if($description) $this->description = $description;
		return $this;
	}

	function request() {
		return $this->_inst->request;
	}

	function values() {
		return $this->_inst->getValues();
	}

	function name($name) {
		$this->grpIdentifier = $this->identifier = trim($name);
		$this->nameExp = $exp = explode(",", $this->identifier);
		$this->name = array_shift($exp);
		$this->dataName = end($this->nameExp);
		$this->grpIdentifier = preg_replace('/(,[0-9])+/', '', $this->grpIdentifier);


		$this->_inst->setValidateData($this->identifier, [
			"id" => ($this->rows['id'] ?? 0),
			"type" => (!is_null($this->fieldType) ? $this->fieldType : "text"),
			"validate" => $this->validate,
			"config" => $this->config
		]);
		
		foreach($exp as $item) {
			$this->name .= "[".htmlentities(trim($item))."]";
		}
		return $this;
	}



	function value($val = false) {
		if($val !== false) {
			$this->value = $val;

		} elseif(is_array($this->nameExp) && count($this->nameExp) > 0) {

			$values = $this->_inst->getValues();
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

		return $this;
	}

	/**
	 * Group fields / custom fields with dynamic and nested fields names
	 * @param  callable $callback   	Container room for customization 
	 * @param  bool 	$manipulateName Manipulate the input field name 
	 * @return string
	 */
	protected function groupFields(callable $callback, bool $manipulateName = true) {
		$out = "";
		$fields = array();
		if(!is_array($this->value)) $this->value = array(0); // This will add new value
		foreach($this->value as $k => $a) {
			$o = "";
			foreach($this->fields as $name => $arr) {
				$fk = ($manipulateName) ? "{$this->identifier},{$k},{$name}" : $name;
				$fields[$fk] = $arr;
				$o .= $this->_inst->html($fields);
				unset($fields);
			}
			$out .= $callback($o, $a);
		}
		return $out;
	}

	/**
	 * This will inherit the parent name and build upon it.
	 * @return string
	 */
	protected function inheritField() {
		$o = $out = "";
		$fields = array();
		foreach($this->fields as $name => $arr) {
			$fk = "{$this->identifier},{$name}";
			$fields[$fk] = $arr;
			$o .= $this->_inst->html($fields);
			unset($fields);
		}		
		return $o;
	}

	/**
	 * Check if filed is checked/active
	 * @param  string  $val
	 * @return boolean
	 */
	protected function isChecked($val): bool
	{
		if(is_array($this->value)) {
			return (bool)in_array((string)$val, $this->value);
		}
		return (bool)((string)$val === (string)$this->value);
	}

	/**
	 * Get the last key can be used with @groupFields to create dynamic custom fields
	 * Can be used to make the dynamic input name alwas uniqe 
	 * @return int
	 */
	protected function lastKey(): int 
	{
		$mk = 0;
		if(!is_null($this->value) && is_array($this->value)) {
			$mk = $this->value;
			krsort($mk);
			$mk = key($mk);
		}
		return $mk;
	}

	/**
	 * Used in to help make sence of validate data
	 * @param  mixed $jsonStr
	 * @return mixed
	 */
	private function json($jsonStr) {
		if(is_string($jsonStr)) {
			if($data = json_decode($jsonStr, true)) {
				return $data;
			} else {
				throw new \Exception("JSON ERROR CODE: ".json_last_error(), 1);
			}
		}
		return $jsonStr;
	}

}
