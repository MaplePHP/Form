<?php
/**
 * @Package:    PHPFuse - Form builder engine
 * @Author:     Daniel Ronkainen
 * @Licence:    The MIT License (MIT), Copyright © Daniel Ronkainen
                Don't delete this comment, its part of the license.
 */
namespace PHPFuse\Form;

class Arguments extends AbstractArguments
{
    protected $grpIdentifier;
    protected $count = 0;
    protected $nameExp;
    protected $dataName;

    /**
     * Add label to field
     * @param  string $label
     * @return self
     */
    public function label(?string $label): self
    {
        if ($label) {
            $this->label = $label;
        }
        return $this;
    }

    /**
     * Add description to field
     * @param  string $label
     * @return self
     */
    public function description(?string $description): self
    {
        if ($description) {
            $this->description = $description;
        }
        return $this;
    }

    /**
     * Set text fields type
     * @param  string $fieldType
     * @return self
     */
    public function fieldType(?string $fieldType): self
    {
        $this->fieldType = $fieldType;
        return $this;
    }

    /**
     * Set field attribute
     * @param  array|bool   $arr
     * @return self
     */
    public function attr(array $arr): self
    {
        if (is_array($arr)) {
            $this->attrArr = array_merge($this->attrArr, $arr);
            $this->setAttr();
        }
        return $this;
    }

    /**
     * Pass extra configs to fields
     * @param  array $arr
     * @return self
     */
    public function config(array $arr): self
    {
        if (is_array($arr)) {
            $this->config = array_merge($this->config, $arr);
        }
        return $this;
    }

    /**
     * Add items to e.g. select list, checkboxes or radio
     * @param  array  $arr
     * @return self
     */
    public function items(array $arr): self
    {
        if (is_array($arr)) {
            $this->items = $arr;
        }
        return $this;
    }

    /**
     * Add fields to group
     * @param  array $arr
     * @return self
     */
    public function fields(array $arr): self
    {
        if (is_array($arr)) {
            $this->fields = $arr;
        }
        return $this;
    }

    /**
     * Set Validation to field
     * @param  array $arr
     * @return self
     */
    public function validate(array $arr): self
    {
        if (is_array($arr)) {
            $this->validate = $arr;
        }
        return $this;
    }

    /**
     * Set default value if empty e.g "" or 0
     * @param  string|null $default
     * @return self
     */
    public function default(?string $default): self
    {
        if (!is_null($default)) {
            $this->default = $default;
        }
        return $this;
    }

    /**
     * Set field name
     * @param  string $name
     * @return self
     */
    public function name($name): self
    {
        $this->grpIdentifier = $this->identifier = trim($name);
        $this->nameExp = $exp = explode(",", $this->identifier);
        $this->name = array_shift($exp);
        $this->dataName = end($this->nameExp);
        $this->grpIdentifier = preg_replace('/(,[0-9])+/', '', $this->grpIdentifier);

        $this->inst->setValidateData($this->identifier, [
            "id" => ($this->rows['id'] ?? 0),
            "type" => (!is_null($this->fieldType) ? $this->fieldType : "text"),
            "validate" => $this->validate,
            "default" => $this->default,
            "config" => $this->config
        ]);

        foreach ($exp as $item) {
            $this->name .= "[".htmlentities(trim($item))."]";
        }
        return $this;
    }

    /**
     * Set field value
     * @param  boolean $val
     * @return self
     */
    public function value(?string $val = null): self
    {
        if (!is_null($val)) {
            $this->value = $val;
        } elseif (is_array($this->nameExp) && count($this->nameExp) > 0) {
            $values = $this->inst->getValues();
            if (!is_null($values)) {
                $values = (array)$values;
                $exp = $this->nameExp;

                $first = array_shift($exp);
                if (isset($values[$first])) {
                    $this->value = $values[$first];
                    if (count($exp) > 0) {
                        $this->value = $this->json($this->value);
                        foreach ($exp as $item) {
                            $item = htmlentities(trim($item));
                            $this->value = isset($this->value[$item]) ? $this->value[$item] : $val;
                        }
                    }
                }
            }
        }

        return $this;
    }
}
