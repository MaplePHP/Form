<?php 
/**
 * @Package: 	PHP Fuse - Form builder
 * @Author: 	Daniel Ronkainen
 * @Licence: 	The MIT License (MIT), Copyright © Daniel Ronkainen
 				Don't delete this comment, its part of the license.
 * @Version: 	2.2.1
 */
namespace PHPFuse\Form\Interfaces;

interface FormFieldsInterface {

	
	public function container(callable $callback): string;
	public function text(): string;
	public function textarea(): string;
	public function hidden(): string;

}
