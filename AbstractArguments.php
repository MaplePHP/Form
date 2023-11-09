<?php

/**
 * @Package:    PHPFuse - Form builder engine
 * @Author:     Daniel Ronkainen
 * @Licence:    The MIT License (MIT), Copyright © Daniel Ronkainen
                Don't delete this comment, its part of the license.
 */

namespace PHPFuse\Form;

use PHPFuse\Form\Interfaces\FieldInterface;

abstract class AbstractArguments
{

    protected $inst;
    protected $attr;

    protected $fieldType;
    protected $attrArr = array();
    protected $name;
    protected $value;
    protected $default;
    protected $label;
    protected $description;
    protected $validate = array();
    protected $items = array();
    protected $config = array();
    protected $fields = array();
    protected $identifier;


    /**
     * Main field instance
     * @param  FieldInterface $inst
     * @return self
     */
    public function setFieldInst(FieldInterface $inst): self
    {
        $this->inst = $inst;
        return $this;
    }

    /**
     * Get request (Get filtered requests)
     * @return mixed
     */
    public function getRequest(): mixed
    {
        return $this->inst->request;
    }

    /**
     * Get all values
     * @return mixed
     */
    public function getValues(): mixed
    {
        return $this->inst->getValues();
    }

    /**
     * Same as above
     * @return mixed
     */
    public function values(): mixed
    {
        return $this->getValues();
    }

    /**
     * Same as above
     * @return mixed
     */
    public function value(): mixed
    {
        return $this->value;
    }

    /**
     * Get field type
     * @return string
     */
    public function getFieldType(): ?string
    {
        return $this->fieldType;
    }

    /**
     * Get name
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get value
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Get default value
     * @return string
     */
    public function getDefault(): ?string
    {
        return $this->default;
    }

    /**
     * Get form field
     * @return FieldInterface
     */
    public function get()
    {
        $this->value();
        return $this->inst->get();
    }


    protected function setAttr(): string
    {
        $this->attr = "";
        foreach ($this->attrArr as $key => $value) {
            $this->attr .= "{$key}=\"{$value}\" ";
        }
        return $this->attr;
    }

    /**
     * Group fields / custom fields with dynamic and nested fields names
     * @param  callable $callback       Container room for customization
     * @param  bool     $manipulateName Manipulate the input field name
     * @return string
     */
    protected function groupFields(callable $callback, bool $manipulateName = true)
    {
        $out = "";
        $fields = array();
        if (!is_array($this->value)) {
            $this->value = array(0);
        } // This will add new value
        foreach ($this->value as $k => $a) {
            $outB = "";
            foreach ($this->fields as $name => $arr) {
                $fieldKey = ($manipulateName) ? "{$this->identifier},{$k},{$name}" : $name;
                $fields[$fieldKey] = $arr;
                $outB .= $this->inst->html($fields);
                unset($fields);
            }
            $out .= $callback($outB, $a);
        }
        return $out;
    }

    /**
     * This will inherit the parent name and build upon it.
     * @return string
     */
    protected function inheritField()
    {
        $out = "";
        $fields = array();
        foreach ($this->fields as $name => $arr) {
            $fieldKey = "{$this->identifier},{$name}";
            $fields[$fieldKey] = $arr;
            $out .= $this->inst->html($fields);
            unset($fields);
        }
        return $out;
    }

    /**
     * Check if filed is checked/active
     * @param  string  $val
     * @return boolean
     */
    protected function isChecked($val): bool
    {
        if (is_array($this->value)) {
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
        $findKey = 0;
        if (!is_null($this->value) && is_array($this->value)) {
            $findKey = $this->value;
            krsort($findKey);
            $findKey = key($findKey);
        }
        return $findKey;
    }

    /**
     * Used in to help make sence of validate data
     * @param  mixed $jsonStr
     * @return mixed
     */
    final protected function json($jsonStr)
    {
        if (is_string($jsonStr)) {
            if ($data = json_decode($jsonStr, true)) {
                return $data;
            } else {
                throw new \Exception("JSON ERROR CODE: ".json_last_error(), 1);
            }
        }
        return $jsonStr;
    }
}