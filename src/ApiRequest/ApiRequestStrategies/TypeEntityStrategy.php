<?php
namespace App\ApiRequest\ApiRequestStrategies;
use App\ApiRequest\ApiRequest;
use App\ResourceStrategy\ResourceStrategy;

class TypeEntityStrategy extends ResourceStrategy {
    private $paramName;
    private $options;
    private $data;
    private $apiRequest;
    private $fn;
    private $repository;
    private $object;
    private $em;

    public function config($context) {
        $this->paramName = $context['paramName'];
        $this->options = $context['options'];
        $this->apiRequest = $context['apiRequest'];
        $this->em = $context['em'];
    }

    public function checkPreconditions() : bool
    {
        return array_key_exists('type', $this->options) && $this->options['type'] == ApiRequest::ENTITY && array_key_exists('entity', $this->options);
    }

    public function validate() {
        $this->repository = $this->em->getRepository('App:' . ucfirst($this->options['entity']));
        if(!$this->repository) {
            $this->apiRequest->throwError($this->paramName, [], ApiRequest::NON_EXISTENT_OBJECT, "({$this->options['entity']})");
        }
        $this->object = $this->repository->find($this->apiRequest->getValueFromRequest($this->paramName));
        if(!$this->object) {
            $this->apiRequest->throwError($this->paramName, [], ApiRequest::NON_EXISTENT_OBJECT, "(". $this->apiRequest->getValueFromRequest($this->paramName) .")");
        }
    }

    public function preProcess() {}

    public function postProcess()
    {
        $this->apiRequest->setData($this->paramName, $this->object);
    }
}