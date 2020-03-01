<?php

namespace App\Services;

use App\EntityType\EntityType;
use DateTime;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;


class ApiResponderService
{
    private $env;
    private $logger;
    private $serializer;

    public function __construct($env, LoggerInterface $logger, SerializerInterface $serializer)
    {
        $this->logger = $logger;
        $this->env = $env;
        $this->serializer = $serializer;
    }

    public function getResponse($data, $exceptionOrHttpCode, $metadata = null, EntityType $apiResponse = null)
    {
        if ($exceptionOrHttpCode && $exceptionOrHttpCode instanceof Exception) {
            $serializedData = $this->getArrayDataResponse($data, $exceptionOrHttpCode);
        }


        if ($metadata) {
            $serializedData = $this->getArrayDataResponse($data, null, $metadata, $apiResponse);
        } else {
            $serializedData = $this->getArrayDataResponse($data, $exceptionOrHttpCode, null, $apiResponse);
        }

        $response = new Response();

        if (is_numeric($exceptionOrHttpCode)) {
            $httpCode = $exceptionOrHttpCode;
        } else {
            if (method_exists($exceptionOrHttpCode, 'getHttpCode')) {
                $httpCode = $exceptionOrHttpCode->getHttpCode();
            } else {
                $httpCode = 500;
                //throw $exceptionOrHttpCode;
            }

        }

        $response->setStatusCode($httpCode);
        $response->setContent($serializedData);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function getArrayDataResponse($data = array(), $exceptionOrMetadata = null, $metadata = null, EntityType $entityType = null)
    {
        if ($exceptionOrMetadata && $exceptionOrMetadata instanceof Exception) {
            $exception = $exceptionOrMetadata;
            $arrayJson = [
                'message' => $exception ? $this->env != 'prod' ? $exception->getMessage() : "Internal Error" : "",
                'error' => $exception ? $exception->getCode() : "",
            ];
            if ($this->env != 'prod') {
                $arrayJson['log'] = $exception->getMessage() . ". File: " . $exception->getFile() . ". Line: " . $exception->getLine();
            }

            $this->logger->info("[API_EXCEPTION] " . $exception->getMessage() . ". File: " . $exception->getFile() . ". Line: " . $exception->getLine());
        } else {

            if ($entityType) {
                if(!$data) {
                    throw new \Exception("Null data");
                }
                $data = $entityType->processData($data);
            } else {
                if (!is_object($data) && is_array($data)) {
                    $firstElement = current($data);
                    if (!is_object($firstElement)) {
                        $data = $this->_processData($data);
                    }
                }
            }

            if ($metadata) {
                $arrayJson = [
                    'metadata' => $metadata,
                    'data' => $data,
                ];
            } else {
                $arrayJson = [
                    'data' => $data,
                ];
            }
        }


        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $serializedData = $this->serializer->serialize($arrayJson, "json", $context);

        return $serializedData;
    }

    private function _processData($data)
    {
        if ($this->isAssoc($data)) {
            return $this->processData($data);
        } else {
            $output = [];
            foreach ($data as $element) {
                $output[] = $this->processData($element);
            }
            return $output;
        }
    }

    private function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    private function processData($data)
    {
        $output = [];

        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $output[$this->camel_to_snake($key)] = $this->processData($value, null);
                } else {
                    if (is_object($value) && $value instanceof DateTime) {
                        $value = $value->format("Y-m-d");
                    }
                    $output[$this->camel_to_snake($key)] = $value;
                }
            }
        }
        return $output;
    }

    private function camel_to_snake( $input )
    {
        if ( preg_match ( '/[A-Z]/', $input ) === 0 ) { return $input; }

        $r = strtolower(preg_replace_callback_array(
            [
                '/([a-z])([A-Z])/' => function ($a) {
                    return $a[1] . "_" . strtolower ( $a[2] );
                },
                '/([a-z])([0-9][0-9]*)$/' => function ($a) {
                    return $a[1] . "_" . $a[2];
                },
                '/([a-z])([0-9]*)([A-Z])/' => function ($a) {
                    return $a[1] . "_" . $a[2] . "_" . strtolower ( $a[3] );
                }
            ]
            , $input));

        return $r;
    }

    public function getQueryParams($queryString)
    {
        $params = array();
        parse_str($queryString, $params);
        return $params;
    }

    public function plainDataToSubmodels($plainData = array(), $models = array())
    {
        $output = array();
        foreach ($plainData as $key => $value) {
            $submodels = $this->getSubmodels($key, $models);
            $varToEval = "\$output";

            foreach ($submodels as $submodel) {
                $varToEval .= "['$submodel']";
            }
            $value = str_replace("'", "\'", $value);
            $varToEval .= " = '$value';";
            eval($varToEval);
        }
        $output = $this->cleanEmptyModels($output);
        return $output;
    }

    private function getSubmodels($key, $models)
    {
        $submodels = [];
        $arr = explode('_', $key);

        foreach ($arr as $part) {
            if (in_array($part, $models)) {
                $submodels[] = $this->camel_to_snake($part);
            } else {
                $submodels[] = $this->camel_to_snake($part);
                break;
            }
        }

        return $submodels;
    }

    private function cleanEmptyModels($data)
    {
        if (array_key_exists("id", $data) && $data["id"] == "") return null;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->cleanEmptyModels($value);
            } else {
                if ($key == "id" || $this->endsWith($key, "_id")) {
                    $data[$key] = (int)$value;
                } else {
                    $data[$key] = $value;
                }
            }
        }

        return $data;
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length === 0 ||
            (substr($haystack, -$length) === $needle);
    }

    private function isAnErrorCode($httpCode)
    {
        return substr($httpCode, 0, 1) != 2;
    }

}
