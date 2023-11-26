<?php

/**
 * @Package:    MaplePHP - Form builder
 * @Author:     Daniel Ronkainen
 * @Licence:    Apache-2.0 license, Copyright © Daniel Ronkainen
                Don't delete this comment, its part of the license.
 * @Version:    2.2.1
 */

namespace MaplePHP\Form\Interfaces;

interface FormFieldsInterface
{
    /**
     * The input field HTML container
     * @param  callable $callback return output
     * @return string
     */
    public function container(callable $callback): string;

    /**
     * Input text
     * @return string
     */
    public function text(): string;

    /**
     * Input hidden
     * @return string
     */
    public function hidden(): string;

    /**
     * Input textarea
     * @return string
     */
    public function textarea(): string;

    /**
     * Input select list
     * @return string
     */
    public function select(): string;

    /**
     * Input radio
     * @return string
     */
    public function radio(): string;

    /**
     * Input checkbox
     * @return string
     */
    public function checkbox(): string;

    /**
     * Main field instance
     * @param  FieldInterface $inst
     * @return void
     */
    public function setFieldInst(FieldInterface $inst): void;
}
