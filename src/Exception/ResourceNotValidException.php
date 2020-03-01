<?php
namespace App\Exception;

class ResourceNotValidException extends ApiException
{
    public function __construct($message = "Resource not valid", $errorCode = 400, $httpCode = 400) {
        parent::__construct($message, $errorCode, $httpCode);
    }
}