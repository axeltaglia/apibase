<?php
namespace App\ResourceStrategy;

abstract class ResourceStrategy {

    abstract public function config($context);

    abstract public function checkPreconditions() : bool;

    abstract public function preProcess();

    abstract public function validate();

    abstract public function postProcess();

    public function getNameOfClass()
    {
        return static::class;
    }
}