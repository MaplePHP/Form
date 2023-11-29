<?php

/**
 * @Package:    MaplePHP - Form builder template
 * @Author:     Daniel Ronkainen
 * @Licence:    Apache-2.0 license, Copyright © Daniel Ronkainen
                Don't delete this comment, its part of the license.
 */

namespace MaplePHP\Form\Examples;

use MaplePHP\Form\AbstractFormFields;

class TestFormFields extends AbstractFormFields
{

    /**
     * Input text (Take a look at AbstractFormFields)
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

}
