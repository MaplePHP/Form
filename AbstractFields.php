<?php

/**
 * @Package:    MaplePHP - Form builder
 * @Author:     Daniel Ronkainen
 * @Licence:    The MIT License (MIT), Copyright © Daniel Ronkainen
                Don't delete this comment, its part of the license.
 */

namespace MaplePHP\Form;

use MaplePHP\Form\Interfaces\FormFieldsInterface;
use MaplePHP\Form\Interfaces\FieldInterface;

abstract class AbstractFields
{
    protected const LOAD_FIELD_METHODS = [
        "label" => null,
        "description" => null,
        "config" => [],
        "items" => [],
        "value" => null,
        "fields" => [],
        "attr" => []
    ];

    protected $name = "form";
    protected $fields;
    protected $type;
    protected $args = array();
    protected $values = array();
    protected $inpArr = array();

    /**
    * Form creator
    * @param FormFieldsInterface $fields Form template class
    */
    public function __construct(FormFieldsInterface $fields)
    {
        $this->fields = $fields;
    }

    /**
     * Quick create and return field (Chainable resource)
     * @param  string $method
     * @param  array $args
     * @return FormFieldsInterface
     */
    public function __call($method, $args): FormFieldsInterface
    {
        // Reset fields instance
        if (!is_null($this->type)) {
            $this->fields = $this->fields->withField();
        }
        
        if ($this instanceof FieldInterface) {
            $this->fields->setFieldInst($this);
        }
        $this->type = $method;
        $this->args = $args;
        return $this->fields;
    }

    /**
     * Get a list of supported field configuration
     * @return array
     */
    public function getConfigs(): array
    {
        return static::LOAD_FIELD_METHODS;
    }

    /**
     * You can create a new form
     * @param string $name
     * @return self
     */
    public function withForm(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    /**
     * You can split the form into multiple partials with the help with withForm or new instance
     * Every form partial will then be validate
     * @param FieldInterface $inst
     * @return self
     */
    public function setPartial(FieldInterface $inst): self
    {
        $this->add($inst->getFields(), $inst->getFormName());
        return $this;
    }

    /**
     * Set values
     * @param array|object $values Will be converted to array
     */
    public function setValues(array|object $values): void
    {
        $values = (array)$values;
        foreach ($values as $k => $val) {
            if (!is_array($val)) {
                $val = (string)$val;
            }
            $this->values[$k] = $val;
        }
    }

     /**
     * Delete search and find a array item
     * @param  array $key  Possible to traverse to form field with the comma select property
     * @return void
     */
    final protected function findDelete(array &$array, array $key): void
    {
        $firstKey = array_shift($key);
        if (isset($array[$firstKey])) {
            if (count($key) > 0) {
                $this->findDelete($array[$firstKey], $key);
            } else {
                unset($array[$firstKey]);
            }
        }
    }


    /**
     * Make grouped template fields into grouped fields
     * @return array
     */
    final protected function resolveGrpName(): array
    {
        $get = array();
        foreach ($this->inpArr as $a1) {
            foreach ($a1 as $k => $a2) {
                if (isset($a2['type'])) {
                    if (isset($a2['fields'])) {
                        switch ($a2['type']) {
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
     * @param  string $keyA
     * @return void
     */
    private function resolveNameNest(array $array, array &$get, string $keyA): void
    {
        foreach ($array as $keyB => $row) {
            if (isset($row['type'])) {
                $newKey = ($keyA) ? $keyA . ",{$keyB}" : $keyB;
                if (isset($row['fields'])) {
                    $this->resolveNameNest($row['fields'], $get, $newKey);
                } else {
                    $get[$newKey] = $row;
                }
            }
        }
    }
}
