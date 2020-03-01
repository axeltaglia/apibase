<?php
namespace App\Exception;

use Symfony\Component\Config\Definition\Exception\Exception;

class ApiException extends Exception
{
    protected $httpCode;

    public function __construct($message = "Undefined exception", $errorCode = 0, $httpCode = 0) {
        parent::__construct($message, $errorCode);
        $this->httpCode = $httpCode;
    }

    public function getHttpCode() {
        return $this->httpCode;
    }
}