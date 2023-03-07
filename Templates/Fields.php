<?php 
/**
 * @Package: 	PHP Fuse - Field and input templates
 * @Author: 	Daniel Ronkainen
 * @Licence: 	The MIT License (MIT), Copyright © Daniel Ronkainen
 				Don't delete this comment, its part of the license.
 * @Version: 	1.0.0
 */
namespace Form\Templates;

class Fields extends \Form\Builder\Arguments {


	function container(callable $callback) {
		$length = count($this->_items);

		$class = "";
		if(!is_null($this->_class)) $class .= " {$this->_class}";

		$out = "";
		$out .= "<div {$this->_conAttr}class=\"holder{$class}\" data-count=\"{$length}\">";
		if(!is_null($this->_label)) {
			$boolLength = (isset($this->_validate['length'][0]) && $this->_validate['length'][0] > 0);
			$boolHasLength = (isset($this->_validate['hasLength'][1]) && $this->_validate['hasLength'][1] > 0);
			$req = ($boolLength || $boolHasLength) ? "*" : NULL;
			$out .= "<label>{$this->_label}<span class=\"req\">{$req}</span></label>";
		}
		if(!is_null($this->_description)) $out .= "<div class=\"legend\">{$this->_description}</div>";
		$out .= $callback();
		$out .= "</div>";
		return $out;
	}

	/**
	 * Input text
	 * @return string/html
	 */
	function text() {
		if(isset($this->_attrArr['data-clear'])) $this->attr(["required" => "required"]);

		return $this->container(function() {
			$typeAdd = (isset($this->_attrArr['type']) ? NULL : "type=\"{$this->_inpType}\" ");
			return "<input {$typeAdd}{$this->_attr}name=\"{$this->_name}\" data-name=\"{$this->_dataName}\" value=\"{$this->_value}\">";
		});
	}

	/**
	 * Input hidden
	 * @return string/html
	 */
	function hidden() {		
		return "<input type=\"hidden\" {$this->_attr}name=\"{$this->_name}\" data-name=\"{$this->_dataName}\" value=\"{$this->_value}\">";
	}


	/**
	 * Input textarea
	 * @return string/html
	 */
	function textarea() {
		return $this->container(function() {
			return "<textarea {$this->_attr}name=\"{$this->_name}\">{$this->_value}</textarea>";
		});

	}
	
	function carrot() {
		return "[CARROT:{$this->_name}]";
	}
}
