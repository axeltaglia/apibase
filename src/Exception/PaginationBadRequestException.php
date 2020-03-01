<?php
namespace App\Exception;

use App\Exception\ApiException;

class PaginationBadRequestException extends ApiException
{
    public function __construct($message = "Pagination bad request exception", $errorCode = 404, $httpCode = 404) {
        parent::__construct($message, $errorCode, $httpCode);
    }
}