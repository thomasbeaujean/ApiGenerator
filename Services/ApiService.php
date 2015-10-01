<?php

namespace tbn\ApiGeneratorBundle\Services;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\HttpFoundation\Request;
use tbn\DoctrineRelationVisualizerBundle\Entity\Entity;
use tbn\ApiGeneratorBundle\Exception\MethodNotFoundException;

/**
 *
 * @author Thomas BEAUJEAN
 *
 * ref: tbn.api_generator.service.api_service
 *
 */
class ApiService
{
    protected $doctrine = null;
    protected $validator = null;
    protected $authorizationService = null;
    protected $entityRights = null;

    /**
     *
     * @param array                $entityRights
     * @param AuthorizationService $authorizationService
     * @param type                 $doctrine
     * @param type                 $validator
     */
    public function __construct(array $entityRights, AuthorizationService $authorizationService, $doctrine, $validator)
    {
        $this->authorizationService = $authorizationService;
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->entityRights = $entityRights;
    }

    /**
     * Retrieve an entity by the identifier listed in the data
     *
     * @param string $entityAlias
     * @param object $data
     *
     * @throws Exception
     *
     * @return Entity
     */
    public function retrieveEntity($entityAlias, $data)
    {
        $entityClass = $this->getClassByEntityAlias($entityAlias);
        $entity = $this->retrieveEntityByClass($entityClass, $data);

        if ($entity === null) {
            throw new \Exception('The entity ['.$entityAlias.'] with the data ['.print_r($data, true).'] was not found.');
        }

        return $entity;
    }

    /**
     * Retrieve an entity by the identifier listed in the data
     *
     * @param string $entityAlias
     *
     * @throws Exception
     *
     * @return array
     */
    public function retrieveAllEntities($entityAlias)
    {
        $entityClass = $this->getClassByEntityAlias($entityAlias);

        $doctrine = $this->doctrine;
        $em = $doctrine->getManager();
        $repository = $em->getRepository($entityClass);

        $entities = $repository->findAll();

        return $entities;
    }

    /**
     *
     * @param Request $request
     * @param string  $entityAlias
     * @return \tbn\ApiGeneratorBundle\Services\NULL[][]
     */
    public function handleAction(Request $request, $entityAlias)
    {
        $itemNamespace = $this->getClassByEntityAlias($entityAlias);

        $parameters = $request->request->all();

        if (count($parameters) === 0) {
            throw new \Exception('No data was prodived');
        }

        zdebug($parameters);
        $entities = array();
        zdebug($entityAlias);
        foreach ($parameters as $data) {
            $entity = $this->getEntityByData($itemNamespace, $data);
            $entities[] = $entity;
        }
        zdebug($entities);

        //validate assertions
        $this->validateEntities($entities);

        //persist before check authorization
        //because the authorization uses doctrine to know if we create update or delete
        $this->persistEntities($entities);
        $this->checkEntitiesAuthorization($entities, $entityAlias);

        //finally flush entities
        $em = $this->doctrine->getManager();
        $em->flush();

        return $entities;
    }

    /**
     *
     * @param string $entityClass
     * @param array  $data
     *
     * @return NULL
     */
    public function getEntityByData($entityClass, $data)
    {
        if (!is_array($data)) {
            throw new \Exception('The data for the item '.$entityClass.' must be an array. Data found ['.print_r($data, true).']');
        }

        $entityClassIdentifiers = $this->getIdentifiers($entityClass);

        $containsIdentifier = true;

        foreach ($entityClassIdentifiers as $entityClassIdentifier) {
            if (!array_key_exists($entityClassIdentifier, $data)) {
                $containsIdentifier = false;
            } else {
                if ($data[$entityClassIdentifier] === null) {
                    $containsIdentifier = false;
                }
            }
        }

        $entity = null;

        //new entity
        if ($containsIdentifier === false) {
            $entity = $this->createEntity($entityClass, $data);
        } else {
            $delete = false;
            if (array_key_exists('_delete', $data)) {
                if ($data['delete'] === true) {
                    $delete = true;
                }
            }

            if (!$delete) {
                $entity = $this->retrieveEntityByClass($entityClass, $data);

                //the post contains the id
                if ($this->hasGenerator($entityClass)) {
                    if ($entity === null) {
                        throw new \Exception('The entity ['.$entityClass.'] with the data ['.print_r($data, true).'] was not found.');
                    }

                    $entity = $this->updateEntity($entityClass, $entity, $data);
                } else {
                    //update
                    if ($entity === null) {
                        $entity = $this->createEntity($entityClass, $data);
                    } else {
                        $entity = $this->updateEntity($entityClass, $entity, $data);
                    }
                }

            } else {
                $this->deleteEntity($data);
            }
        }

        return $entity;
    }

    /**
     *
     * @param string $entityClass
     * @param array  $data
     *
     * @return type
     * @throws \Exception
     */
    protected function retrieveEntityByClass($entityClass, $data)
    {
        $doctrine = $this->doctrine;
        $em = $doctrine->getManager();
        $repository = $em->getRepository($entityClass);

        $identifiers = $this->getIdentifiers($entityClass);

        $criteria = array();

        //create the array of identifiers
        foreach ($identifiers as $identifier) {
            if (!array_key_exists($identifier, $data)) {
                throw new \Exception('The identifier ['.$identifier.'] was not in the data provided for the entity ['.$entityClass.']');
            }
            $criteria[$identifier] = $data[$identifier];
        }

        $entity = $repository->findOneBy($criteria);

        return $entity;
    }

    /**
     * Check that the action is allowed
     * @param array  $entities
     * @param string $entityAlias
     */
    protected function checkEntitiesAuthorization($entities, $entityAlias)
    {
        $em = $this->doctrine->getManager();
        $uow = $em->getUnitOfWork();

        $authorizationService = $this->authorizationService;

        foreach ($entities as $entity) {
            if ($uow->isScheduledForInsert($entity)) {
                $authorizationService->isEntityAliasAllowedForRequest($entityAlias, 'create');
            }
            if ($uow->isScheduledForUpdate($entity)) {
                $authorizationService->isEntityAliasAllowedForRequest($entityAlias, 'update');
            }
            if ($uow->isScheduledForDelete($entity)) {
                $authorizationService->isEntityAliasAllowedForRequest($itemNamespace, 'delete');
            }
        }
    }

    /**
     *
     * @param array $entities
     * @throws \Exception
     */
    protected function validateEntities($entities)
    {
        //validate entities
        $validator = $this->validator;

        foreach ($entities as $entity) {
            $errorList = $validator->validate($entity);

            if (count($errorList) > 0) {
                throw new \Exception(print_r($errorList, true));
            }
        }
    }

    /**
     * Persist and flush entities
     * @param array $entities
     */
    protected function persistEntities($entities)
    {
        $em = $this->doctrine->getManager();

        //persist entities
        foreach ($entities as $entity) {
            $em->persist($entity);
        }
    }

    /**
     *
     * @param String $entityClass
     * @return unknown
     */
    protected function getMetadata($entityClass)
    {
        $em = $this->doctrine->getManager();
        $meta = $em->getMetadataFactory()->getMetadataFor($entityClass);

        return $meta;
    }

    /**
     * Get the classname
     * @param string $entityClass
     *
     * @return string The classname
     */
    protected function getEntityClassname($entityClass)
    {
        $meta = $this->getMetadata($entityClass);

        return $meta->name;
    }

    /**
     * Get the field mappings
     * @param String $entityClass
     *
     * @return array
     */
    protected function getFieldMappings($entityClass)
    {
        $meta = $this->getMetadata($entityClass);

        return $meta->fieldMappings;
    }

    /**
     * Get the association mappings
     * @param String $entityClass
     *
     * @return array
     */
    protected function getAssociationMappings($entityClass)
    {
        $meta = $this->getMetadata($entityClass);

        return $meta->associationMappings;
    }

    /**
     * Get the identifiers
     *
     * @param String $entityClass
     *
     * @return array The identifiers
     *
     */
    protected function getIdentifiers($entityClass)
    {
        $meta = $this->getMetadata($entityClass);

        return $meta->identifier;
    }


    /**
     * Get the id generator
     *
     * @param String $entityClass
     *
     * @return array The id generator
     *
     */
    protected function hasGenerator($entityClass)
    {
        $meta = $this->getMetadata($entityClass);

        if (ClassMetadataInfo::GENERATOR_TYPE_NONE === $meta->generatorType) {
            $hasGenerator = false;
        } else {
            $hasGenerator = true;
        }

        return $hasGenerator;
    }


    /**
     *
     * @param string $entityClass
     * @param object $data
     */
    protected function createEntity($entityClass, $data)
    {
        $entityClassname = $this->getEntityClassname($entityClass);

        $entity = new $entityClassname();

        $identifiers = $this->getIdentifiers($entityClass);
        $meta = $this->getMetadata($entityClass);

        $generatorType = $meta->generatorType;

        $ignoredAttributes = array();

        //is the id generated manually
        if ($generatorType !== ClassMetadataInfo::GENERATOR_TYPE_NONE) {
            //nope, so we remove the identifier to be generated automatically
            foreach ($identifiers as $identifier) {
                $ignoredAttributes[] = $identifier;
            }
        }

        //
        $this->setFields($entity, $entityClass, $data, $ignoredAttributes);
        $this->setAssociations($entity, $entityClass, $data, $ignoredAttributes);

        return $entity;
    }

    /**
     *
     * @param object $entity
     * @param string $entityClass
     * @param array $data
     * @param array $ignoredAttributes
     *
     * @throws \Exception
     */
    protected function setAssociations($entity, $entityClass, $data, $ignoredAttributes = array())
    {
        $associationMappings = $this->getAssociationMappings($entityClass);
        //
        foreach ($associationMappings as $associationName => $associationMapping) {
            if (!in_array($associationName, $ignoredAttributes)) {
                $isOwningSide = $associationMapping['isOwningSide'];

                //the entity owns the association, so it is linked to ONE another entity
                if ($isOwningSide) {
                    $nullable = false;
                    $joinColumns = $associationMapping['joinColumns'];

                    //parse join columns to check that they are all not nullable
                    foreach ($joinColumns as $joinColumn) {
                        if ($joinColumn['nullable'] === true) {
                            $nullable = true;
                        }
                    }

                    //the targeted entity
                    $targetEntityClass = $associationMapping['targetEntity'];

                    //the field has been sent
                    if (array_key_exists($associationName, $data)) {
                        $value = $data[$associationName];

                        if (!$nullable) {
                            if ($value === null) {
                                throw new \Exception('The value NULL for the field ['.$associationName.'] is not allowed; entity ['.$entityClass.']');
                            }
                        }

                        if ($value !== null) {
                            //Get the associated entity
                            $associatedEntity = $this->getEntityByData($targetEntityClass, $value);
                        } else {
                            $associatedEntity = null;
                        }

                        $method = 'set'.ucfirst($associationName);

                        if (!method_exists($entity, $method)) {
                            throw new MethodNotFoundException('The entity '.$entityClass.' requires to the '.$method.' method');
                        }

                        //set The value
                        call_user_method($method, $entity, $associatedEntity);
                    } else {
                        //
                        if (!$nullable) {
                            throw new \Exception('The association ['.$associationName.'] has not been sent and is mandatory for an entity ['.$entityClass.']');
                        }
                    }
                }
            }
        }

        return $entity;
    }

    /**
     *
     * @param object $entity
     * @param string $entityClass
     * @param array  $data
     * @param array  $ignoredAttributes
     *
     * @throws \Exception
     */
    protected function setFields($entity, $entityClass, $data, $ignoredAttributes = array())
    {
        $fieldMappings = $this->getFieldMappings($entityClass);
        //
        foreach ($fieldMappings as $fieldName => $fieldMapping) {
            if (!in_array($fieldName, $ignoredAttributes)) {
                $nullable = $fieldMapping['nullable'];

                //the field has been sent
                if (array_key_exists($fieldName, $data)) {
                    $originalValue = $data[$fieldName];

                    $fieldType = $fieldMapping['type'];

                    if ($fieldType === 'time') {
                        $value = $this->revertTimeValue($originalValue);
                    } else {
                        $value = $originalValue;
                    }


                    if (!$nullable) {
                        if ($value === null) {
                            throw new \Exception('The value NULL for the field ['.$fieldName.'] is not allowed; entity ['.$entityClass.']');
                        }
                    }

                    $method = 'set'.ucfirst($fieldName);

                    if (!method_exists($entity, $method)) {
                        throw new MethodNotFoundException('The entity '.$entityClass.' requires to the '.$method.' method');
                    }
                    //set The value
                    call_user_func(array($entity, $method), $value);
                }
            }
        }

        return $entity;
    }

    /**
     *
     * @param type $originalValue
     *
     * @return Datetime
     * @throws \Exception
     */
    protected function revertTimeValue($originalValue)
    {
        $timeFormat = 'H:i:s';
        $value = \DateTime::createFromFormat($timeFormat, $originalValue);

        if ($value === false) {
            throw new \Exception('The value ['.$originalValue.'] for the field ['.$fieldName.'] is not allowed; entity ['.$entityClass.']: Expected format ['.$timeFormat.']');
        }

        return $value;
    }

    /**
     *
     * @param string $entityClass
     * @param array $data
     * @return object The entity
     */
    protected function updateEntity($entityClass, $entity, $data)
    {
        $meta = $this->getMetadata($entityClass);

        //
        $this->setFields($entity, $entityClass, $data);
        $this->setAssociations($entity, $entityClass, $data);

        return $entity;
    }

    /**
     *
     * @param unknown $entity
     *
     * @throws \Exception
     */
    protected function deleteEntity($entity)
    {
        throw new \Exception('The delete action for the api generator is not yet available');
    }

    /**
     * Get the class linked to the entity alias
     *
     * @param string $entityAlias
     * @return type
     */
    protected function getClassByEntityAlias($entityAlias)
    {
        return $this->entityRights[$entityAlias]['class'];
    }
}
