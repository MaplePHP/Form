<?php 

namespace PHPFuse\Form;

use PHPFuse\Form\Interfaces\FieldInterface;

use PHPFuse\Validate\Inp;
use PHPFuse\DTO\Format\Local;

class Validate {

	const WHITELIST_INC_ARR_FIELD = ["list"];
	const TO_REQURED_FLAG = ["length", "hasLength", "hasValue", "required"];

	private $validArr;
	private $fields;
	private $post;
	private $request = array();
	private $files = array();
	private $local;
	
	/**
	 * Built to auto validate FieldInterface fields
	 * @param FieldInterface $fields
	 * @param array          $post
	 */
	function __construct(FieldInterface $fields, array $post) 
	{
		$this->fields = $fields;
		$this->post = $post;	
	}

	/**
	 * Get filtered RAW filtered POST data
	 * @return array
	 */
	public function getRequest(): array
	{
		return $this->request;
	}

	/**
	 * Get filtered RAW filtered FILES data
	 * @return array
	 */
	public function getFiles(): array 
	{
		return $this->files;
	}

	/**
	 * Set local to return message in right language
	 * @param Local $local
	 */
	public function setLocal(Local $local): void 
	{
		$this->local = $local;
	}

	/**
	 * Add an error item manually 
	 * @param string $name    The input/field name
	 * @param string $type    Type of error (e.g. required, min, max...)
	 * @param string $message Nice error message
	 */
	public function add(string $name, string $type, ?string $message = NULL) 
	{
		$this->validArr[$name] = ["type" => $type, "message" => $message];
		return $this;
	}

	/**
	 * Validate all fields in form
	 * @return array|NULL (array=error and NULL=Success)
	 */
	public function execute(): ?array 
	{
		$this->fields->setValues($this->post);
		$this->fields->build();

		$postArr = array();
		$arr = $this->fields->getValidateData();
		foreach($arr as $name => $arr) {
			$field = $this->fields->{$arr['type']}();
			$input = $field->name($name)->fieldType($arr['type'])->value(false);
			$nameKey = $input->getName();
			$exp = explode(",", $name);

			// Build request array from exploded protocol and return only passed values
			$value = $this->buildPostArr($exp, $this->post, $postArr, $input);

			if(!is_array($value)) {
				if(isset($arr['validate'])) {
					if($error = $this->isInvalid($arr['validate'], htmlspecialchars((string)$value))) {
						$this->validArr[$nameKey] = $error;
					}
				}
			}		
		}

		$this->request = $postArr;
		return $this->validArr;
	}

	/**
	 * Validate field
	 * @param  array  $arr 	 [ Method => [Arg1, Arg2] ]
	 * @param  string $value Field value to be validated
	 * @return boolean|array
	 */
	protected function isInvalid($arr, $value): bool|array 
	{
		$valid = Inp::value($value);

		if(is_array($arr)) foreach($arr as $method => $args) {
			$valFilledIn = false;
			if(strpos($method, "!") !== false) {
				$valFilledIn = true;
				$method = substr($method, 1);
			}

			if(method_exists($valid, $method)) {
				if(!is_array($args)) $args = array();
				$object = call_user_func_array([$valid, $method], $args);
				if(($valFilledIn && strlen($value) > 0 && !$object) || (!$valFilledIn && !$object)) {

					if(in_array($method, self::TO_REQURED_FLAG)) {
						$length = strlen($value);
						if($length === 0) $method = "required";
					}

					return ["type" => $method, "args" => $args, "message" => $this->message($method, $args)];
				}
			} else {
				return ["type" => $method, "args" => $args, "message" => "Validation method ({$method}) does not exist"];
			}
		}

		return false;
	}

	/**
	 * Will be used to extract a nice message from key
	 * @param  string $key  error key (e.g. required, length, max, min...)
	 * @param  array  $args sprint push possible values
	 * @return string
	 */
	protected function message(string $key, array $args = array()) 
	{
		if(!is_null($this->local)) {
			return $this->local->get($key, $key, $args);
		}
		return $key;
	}

	/**
	 * Will build a validateable post array from extisting fields
	 * @param  array         $exp 
	 * @param  array         &$arr
	 * @param  mixed         &$new
	 * @return mixed (field value)
	 */
	protected function buildPostArr(array $exp, array &$arr, mixed &$new, object $field)
	{
		$k = array_shift($exp);
		if(isset($arr[$k])) {
			if(count($exp) > 0) {
				//if(isset($new[$k])) $new[$k] = [];
				return $this->buildPostArr($exp, $arr[$k], $new[$k], $field);
			} else {

				// Pass _FILE value to array
				if(isset($arr[$k]['tmp_name'])) {
					$this->files[$k] = $arr[$k];
					return $arr[$k];

				} elseif(!is_array($arr[$k])) {
					$new[$k] = $arr[$k];
					return $arr[$k];
				} else {
					// Pass on incremental values from field type
					if(isset($arr[$k][0]) && is_string($arr[$k][0]) && in_array($field->getFieldType(), static::WHITELIST_INC_ARR_FIELD)) {
						$new[$k] = $arr[$k];
						return $arr[$k];
					}
				}
				return NULL;
			}
		}
	}

}

