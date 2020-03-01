<?php
namespace App\ApiRequest;

use App\ApiRequest\ApiRequestStrategies\NotRequiredFieldStrategy;
use App\ApiRequest\ApiRequestStrategies\ProcessFieldStrategy;
use App\ApiRequest\ApiRequestStrategies\RequiredFieldIfConditionStrategy;
use App\ApiRequest\ApiRequestStrategies\RequiredFieldOnlyIfConditionStrategy;
use App\ApiRequest\ApiRequestStrategies\RequiredFieldStrategy;
use App\ApiRequest\ApiRequestStrategies\TypeEntityStrategy;
use App\ResourceStrategy\ResourceContext;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

abstract class ApiRequest
{
    const PARAMETER_REQUIRED = 1;
    const UNSPECIFIED_PARAMETER = 2;
    const NON_EXISTENT_OBJECT = 3;

    const ENTITY = 10;
    const STRING = 11;
    const INTEGER = 12;

    private $request; // api Request Object
    protected $givenParams; // params passed by request
    protected $logger;
    private $parameters; // params api ws accept with options (required, etc.)
    private $data; // final procesed data
    private $em;

    abstract protected function buildParameters();
    private $resourceContext;
    public function __construct(ObjectManager $entityManager, ResourceContext $resourceContext, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->givenParams = [];
        $this->parameters = [];
        $this->data = [];
        $this->resourceContext = $resourceContext;
        $this->logger = $logger;
    }

    protected function add($paramName, $options=NULL) {
        if(!$options) {
            $options = [];
        }
        $this->parameters[$paramName] = $options;
        return $this;
    }

    public function addData($paramName, $value) {
        $this->givenParams[$paramName] = $value;
        return $this;
    }

    public function setData($paramName, $value) {
        $this->data[$paramName] = $value;
        return $this;
    }

    public function submitData($request) {
        $this->request = $request;
        $this->buildParameters();
        $this->validateAndGetData();
        return $this->data;
    }

    private function validateAndGetData() {
        foreach ($this->parameters as $paramName => $options) {
            $this->applyStrategies($paramName, $options);
        }
    }

    private function applyStrategies($paramName, $options) {
        $this->resourceContext
            ->setContext([
                'paramName' => $paramName,
                'options' => $options,
                'apiRequest' => $this,
                'em' => $this->em
            ])
            ->addStrategies([
                new NotRequiredFieldStrategy(),
                new RequiredFieldStrategy(),
                new RequiredFieldOnlyIfConditionStrategy(),
                new RequiredFieldIfConditionStrategy(),
                new TypeEntityStrategy(),
                new ProcessFieldStrategy()
            ])
            ->execute()
        ;
    }

    public function get($paramName) {
        if(!$this->paramInRequest($paramName)) {
            $this->throwError($paramName, [], self::PARAMETER_REQUIRED);
        }

        if(array_key_exists($paramName, $this->data)) {
            return $this->data[$paramName];
        }

        if(!array_key_exists($paramName, $this->parameters)) {
            $this->throwError($paramName, [], self::UNSPECIFIED_PARAMETER);
        } else {
            return null;
        }
    }

    public function getArrayData() {
        return $this->data;
    }

    public function throwError($paramName, $options, $type, $aditionalData="") {
        if(array_key_exists('message', $options)) {
            $message = $options['message'];
        } else {
            switch ($type) {
                case self::PARAMETER_REQUIRED:
                $message = "Parámetro '$paramName' requerido";
                    break;
                case self::UNSPECIFIED_PARAMETER;
                $message = "Parámetro '$paramName' no especificado";
                break;
                case self::NON_EXISTENT_OBJECT;
                    $message = "Objeto '$paramName' no encontrado";
                    break;
                default:
                    $message = "Tipo de error desconocido";
                    break;
            }
        }
        if($aditionalData != "") {
            $message .= " $aditionalData";
        }

        if(array_key_exists('errorCode', $options)) {
            $errorCode = $options['errorCode'];
        } else {
            $errorCode = 1;
        }

        throw new \Exception($message, $errorCode);
    }

    public function paramInRequest($paramName) {
        if(array_key_exists($paramName, $this->givenParams)) return true;
        if($this->request->get($paramName) !== null) return true;
        if($this->request->files->get($paramName) !== null) return true;
        return false;
    }

    public function getValueFromRequest($paramName) {

        if(array_key_exists($paramName, $this->givenParams)) {
            return $this->givenParams[$paramName];
        } else {
            if($this->request->get($paramName) !== null) {
                $this->givenParams[$paramName] = $this->request->get($paramName);
                return $this->givenParams[$paramName];
            } else if($this->request->files->get($paramName) !== null) {
                $this->givenParams[$paramName] = $this->request->files->get($paramName);
                return $this->givenParams[$paramName];
            }

            return null;
        }
    }
}