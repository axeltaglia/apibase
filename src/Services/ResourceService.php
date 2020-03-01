<?php
namespace App\Services;

use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Security;
use App\Exception\ResourceNotFoundException;
use App\Exception\ResourceNotValidException;

abstract class ResourceService
{
    public $repository;
    protected $resource;
    protected $logger;
    protected $em;
    protected $validator;
    protected $populator;
    protected $data;
    protected $serializer;
    protected $security;

    public function __construct(LoggerInterface $logger, ObjectManager $entityManager, ValidatorInterface $validator, ObjectPopulatorService $populator, SerializerInterface $serializer, Security $security)
    {
        $this->resource = null;
        $this->logger = $logger;
        $this->em = $entityManager;
        $this->repository = $this->em->getRepository('App:' . $this->getResourceClassName());
        $this->validator = $validator;
        $this->populator = $populator;
        $this->serializer = $serializer;
        $this->security = $security;
    }

    public function getObject(string $content, $entity, string $type)
    {
        $this->data = $this->serializer->deserialize($content, $entity, $type);
    }

    public function createWithRelations()
    {
        $this->beforeCreatePersisting();
        $this->resource = $this->em->merge($this->data);
        $this->em->flush();
        $this->afterCreate();
        return $this->resource;
    }

    public function updateWithRelations()
    {
        $this->beforeUpdatePersisting();
        $this->data->setId($this->resource->getId());
        $this->resource = $this->em->merge($this->data);
        $this->em->flush();
        $this->afterUpdate();
        return $this->resource;
    }

    public function create($data=array()) 
    {
        $this->data = $data;
        if($this->createCondition()) {
            $this->resource = $this->getNewResource();
            $this->beforeCreatePopulation();
            $this->populate($this->data);
            $this->beforeCreateValidation();
            $this->validateResource();
            $this->beforeCreatePersisting();
            $this->persist();
            $this->afterCreate();
            return $this->resource;
        }
        return $this->ifResourceCouldNotBeCreated();
    }

    public function update($data) 
    {
        $this->data = $data;
        if($this->updateCondition()) {
            $this->beforeUpdatePopulation();
            $this->populate($this->data);
            $this->beforeUpdateValidation();
            $this->validateResource();
            $this->beforeUpdatePersisting();
            $this->afterUpdate();
            $this->persist();
            return $this->resource;
        }
        return ifResourceCouldNotBeUpdated();
    }

    public function delete() {
        if($this->deleteCondition()) {
            $this->checkIfResourceWasSet();
            $this->beforeDelete();
            $this->destroy();
            $this->afterDelete();
            return true;
        }
        return $this->ifResourceCouldNotBeDeleted();
    }

    protected function createCondition() { return true; }
    protected function beforeCreatePopulation() {}
    protected function beforeCreateValidation() {}
    protected function beforeCreatePersisting() {}
    protected function afterCreate() {}
    protected function ifResourceCouldNotBeCreated() { return null; }

    protected function updateCondition() { return true; }
    protected function beforeUpdatePopulation() {}
    protected function beforeUpdateValidation() {}
    protected function beforeUpdatePersisting() {}
    protected function afterUpdate() {}
    protected function ifResourceCouldNotBeUpdated() { return null; }

    protected function deleteCondition() { return true; }
    protected function beforeDelete() {}
    protected function afterDelete() {}
    protected function ifResourceCouldNotBeDeleted() { return null; }


    abstract protected function getNewResource();
    abstract protected function getResourceClassName() : string;

    public function checkIfResourceWasSet() {
        if(!$this->resource) {
            throw new ResourceNotFoundException($this->getResourceClassName() . " doesn't exist", 404, 404);
        }
    }

    public function find($id) {
        if($id) {
            $this->resource = $this->repository->find($id);
            $this->checkIfResourceWasSet();
        }
        return $this->resource;
    }

    public function findAsArray($id) {
        return $this->repository
            ->select('resource')
            ->where('resource.id', $id)
            ->getOneResult()
        ;
    }

    public function _find($id) {
        $this->find($id);
        if(!$this->resource) throw new \Exception("Resource with id $id not found.");
        return $this->resource;
    }

    public function setResource($resource) {
        $this->resource = $resource;
        return $this->resource;
    }

    public function getResource() {
        return $this->resource;
    }

    public function _getResource() {
        if(!$this->resource) throw new \Exception("Resource not set.");
        return $this->resource;
    }

    public function getData($key=null) {
        if(!$this->data) return null;
        if(!$key) return $this->data;
        return array_key_exists($key, $this->data)? $this->data[$key] : null;
    }

    public function _getData($key=null) {
        if(empty($this->data)) throw new \Exception("Empty data.");
        if(!$key) return $this->data;

        if(array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        throw new \Exception("Data not found ('$key').");
    }

    public function setData($key, $value) {
        $this->data[$key] = $value;
    }

    public function all($filters=array(), $orderBy=array(), $limit=null, $offset=null) {
        return $this->repository->findBy($filters, $orderBy, $limit, $offset);
    }

    public function findOneBy($filters) {
	    $this->resource = $this->repository->findOneBy($filters);
        return $this->resource;
    }

    protected function populate($data, $allowedProps = null) {
        $this->checkIfResourceWasSet();
        $metadata = $this->em->getClassMetadata('App:' . $this->getResourceClassName());

        if(!$allowedProps) {
            $this->populator->populateObject($data, [], $this->resource, $metadata);
        } else {
            $this->populator->populateObject($data, [], $this->resource, $metadata);
        }
        
    }

    protected function validateResource() {
        $this->checkIfResourceWasSet();

        $errors = $this->validator->validate($this->resource);
        if (count($errors) > 0) {
            $errorList = [];
            foreach ($errors as $error) {
                $errorList[] = $error->getMessage();
            }

            $errorList = implode("\n", $errorList);

            throw new ResourceNotValidException((string) $errorList, 400, 400);
        }
    }

    public function persist($flush=true) {
        $this->checkIfResourceWasSet();

        if(method_exists($this->resource, 'setUpdatedAt')) {
            $this->resource->setUpdatedAt(new \DateTime());
        }
        
        $this->em->persist($this->resource);
        if($flush) {
            $this->em->flush();
        }
        return $this->resource;
    }

    protected function destroy() {
        $this->em->remove($this->resource);
        $this->em->flush();
    }

    public function permitParams(&$data, $allowedParams) {
        foreach ($data as $param => $value) {
            if(!in_array($param, $allowedParams)) {
                unset($data[$param]);
            }
        }
    }

}
