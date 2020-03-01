<?php
namespace App\ApiRequest\ApiRequestStrategies;
use App\ResourceStrategy\ResourceStrategy;

class ProcessFieldStrategy extends ResourceStrategy {
    private $paramName;
    private $options;
    private $data;
    private $apiRequest;

    public function config($context) {
        $this->paramName = $context['paramName'];
        $this->options = $context['options'];
        $this->apiRequest = $context['apiRequest'];
    }

    public function checkPreconditions() : bool
    {
        return array_key_exists('process', $this->options) && is_callable($this->options['process']);
    }

    public function validate() {}

    public function preProcess() {}

    public function postProcess()
    {
        $fn = $this->options['process'];
        if($this->apiRequest->getValueFromRequest($this->paramName) !== null) {
            $this->apiRequest->setData($this->paramName, $fn($this->apiRequest->getValueFromRequest($this->paramName)));
        }
    }
}