<?php

/**
 * @Package:    PHPFuse - Form builder interface
 * @Author:     Daniel Ronkainen
 * @Licence:    The MIT License (MIT), Copyright © Daniel Ronkainen
                Don't delete this comment, its part of the license.
 */

namespace PHPFuse\Form\Interfaces;

interface FieldInterface
{
    /**
     * Get forms
     * @return array
     */
    public function getFormData(): array;

    /**
     * Check if form exists
     * @param  string  $name Form name
     * @return boolean
     */
    public function hasFormData(): bool;

    /**
     * Get fields (will throw Exception if form is missing)
     * @param  string $name Form name
     * @return array
     */
    public function getFields(): array;

    /**
     * Set values
     * @param array|object $values Will be converted to array
     */
    public function setValues(array|object $values): void;

    /**
     * Get settted values
     * @return array
     */
    public function getValues(): array;

    /**
     * Create form
     * @param string $name   Form name
     * @param array $fields
     */
    public function add($fields): self;

    /**
     * Set validation array
     * @param string $id
     * @param array  $arr
     */
    public function setValidateData(string $id, array $arr): void;

    /**
     * Get forms validation options
     * @return array
     */
    public function getValidateData(): array;

    /**
     * Get forms validation options
     * @param  string $key field key
     * @return array
     */
    public function getValidateDataRow(string $key): array;


    /**
     * Build all form data before valiate or read
     * This will reset validation data.
     * @return void
     */
    public function build(): void;


    /**
     * Build HTML
     * @param  array $inpArr
     * @param  callable $callback
     * @return string/html
     */
    public function html(array $inpArr): string;

    /**
     * Build all form data before valiate or read (This is immutable)
     * @return static
     */
    public function withBuild(): static;

    /**
     * Quick generate and return single fields
     * @return string
     */
    public function get(): string;

    /**
     * Check if form exists
     * @param  string $key The form key
     * @return bool
     */
    public function hasForm(): bool;

    /**
     * Get built form (Will return exception if does not exist!)
     * @param  string $key form key
     * @return string
     */
    public function getForm(): string;
}
