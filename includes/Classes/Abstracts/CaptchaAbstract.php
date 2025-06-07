<?php
namespace ProjectSend\Classes\Abstracts;

abstract class CaptchaAbstract {
    protected $method_name;

    public function __construct($name)
    {
        $this->method_name = $name;
    }

    public function getMethodName()
    {
        return $this->method_name;
    }

    abstract function check();
}