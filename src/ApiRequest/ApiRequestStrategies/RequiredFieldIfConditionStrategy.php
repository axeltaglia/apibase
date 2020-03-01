<?php
namespace App\ApiRequest\ApiRequestStrategies;
use App\ApiRequest\ApiRequest;
use App\ResourceStrategy\ResourceStrategy;

class RequiredFieldIfConditionStrategy extends ResourceStrategy {
    private $paramName;
    private $options;
    private $data;
    private $apiRequest;
    private $fn;

    public function config($context) {
        $this->paramName = $context['paramName'];
        $this->options = $context['options'];
        $this->apiRequest = $context['apiRequest'];
    }

    public function checkPreconditions() : bool
    {
        $condition = $this->options['required'];
        if(!is_array($condition)) {
            return false;
        }
        $label = key($condition);
        $this->fn = current($condition);
        return array_key_exists('required', $this->options) && $label == 'if' && is_callable($this->fn);
    }

    public function validate() {
        $fn = $this->fn;
        if($fn() && !$this->apiRequest->paramInRequest($this->paramName)) {
            $this->apiRequest->throwError($this->paramName, [], ApiRequest::PARAMETER_REQUIRED);
        }
    }

    public function preProcess() {
        $this->apiRequest->setData($this->paramName, $this->apiRequest->getValueFromRequest($this->paramName));
    }

    public function postProcess()
    {

    }
}