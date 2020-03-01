<?php
namespace App\ApiRequest\ApiRequestStrategies;
use App\ApiRequest\ApiRequest;
use App\ResourceStrategy\ResourceStrategy;

class NotRequiredFieldStrategy extends ResourceStrategy {
    private $paramName;
    private $options;
    private $apiRequest;

    public function config($context) {
        $this->paramName = $context['paramName'];
        $this->options = $context['options'];
        $this->apiRequest = $context['apiRequest'];
    }

    public function checkPreconditions() : bool
    {
        return array_key_exists('required', $this->options) && $this->options['required'] === false;
    }

    public function validate() {
    }

    public function preProcess() {
        if($this->apiRequest->getValueFromRequest($this->paramName) !== null) {
            $this->apiRequest->setData($this->paramName, $this->apiRequest->getValueFromRequest($this->paramName));
        }
    }

    public function postProcess()
    {

    }
}