<?php
namespace App\EntityType;

abstract class EntityType
{
    const String = 0;

    abstract protected function buildFields();

    private $config;
    private $fields;
    private $args;

    public function __construct()
    {
        $this->fields = [];
        $this->config = [];
        $this->args = [];
    }

    public function addArg($arg, $value) {
        $this->args[$arg] = $value;
    }

    public function getArg($arg) {
        $output = null;
        if(array_key_exists($arg, $this->args)) {
            $output = $this->args[$arg];
        }
        return $output;
    }

    public function setArgs($args) {
        $this->args = $args;
    }

    protected function field($fieldName, $options=NULL) {
        if(!$options) {
            $options = [];
        }
        $this->fields[$fieldName] = $options;
        return $this;
    }

    protected function config($key, $value = true) {
        $this->config[$key] = $value;
        return $this;
    }

    public function isFieldEnabled($field) {
        return array_key_exists($field, $this->fields);
    }

    public function getFieldName($key) {
        if(array_key_exists('snake_case', $this->config) && $this->config['snake_case']) {
            return $this->camel_to_snake($key);
        }

        if(array_key_exists('as', $this->fields[$key])) {
            return $this->fields[$key]['as'];
        }

        return $key;
    }

    public function getFieldValue($field, $data, $options) {
        if(array_key_exists($field, $data)) {
            return $this->processDataType($field, $data, $options);
        }

        if(array_key_exists('fetch', $options) && is_callable($options['fetch'])) {
            $fnFetch = $options['fetch'];
            return $fnFetch($data);
        }

        throw new \Exception("Field '$field' doesn't exist in data");
    }

    private function processDataType($field, $data, $options) {
        if(array_key_exists('type', $options)) {
            if(is_array($options['type'])) {
                $type = array_shift($options['type']);
                $arrayData = $data[$field];
                if(!is_array($arrayData)) throw new \Exception("Expected an array for field '$field'");
                return $this->processArrayDataType($type, $arrayData);
            } else {
                $type = $options['type'];
                $data = $data[$field];
                return $this->processPlainDataType($type, $data);
            }
        } else if(array_key_exists('process', $options)) {
            $fn = $options['process'];
            return $fn($data[$field]);
        }
        return $data[$field];
    }

    public function processArrayDataType($type, $arrayData) {
        $output = [];
        $instanceType = new $type();

        $instanceType->setArgs($this->args);

        foreach ($arrayData as $data) {
            $output[] = $instanceType->processData($data);
        }

        return $output;
    }

    public function processPlainDataType($type, $data) {
        $instanceType = new $type();
        $instanceType->setArgs($this->args);
        return $instanceType->processData($data);
    }

    public function processData($data) {
        $this->buildFields();
        $output = [];
        foreach ($this->fields as $fieldName => $options) {
            $output[$this->getFieldName($fieldName)] = $this->getFieldValue($fieldName, $data, $options);
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

}
