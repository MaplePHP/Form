<?php

/**
 * @Package:    PHPFuse - Form builder
 * @Author:     Daniel Ronkainen
 * @Licence:    The MIT License (MIT), Copyright © Daniel Ronkainen
                Don't delete this comment, its part of the license.
 */

namespace PHPFuse\Form;

use PHPFuse\Form\Interfaces\FieldInterface;
use PHPFuse\Form\Interfaces\FormFieldsInterface;

class Fields implements FieldInterface
{
    private $form;
    private $name = "form";
    private $fields;
    private $type;
    private $args = array();
    private $inpArr = array();
    private $values = array();
    private $buildArr;
    private $validateData = array();

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
     * @param  string $a
     * @param  array $b
     * @return self
     */
    public function __call($a, $b)
    {
        // Reset build instance
        if (!is_null($this->type)) {
            $class = get_class($this->fields);
            $this->fields = new $class();
        }

        $this->fields->inst($this);
        $this->type = $a;
        $this->args = $b;
        return $this->fields;
    }

    /**
     * You can split the form into multiple partials with the help with withForm or new instance
     * Every form partial will then be validate
     * @param self
     */
    public function setPartial(FieldInterface $inst): self
    {
        $this->add($inst->getFields(), $inst->getFormName());
        return $this;
    }

    /**
     * You can create a new form
     * @param static
     */
    public function withForm($name): self
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    /**
     * Get form name
     * @return string
     */
    public function getFormName(): string
    {
        return $this->name;
    }

    /**
     * Get forms
     * @return array
     */
    public function getFormData(): array
    {
        return $this->inpArr;
    }

    /**
     * Check if form exists
     * @param  string  $name Form name
     * @return boolean
     */
    public function hasFormData(): bool
    {
        return (bool)(isset($this->inpArr[$this->name]));
    }

    /**
     * Get fields (will throw Exception if form is missing)
     * @param  string $name Form name
     * @return array
     */
    public function getFields(): array
    {
        return ($this->inpArr[$this->name] ?? []);
    }

    /**
     * Get fields with resolved group name if needed
     * @return array
     */
    public function getData(): array
    {
        return $this->resolveGrpName();
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
     * Get settted values
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Create form
     * @param string $name   Form name
     * @param array $fields
     */
    public function add($fields, ?string $name = null): self
    {
        if (is_null($name)) {
            $name = $this->name;
        }
        $this->inpArr[$name] = $fields;
        return $this;
    }

    /**
     * Prepend field
     * @param  string $name
     * @param  array  $fields
     * @return self
     */
    public function prepend(array $fields): self
    {
        $this->inpArr[$this->name] = array_merge($fields, $this->inpArr[$this->name]);
        return $this;
    }

    /**
     * Append field
     * @param  string $name
     * @param  array  $fields
     * @return self
     */
    public function append(array $fields): self
    {
        $this->inpArr[$this->name] = array_merge($this->inpArr[$this->name], $fields);
        return $this;
    }

    /**
     * Delete whole form
     * @param  string $name Form name
     * @return void
     */
    public function deleteForm(): void
    {
        unset($this->inpArr[$this->name]);
    }

    /**
     * Delete a field in form
     * @param  string $name Form name
     * @param  string $key  Field name
     * @return void
     */
    public function deleteField(string $key): void
    {
        if (is_array($key)) {
            $this->findDelete($this->inpArr[$this->name], $key);
        } else {
            if (isset($this->inpArr[$this->name][$key])) {
                unset($this->inpArr[$this->name][$key]);
            }
        }
    }

    /**
     * Set validation array
     * @param string $id
     * @param array  $arr
     */
    public function setValidateData(string $id, array $arr): void
    {
        $this->validateData[$id] = $arr;
    }

    /**
     * Get forms validation options
     * @return array
     */
    public function getValidateData(): array
    {
        return $this->validateData;
    }

    /**
     * Get forms validation options
     * @param  string $key field key
     * @return array
     */
    public function getValidateDataRow(string $key): array
    {
        return ($this->validateData[$key] ?? null);
    }

    /**
     * Build all form data before valiate or read
     * This will reset validation data.
     * @return void
     */
    public function build(): void
    {
        $this->validateData = array();
        foreach ($this->inpArr as $key => $array) {
            $this->buildArr[$key] = $this->html($array);
        }
    }

    /**
     * Build all form data before valiate or read (is immutable!)
     * This will reset validation data.
     * @return static
     */
    public function withBuild(): static
    {
        $inst = clone $this;
        $inst->build();
        return $inst;
    }

    /**
     * Quick generate and return single fields
     * @return string
     */
    public function get(): string
    {
        if (!is_null($this->type) && method_exists($this->fields, $this->type)) {
            $get = call_user_func_array([$this->fields, $this->type], $this->args);
            return $get;
        }
        return "";
    }

    /**
     * Check if form exists
     * @param  string $key The form key
     * @return bool
     */
    public function hasForm(?string $name = null): bool
    {
        if (is_null($name)) {
            $name = $this->name;
        }
        return (bool)(isset($this->buildArr[$name]));
    }

    /**
     * Get built form (Will return exception if does not exist!)
     * @param  string $key form key
     * @return string
     */
    public function getForm(?string $name = null): string
    {

        if (is_null($name)) {
            $name = $this->name;
        }
        if (!$this->hasFormData($name)) {
            throw new \Exception("The form does not exists. You need to create a form using the @add method!", 1);
        }
        if (!$this->hasForm($name)) {
            throw new \Exception("The form need to be built with the @withBuild method before you can read it!", 1);
        }
        return $this->buildArr[$name];
    }

    /**
     * Build HTML
     * @param  array $inpArr
     * @param  callable $callback
     * @return string/html
     */
    public function html(array $inpArr): string
    {
        $out = "";
        foreach ($inpArr as $name => $arr) {
            if (isset($arr['type'])) {
                $field = $this->{$arr['type']}();
                $value = (isset($arr['value'])) ? $arr['value'] : false;
                $default = ($arr['default'] ?? null);
                $attr = (isset($arr['attr'])) ? $arr['attr'] : false;
                $label = (isset($arr['label'])) ? $arr['label'] : false;
                $description = (isset($arr['description'])) ? $arr['description'] : false;
                $config = (isset($arr['config'])) ? $arr['config'] : array();
                $items = (isset($arr['items'])) ? $arr['items'] : array();


                $fields = (isset($arr['fields'])) ? $arr['fields'] : array();
                $conAttr = (isset($arr['conAttr'])) ? $arr['conAttr'] : false;
                $validate = (isset($arr['validate'])) ? $arr['validate'] : array();

                $args = $field->rows($arr)->default($default)->fieldType($arr['type'])->label($label)
                ->description($description)->validate($validate)->config($config)->name($name)->items($items)
                ->value($value)->fields($fields)->attr($attr)->conAttr($conAttr);

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
     * @param  string $key
     * @return void
     */
    private function resolveNameNest(array $array, array &$get, string $key): void
    {
        foreach ($array as $k => $row) {
            if (isset($row['type'])) {
                $k1 = ($key) ? $key.",{$k}" : $k;
                if (isset($row['fields'])) {
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
    private function findDelete(&$array, $key): void
    {
        $k = array_shift($key);
        if (isset($array[$k])) {
            if (count($key) > 0) {
                $this->findDelete($array[$k], $key);
            } else {
                unset($array[$k]);
            }
        }
    }
}
