<?php

/**
 * @Package:    MaplePHP - Form builder
 * @Author:     Daniel Ronkainen
 * @Licence:    Apache-2.0 license, Copyright © Daniel Ronkainen
                Don't delete this comment, its part of the license.
 */

namespace MaplePHP\Form;

use MaplePHP\Form\Interfaces\FieldInterface;

class Fields extends AbstractFields implements FieldInterface
{
    private $buildArr;
    private $validateData = array();

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
     * @param  string $name The form key
     * @return boolean
     */
    public function hasFormData(?string $name = null): bool
    {
        if (is_null($name)) {
            $name = $this->name;
        }
        return (isset($this->inpArr[$name]));
    }

    /**
     * Get fields (will throw Exception if form is missing)
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
     * Get settted values
     * @return mixed
     */
    public function getValues(): mixed
    {
        return $this->values;
    }

    /**
     * Create form
     * @param array $fields
     * @param string $name   Form name
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
     * @return void
     */
    public function deleteForm(): void
    {
        unset($this->inpArr[$this->name]);
    }

    /**
     * Delete a field in form
     * @param  string|array $key  Field name (possible to traverse to form field with the comma select property)
     * @return void
     */
    public function deleteField(string|array $key): void
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
     * @param string $key
     * @param array  $arr
     */
    public function setValidateData(string $key, array $arr): void
    {
        $this->validateData[$key] = $arr;
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
        return ($this->validateData[$key] ?? []);
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
     * @param  string $name The form key
     * @return bool
     */
    public function hasForm(?string $name = null): bool
    {
        if (is_null($name)) {
            $name = $this->name;
        }
        return (isset($this->buildArr[$name]));
    }

    /**
     * Get built form (Will return exception if does not exist!)
     * @param  string $name form key
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
     * @return string
     */
    public function html(array $inpArr): string
    {
        $out = "";
        foreach ($inpArr as $name => $arr) {
            if (isset($arr['type'])) {
                $field = $this->{$arr['type']}();
                $args = $field->default(($arr['default'] ?? null))
                ->fieldType($arr['type'])
                ->validate(((isset($arr['validate'])) ? $arr['validate'] : []))->name($name);
                foreach (static::LOAD_FIELD_METHODS as $method => $defualt) {
                    $value = (isset($arr[$method])) ? $arr[$method] : $defualt;
                    $args = $field->{$method}($value);
                }
                $out .= $args->get();
            }
        }
        return $out;
    }
}
