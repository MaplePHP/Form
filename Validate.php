<?php

namespace PHPFuse\Form;

use PHPFuse\Form\Interfaces\FieldInterface;
use PHPFuse\Validate\Inp;
use PHPFuse\DTO\Format\Local;

class Validate
{
    public const WHITELIST_INC_ARR_FIELD = ["list"];
    public const TO_REQURED_FLAG = ["length", "hasLength", "hasValue", "required"];

    private $validArr;
    private $fields;
    private $post;
    private $request = array();
    private $files = array();
    private $local;
    private $value;
    private $length = 0;
    private $validate;

    /**
     * Built to auto validate FieldInterface fields
     * @param FieldInterface $fields
     * @param array          $post
     */
    public function __construct(FieldInterface $fields, array $post)
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
    public function add(string $name, string $type, ?string $message = null)
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
        foreach ($arr as $name => $arr) {
            $field = $this->fields->{$arr['type']}();
            $input = $field->name($name)->fieldType($arr['type'])->value(false)->default($arr['default'] ?? null);
            $nameKey = $input->getName();
            $exp = explode(",", $name);

            // Build request array from exploded protocol and return only passed values
            $value = $this->buildPostArr($exp, $this->post, $postArr, $input);

            if (!is_array($value)) {
                if (isset($arr['validate'])) {
                    $this->value = htmlspecialchars((string)$value);
                    $this->length = strlen($this->value);
                    $this->validate = Inp::value($this->value);

                    if ($error = $this->isInvalid($arr['validate'])) {
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
     * @param  array  $arr   [ Method => [Arg1, Arg2] ]
     * @param  string $value Field value to be validated
     * @return boolean|array
     */
    protected function isInvalid($arr): bool|array
    {
        if (is_array($arr)) {
            foreach ($arr as $method => $args) {
                $valFilledIn = false;
                if (strpos($method, "!") !== false) {
                    $valFilledIn = true;
                    $method = substr($method, 1);
                }

                if (method_exists($this->validate, $method)) {
                    if ($this->validateWithMethod($method, $args, $valFilledIn)) {
                        if (in_array($method, self::TO_REQURED_FLAG)) {
                            if ($this->length === 0) {
                                $method = "required";
                            }
                        }
                        return $this->buildMessage($method, $args, $this->message($method, $args));
                    }
                } else {
                    $message = "Validation method ({$method}) does not exist";
                    return $this->buildMessage($method, $args, $message);
                }
            }
        }
        return false;
    }

    /**
     * Validate with the validation library
     * @param  string $method
     * @param  array  $args
     * @param  bool   $valFilledIn
     * @return bool
     */
    private function validateWithMethod(string $method, ?array $args, bool $valFilledIn): bool
    {
        if (!is_array($args)) {
            $args = array();
        }
        $object = call_user_func_array([$this->validate, $method], $args);
        return (bool)(($valFilledIn && $this->length > 0 && !$object) || (!$valFilledIn && !$object));
    }

    /**
     * Build response structure
     * @param  string $method
     * @param  array  $args
     * @param  string $message
     * @return array
     */
    private function buildMessage(string $method, ?array $args, string $message): array
    {
        return ["type" => $method, "args" => $args, "message" => $message];
    }

    /**
     * Will be used to extract a nice message from key
     * @param  string $key  error key (e.g. required, length, max, min...)
     * @param  array  $args sprint push possible values
     * @return string
     */
    protected function message(string $key, array $args = array())
    {
        if (!is_null($this->local)) {
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
        $firstKey = array_shift($exp);
        if (isset($arr[$firstKey])) {
            if (count($exp) > 0) {
                //if(isset($new[$firstKey])) $new[$firstKey] = [];
                return $this->buildPostArr($exp, $arr[$firstKey], $new[$firstKey], $field);
            } else {
                // Pass _FILE value to array
                if (isset($arr[$firstKey]['tmp_name'])) {
                    $this->files[$firstKey] = $arr[$firstKey];
                    return $arr[$firstKey];
                } elseif (!is_array($arr[$firstKey])) {
                    $new[$firstKey] = $arr[$firstKey];
                    return $arr[$firstKey];
                } else {
                    // Pass on incremental values from field type
                    if (
                        isset($arr[$firstKey][0]) && is_string($arr[$firstKey][0]) &&
                        in_array($field->getFieldType(), static::WHITELIST_INC_ARR_FIELD)
                    ) {
                        $new[$firstKey] = $arr[$firstKey];
                        return $arr[$firstKey];
                    } else {
                        $new[$firstKey] = $field->getDefault();
                    }
                }
                return null;
            }
        } else {
            $new[$firstKey] = $field->getDefault();
        }
    }
}
