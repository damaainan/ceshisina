<?php

namespace Composite;

/**
 * FormElement类
 */
abstract class FormElement
{
    /**
     * renders the elements' code
     *
     * @param int $indent
     *
     * @return mixed
     */
    abstract public function render($indent = 0);
}