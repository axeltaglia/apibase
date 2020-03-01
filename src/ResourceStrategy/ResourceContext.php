<?php
namespace App\ResourceStrategy;

class ResourceContext
{
    private $strategies;
    private $context;

    public function __construct()
    {
        $this->strategies = [];
        $this->context = [];
    }

    public function setContext($context) {
        $this->context = $context;
        return $this;
    }

    public function addStrategies($strategies=[])
    {
        foreach ($strategies as $strategy) {
            $strategy->config($this->context);
            $this->strategies[] = $strategy;
        }
        return $this;
    }

    public function execute() {
        $strategiesToExecute = [];

        foreach ($this->strategies as $strategy) {
            if($strategy->checkPreconditions()) {
                $strategy->preProcess();
                $strategy->validate();
                $strategiesToExecute[] = $strategy;
            }
        }

        foreach ($strategiesToExecute as $strategy) {
            $strategy->postProcess();
        }

        return $this;
    }

}