<?php

/**
 * @Package:    PHP Fuse - Form builder
 * @Author:     Daniel Ronkainen
 * @Licence:    The MIT License (MIT), Copyright © Daniel Ronkainen
                Don't delete this comment, its part of the license.
 * @Version:    2.2.1
 */

namespace PHPFuse\Form\Interfaces;

interface FormFieldsInterface
{
    /**
     * The input field HTML container
     * @param  callable $callback return output
     * @return string/html
     */
    public function container(callable $callback): string;

    /**
     * Input text
     * @return string/html
     */
    public function text(): string;

    /**
     * Input hidden
     * @return string/html
     */
    public function hidden(): string;

    /**
     * Input textarea
     * @return string/html
     */
    public function textarea(): string;

    /**
     * Input select list
     * @return string/html
     */
    public function select(): string;

    /**
     * Input radio
     * @return string/html
     */
    public function radio(): string;

    /**
     * Input checkbox
     * @return string/html
     */
    public function checkbox(): string;
}
