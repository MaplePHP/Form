<?php

/**
 * @Package:    MaplePHP - Form builder template
 * @Author:     Daniel Ronkainen
 * @Licence:    Apache-2.0 license, Copyright © Daniel Ronkainen
                Don't delete this comment, its part of the license.
 */

namespace MaplePHP\Form;

use MaplePHP\Form\Interfaces\FormFieldsInterface;
use MaplePHP\Form\Arguments;

abstract class AbstractFormFields extends Arguments implements FormFieldsInterface
{
    /**
     * The input field HTML container
     * @param  callable $callback return output
     * @return string
     */
    public function container(callable $callback): string
    {

        $length = count($this->items);

        $out = "";
        $out .= "<div class=\"mb-15\" data-count=\"{$length}\">";
        if (!is_null($this->label)) {
            $boolLength = (isset($this->validate['length'][0]) && $this->validate['length'][0] > 0);
            $req = ($boolLength) ? "*" : "";
            $out .= "<label>{$this->label}<span class=\"req\">{$req}</span><div class=\"message hide\"></div></label>";
        }
        if (!is_null($this->description)) {
            $out .= "<div class=\"description legend\">{$this->description}</div>";
        }
        $out .= $callback();
        $out .= "</div>";
        return $out;
    }

    /**
     * Input text
     * @return string
     */
    public function text(): string
    {
        if (isset($this->attrArr['data-clear'])) {
            $this->attr(["required" => "required"]);
        }

        return $this->container(function () {
            $typeAdd = (isset($this->attrArr['type']) ? "" : "type=\"text\" ");
            return "<input {$typeAdd}{$this->attr}name=\"{$this->name}\" data-name=\"{$this->dataName}\" value=\"{$this->value}\">";
        });
    }

    /**
     * Input hidden
     * @return string
     */
    public function hidden(): string
    {
        return "<input type=\"hidden\" {$this->attr}name=\"{$this->name}\" data-name=\"{$this->dataName}\" value=\"{$this->value}\">";
    }

    /**
     * Input text
     * @return string
     */
    public function date(): string
    {
        if (isset($this->attrArr['data-clear'])) {
            $this->attr(["required" => "required"]);
        }

        return $this->container(function () {
            $typeAdd = (isset($this->attrArr['type']) ? null : "type=\"date\" ");
            return "<input {$typeAdd}{$this->attr}name=\"{$this->name}\" data-name=\"{$this->dataName}\" value=\"{$this->value}\">";
        });
    }

    /**
     * Input text
     * @return string
     */
    public function datetime(): string
    {
        if (isset($this->attrArr['data-clear'])) {
            $this->attr(["required" => "required"]);
        }

        return $this->container(function () {
            $typeAdd = (isset($this->attrArr['type']) ? null : "type=\"datetime-local\" ");
            return "<input {$typeAdd}{$this->attr}name=\"{$this->name}\" data-name=\"{$this->dataName}\" value=\"{$this->value}\">";
        });
    }

    /**
     * Input textarea
     * @return string
     */
    public function textarea(): string
    {
        return $this->container(function () {
            return "<textarea {$this->attr}name=\"{$this->name}\" data-name=\"{$this->dataName}\">{$this->value}</textarea>";
        });
    }


    /**
     * Input select list
     * @return string
     */
    public function select(): string
    {
        return $this->container(function () {

            $name = $this->name;
            if (isset($this->attrArr['multiple'])) {
                $name .= "[]";
            }

            $out = "<select {$this->attr}name=\"{$name}\" data-name=\"{$this->dataName}\" autocomplete=\"off\">";
            foreach ($this->items as $val => $item) {
                $selected = ($this->isChecked($val)) ? "selected=\"selected\" " : null;
                $out .= "<option {$selected}value=\"{$val}\">{$item}</option>";
            }
            $out .= "</select>";
            return $out;
        });
    }

    /**
     * Input radio
     * @return string
     */
    public function radio(): string
    {
        return $this->container(function () {
            $out = "";
            foreach ($this->items as $val => $item) {
                $checked = ($this->isChecked($val)) ? "checked=\"checked\" " : null;
                $out .= "<label class=\"radio item small\">";
                $out .= "<input {$checked}type=\"radio\" {$this->attr}name=\"{$this->name}\" value=\"{$val}\"><span class=\"title\">{$item}</span>";
                $out .= "</label>";
            }
            return $out;
        });
    }

    /**
     * Input checkbox
     * @return string
     */
    public function checkbox(): string
    {
        return $this->container(function () {

            $out = "";
            $length = count($this->items);

            foreach ($this->items as $val => $item) {
                $name = ($length > 1) ? "{$this->name}[]" : $this->name;
                $checked = ($this->isChecked($val)) ? "checked=\"checked\" " : null;
                $out .= "<label class=\"checkbox item small\">";
                $out .= "<input {$checked}type=\"checkbox\" {$this->attr}name=\"{$name}\" value=\"{$val}\"><span class=\"title\">{$item}</span>";
                $out .= "</label>";
            }
            return $out;
        });
    }


    /**
     * Group fields
     * With some know how you can make it dynamical
     * @return string
     */
    public function group()
    {

        $lastKey = $this->lastKey();
        $out = "<div class=\"mb-20 group\" {$this->attr}data-key=\"{$lastKey}\">";

        if (!is_null($this->label)) {
            $out .= "<label>{$this->label}</label>";
        }
        if (!is_null($this->description)) {
            $out .= "<div class=\"legend mb-20 v3\">{$this->description}</div>";
        }

        $out .= "<ul>";
        $out .= $this->groupFields(function ($o, $_val) {
            $out = "<li>";
            $out .= $o;
            $out .= "</li>";
            return $out;
        });

        $out .= "</ul>";
        $out .= "</div>";

        return $out;
    }
}
