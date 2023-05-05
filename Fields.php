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
	private $buildArr;
	private $validateArr = array();

	static private $_inst;

	/**
	 * Form creator
	 * @param FormFieldsInterface $fields Form template class
	 */
	function __construct(FormFieldsInterface $fields) {
		self::$_inst = $this;
		$this->fields = $fields;
	}

	/**
	 * Quick create and return field (Chainable resource)
	 * @param  string $a [description]
	 * @param  array $b [description]
	 * @return self
	 */
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

	/**
	 * Get forms
	 * @return array
	 */
	function getForms(): array 
	{		
		return $this->inpArr;
	}

	/**
	 * Get fields
	 * @param  string $name Form name
	 * @return array
	 */
	function getFields(string $name): array 
	{		
		return ($this->inpArr[$name] ?? NULL);
	}

	/**
	 * Get fields with resolved group name if needed
	 * @return array
	 */
	function getData() 
	{
		return $this->resolveGrpName();
	}

	/**
	 * Set values
	 * @param void
	 */
	function setValues($values): void 
	{
		$values = (array)$values;
		foreach($values as $k => $val) {
			if(is_array($val) || strlen($val)) $this->values[$k] = $val;
		}
	}

	/**
	 * Get settted values
	 * @return array
	 */
	function getValues(): array 
	{
		return $this->values;
	}

	/**
	 * Create form
	 * @param string $name   Form name
	 * @param array $fields
	 */
	function add(string $name, $fields): self 
	{
		$this->inpArr[$name] = $fields;
		return $this;
	}

	/**
	 * Prepend field
	 * @param  string $name
	 * @param  array  $fields
	 * @return self
	 */
	function prepend(string $name, array $fields): self
	{
		$this->inpArr[$name] = array_merge($fields, $this->inpArr[$name]);
		return $this;
	}

	/**
	 * Append field
	 * @param  string $name
	 * @param  array  $fields
	 * @return self
	 */
	function append(string $name, array $fields): self
	{
		$this->inpArr[$name] = array_merge($this->inpArr[$name], $fields);
		return $this;
	}

	/**
	 * Delete whole form
	 * @param  string $name Form name
	 * @return void
	 */
	function deleteForm(string $name): void 
	{
		unset($this->inpArr[$name]);
	}

	/**
	 * Delete a field in form
	 * @param  string $name Form name
	 * @param  string $key  Field name
	 * @return void
	 */
	function deleteField(string $name, string $key): void 
	{
		if(is_array($key)) {
			$this->findDelete($this->inpArr[$name], $key);
		} else {
			if(isset($this->inpArr[$name][$key])) unset($this->inpArr[$name][$key]);
		}
	}

	/**
	 * Set validation array
	 * @param string $id
	 * @param array  $arr
	 */
	function setValidateArr(string $id, array $arr): void 
	{
		$this->validateArr[$id] = $arr;
	}

	/**
	 * Get forms validation options
	 * @param  string|null $key formKey
	 * @return array
	 */
	function validateArr(?string $key = NULL): array 
	{
		return (!is_null($key)) ? (isset($this->validateArr[$key]) ? $this->validateArr[$key] : []) : $this->validateArr;
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
	 * Quick generate and return single fields
	 * @return string
	 */
	function get() {
		if(!is_null($this->type)) {
			$get = call_user_func_array([$this->fields, $this->type], $this->args);
			return $get;
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
				$label = (isset($arr['label'])) ? $arr['label'] : false;
				$description = (isset($arr['description'])) ? $arr['description'] : false;
				$config = (isset($arr['config'])) ? $arr['config'] : array();
				$items = (isset($arr['items'])) ? $arr['items'] : array();

				
				$fields = (isset($arr['fields'])) ? $arr['fields'] : array();
				$conAttr = (isset($arr['conAttr'])) ? $arr['conAttr'] : false;
				$validate = (isset($arr['validate'])) ? $arr['validate'] : array();

				$args = $field->rows($arr)->fieldType($arr['type'])->label($label)->description($description)->validate($validate)->config($config)->name($name)->items($items)->value($value)->fields($fields)
				->attr($attr)->conAttr($conAttr);

				if(!is_null($callback)) $callback($args);
				$out .= $args->get();
			}
		}

		return $out;
	}

	/**
	 * Make grouped template fields into grouped fields  
	 * @param  array|null $array [description]
	 * @return array
	 */
	private function resolveGrpName(): array
	{
		//if(!is_array($array)) $array = $this->inpArr;

		$get = array();
		foreach($this->inpArr as $a1) {
			foreach($a1 as $k => $a2) {
				if(isset($a2['type'])) {					
					if(isset($a2['fields'])) {
						switch($a2['type']) {
							case "group":
								$this->resolveNameNest($a2['fields'], $get, $k);
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

	/**
	 * Set input fields name multidimensional
	 * @param  array  $array
	 * @param  array  &$get
	 * @param  string $key
	 * @return void
	 */
	private function resolveNameNest(array $array, array &$get, string $key): void 
	{
		foreach($array as $k => $row) {
			if(isset($row['type'])) {
				$k1 = ($key) ? $key.",{$k}" : $k;
				if(isset($row['fields'])) {
					$this->resolveNameNest($row['fields'], $get, $k1);
				} else {
					$get[$k1] = $row;
				}
			}
		}
	}

	/**
	 * Delete fields
	 */
	private function findDelete(&$array, $key): void {
		$k = array_shift($key);
		if(isset($array[$k])) {
			if(count($key) > 0) {
				$this->findDelete($array[$k], $key);
			} else {
				unset($array[$k]);
			}
		}
	}


	// DEPRECATED --> 
	

	/*
	function data(?string $key = NULL): array|string 
	{
		$arr = $this->resolveGrpName();
		return (!is_null($key)) ? (isset($arr[$key]) ? $arr[$key] : false) : $arr;
	}

	function inpArr() {
		return $this->inpArr;
	}
	function getType() {
		return $this->type;
	}
	 */
	

}
