<?php

namespace App\Services;

use App\Exception\ResourceNotFoundException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class ObjectPopulatorService
{

    private $logger;
    private $em;
    private $kernel;

    public function __construct(LoggerInterface $logger, ObjectManager $entityManager, KernelInterface $kernel)
    {
        $this->logger = $logger;
        $this->em = $entityManager;
        $this->kernel = $kernel;
    }

    public function populateObject($data, $allowedProps = array(), &$object, ClassMetadata $metadata)
    {
        foreach ($data as $prop => $value) {
            $attribute = $this->getClassAttribute($prop);
            // ManyToOne assoc
            if ($metadata->isSingleValuedAssociation($attribute)) {
                $className = $this->getClassName($metadata->getAssociationTargetClass($attribute));
                $this->populateWithAssociatedObject($object, $attribute, $className, $value);
            } // OneToMany assoc
            else if ($metadata->isCollectionValuedAssociation($attribute)) {
                $className = $this->getClassName($metadata->getAssociationTargetClass($attribute));
                $this->populateWithObjectsCollection($object, $attribute, $className, $value);
            } // Common attribute
            else if ($metadata->hasField($attribute)) {
                $this->populateWithCommonValue($object, $attribute, $value);
            } else {
                // Invalid field
            }
        }
    }

    private function getClassAttribute($prop)
    {
        $attribute = $this->snakeToCamelCase($prop);
        return $attribute;
    }

    private function snakeToCamelCase($prop)
    {
        return lcfirst(str_replace('_', '', ucwords($prop, '_')));
    }

    private function getClassName($completeEntityPath)
    {
        $split = explode('\\', $completeEntityPath);
        return end($split);
    }

    private function populateWithAssociatedObject(&$object, $attributeName, $associatedClassName, $associatedObjectOrArrayData)
    {
        $setterMethod = $this->getSetterMethod($attributeName);

        if ($associatedObjectOrArrayData != null) {
            $associatedObject = $this->getAssociatedObject($associatedClassName, $associatedObjectOrArrayData);
            if (!$associatedObject) {
                throw new ResourceNotFoundException("Resourse $associatedClassName' not exists");
            }
            if(!method_exists($object, $setterMethod)) {
                throw new Exception("Method $setterMethod doesn't exist.");
            }
            $object->{$setterMethod}($associatedObject);
        }
    }

    private function getSetterMethod($attribute)
    {
        return "set" . ucfirst($attribute);
    }

    private function getAssociatedObject($associatedObjectClassName, $objectOrArrayData)
    {
        if (is_object($objectOrArrayData) && $this->getClassName(get_class($objectOrArrayData)) == $associatedObjectClassName) {
            return $objectOrArrayData;
        }

        if (is_array($objectOrArrayData)) {
            if (array_key_exists('id', $objectOrArrayData)) {
                $repository = $this->getRepository($associatedObjectClassName);
                $object = $repository->find($objectOrArrayData['id']);
                unset($objectOrArrayData['id']);
                return $this->populateExistingObject($object, $associatedObjectClassName, $objectOrArrayData);
            } else {
                return $this->getNewAndPopulateObject($associatedObjectClassName, $objectOrArrayData);
            }
        } else {
            throw new Exception("Invalid FK Id");
        }
    }

    private function getRepository($className)
    {
        $repository = $this->em->getRepository('App:' . $className);
        if (!$repository) {
            throw new RepositoryNotFoundException("Repository Not found for '" . 'App:' . $className . "'");
        }
        return $repository;
    }

    private function getNewAndPopulateObject($className, $data)
    {
        $modelName = "App\Entity\\" . $className;
        $object = new $modelName;
        $metadata = $this->em->getClassMetadata('App:' . $className);
        $this->populateObject($data, [], $object, $metadata);
        return $object;
    }

    private function populateExistingObject($object, $className, $data)
    {
        $metadata = $this->em->getClassMetadata('App:' . $className);
        $this->populateObject($data, [], $object, $metadata);
        return $object;
    }

    private function populateWithObjectsCollection(&$object, $attributeName, $className, $collection = array())
    {
        foreach ($collection as $item) {
            $associatedObject = $this->getAssociatedObject($className, $item);
            $addMethod = "add" . ucfirst($this->unPluralize($attributeName));
            if(!method_exists($object, $addMethod)) {
                throw new Exception("Method $addMethod doesn't exist.");
            }
            $object->{$addMethod}($associatedObject);
        }
    }

    private function populateWithCommonValue(&$object, $attribute, $value)
    {
        $setterMethod = $this->getSetterMethod($attribute);
        $this->executeSetterMethod($object, $setterMethod, $value);
    }

    private function executeSetterMethod(&$object, $setterMethod, $value)
    {
        if (method_exists($object, $setterMethod)) {
            $object->{$setterMethod}($value);
        }
    }

    private function unPluralize($plural)
    {
        $singulars = Yaml::parseFile($this->kernel->getProjectDir() . '/src/Inflector/singularFromPlural.yaml');
        if(array_key_exists($plural, $singulars)) {
            return $singulars[$plural];
        }

        return substr($plural, 0, -1);
    }
}
