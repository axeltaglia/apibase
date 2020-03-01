<?php
namespace App\Exception;

class ResourceNotFoundException extends ApiException
{
    public function __construct($message = "Resource not found", $errorCode = 404, $httpCode = 404) {
        parent::__construct($message, $errorCode, $httpCode);
    }
}