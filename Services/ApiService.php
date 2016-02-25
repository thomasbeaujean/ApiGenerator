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
    protected $entityService = null;
    protected $retrieveService = null;
    protected $converterService = null;

    /**
     *
     * @param array                $entityRights
     * @param AuthorizationService $authorizationService
     * @param type                 $doctrine
     * @param type                 $validator
     * @param EntityService        $entityService
     * @param RetrieveService      $retrieveService
     * @param ConverterService     $converterService
     */
    public function __construct(array $entityRights, AuthorizationService $authorizationService, $doctrine, $validator, EntityService $entityService, RetrieveService $retrieveService, ConverterService $converterService)
    {
        $this->authorizationService = $authorizationService;
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->entityRights = $entityRights;
        $this->entityService = $entityService;
        $this->retrieveService = $retrieveService;
        $this->converterService = $converterService;
    }

    /**
     *
     * @param Request $request
     * @param string  $entityAlias
     * @return \tbn\ApiGeneratorBundle\Services\NULL[][]
     */
    public function handleAction(Request $request, $entityAlias)
    {
        $data = $request->request->all();

        if (count($data) === 0) {
            throw new \LogicException('No data were prodived');
        }

        $dataEntities = $this->getEntitiesByData($entityAlias, $data);

        $dataToCreate = $dataEntities['create'];
        $dataToUpdate = $dataEntities['update'];
        $dataToDelete = $dataEntities['delete'];

        unset($dataEntities);

        $toCreate = [];
        $toUpdate = [];

        foreach ($dataToCreate as $entityData) {
            $toCreate[] = $this->createEntity($entityAlias, $entityData);
        }

        foreach ($dataToUpdate as $entityData) {
            $entity = $this->retrieveService->retrieveEntity($entityAlias, $entityData);
            $toUpdate[] = $this->updateEntity($entityAlias, $entity, $entityData);
        }

        //validate assertions
        $this->validateEntities($toCreate);
        //validate assertions
        $this->validateEntities($toUpdate);

        //persist before check authorization
        //because the authorization uses doctrine to know if we create update or delete
        $this->persistEntities($toCreate);
        $this->persistEntities($toUpdate);

        $entities = array_merge($toCreate, $toUpdate);

        $this->checkEntitiesAuthorization($entities, $entityAlias);

        //finally flush entities
        $em = $this->doctrine->getManager();
        $em->flush($entities);

        return $entities;
    }

    /**
     *
     * @param string $entityAlias
     * @param array  $data
     *
     * @return NULL
     */
    public function getEntitiesByData($entityAlias, array $data)
    {
        $entityClass = $this->entityService->getClassByEntityAlias($entityAlias);

        $toCreate = [];
        $toUpdate = [];
        $toDelete = [];

        $entityClassIdentifiers = $this->entityService->getIdentifiers($entityClass);

        $hasGenerator = $this->entityService->hasGenerator($entityClass);

        foreach ($data as $row) {
            $containsIdentifier = true;

            foreach ($entityClassIdentifiers as $entityClassIdentifier) {
                if (!array_key_exists($entityClassIdentifier, $row)) {
                    $containsIdentifier = false;
                } else {
                    if ($row[$entityClassIdentifier] === null) {
                        $containsIdentifier = false;
                    }
                }
            }

            //new entity
            if ($containsIdentifier === false) {
                if ($hasGenerator === false) {
                    throw new \LogicException('The entity ['.$entityClass.'] does not contains any identifier and does not have any ID generator.');
                }
                //there is no identifier
                //so the user want to create a new entity using the generator
                $toCreate[] = $row;
            } else {
                if (array_key_exists('__delete', $row)) {
                    $toDelete[] = $row;
                } else {
                    $entity = $this->retrieveService->retrieveEntityByClass($entityClass, $row);

                    //no entity found, so it is a creation
                    if ($entity === null) {
                        //the id must not be provided due to the generator
                        if ($hasGenerator === true) {
                            throw new \LogicException('The entity ['.$entityClass.'] does contains an identifier and have any ID generator, it is not compatible.');
                        }

                        $toCreate[] = $row;
                    } else {
                        $toUpdate[] = $row;
                    }
                }
            }
        }

        return [
            'create' => $toCreate,
            'update' => $toUpdate,
            'delete' => $toDelete,
        ];
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
                throw new \LogicException(print_r($errorList, true));
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
     * @param string $entityAlias The alias of the entity
     * @param object $data
     */
    protected function createEntity($entityAlias, $data)
    {
        $entityClass = $this->entityService->getClassByEntityAlias($entityAlias);

        return $this->createEntityByClass($entityClass, $data);
    }

    /**
     *
     * @param string $entityClass The class of the entity
     * @param object $data
     */
    protected function createEntityByClass($entityClass, $data)
    {
        $entity = new $entityClass();

        $identifiers = $this->entityService->getIdentifiers($entityClass);
        $meta = $this->entityService->getMetadata($entityClass);

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
        $associationMappings = $this->entityService->getAssociationMappings($entityClass);
        //
        foreach ($associationMappings as $associationName => $associationMapping) {
            if (!in_array($associationName, $ignoredAttributes)) {
                //the field has been sent
                if (array_key_exists($associationName, $data)) {
                    $isOwningSide = $associationMapping['isOwningSide'];

                    //the targeted entity
                    $targetEntityClass = $associationMapping['targetEntity'];

                    //the values
                    $value = $data[$associationName];

                    //the entity owns the association, so it is linked to ONE another entity
                    if ($isOwningSide) {
                        $associatedEntity = $this->getToOneAssociatedEntity($targetEntityClass, $associationMapping, $value);
                    } else {
                        //get the array collection of entities
                        $associatedEntity = $this->getToManyAssocietedEntities($targetEntityClass, $value);
                    }

                    $method = 'set'.ucfirst($associationName);

                    if (!method_exists($entity, $method)) {
                        throw new MethodNotFoundException('The entity '.$entityClass.' requires to the '.$method.' method');
                    }

                    //set The value
                    call_user_func(array($entity, $method), $associatedEntity);
                }
            }
        }

        return $entity;
    }

    /**
     *
     * @param type $targetEntityClass
     * @param type $values
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getToManyAssocietedEntities($targetEntityClass, $values)
    {
        $associatedEntities = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($values as $value) {
            $associatedEntity = $this->getAssociatedEntity($targetEntityClass, $value);
            $associatedEntities->add($associatedEntity);
        }

        return $associatedEntities;
    }

    /**
     *
     * @param type $targetEntityClass
     * @param type $associationMapping
     * @param type $value
     * @return type
     * @throws \Exception
     */
    protected function getToOneAssociatedEntity($targetEntityClass, $associationMapping, $value)
    {
        $nullable = false;
        $joinColumns = $associationMapping['joinColumns'];

        //parse join columns to check that they are all not nullable
        foreach ($joinColumns as $joinColumn) {
            if ($joinColumn['nullable'] === true) {
                $nullable = true;
            }
        }

        if (!$nullable) {
            if ($value === null) {
                throw new \LogicException('The value NULL for the field ['.$associationName.'] is not allowed; entity ['.$entityClass.']');
            }
        }

        return $this->getAssociatedEntity($targetEntityClass, $value);
    }

    /**
     *
     * @return Entity
     */
    protected function getAssociatedEntity($targetEntityClass, $value)
    {
        if ($value !== null) {
            //Get the associated entity
            $associatedEntity = $this->retrieveService->retrieveEntityByClass($targetEntityClass, $value);

            //no entity were found
            if ($associatedEntity === null) {
                $associatedEntity = $this->createEntityByClass($targetEntityClass, $value);
            } else {
                //we update the associated entity
                $associatedEntity = $this->updateEntityByClass($targetEntityClass, $associatedEntity, $value);
            }
        } else {
            $associatedEntity = null;
        }

        return $associatedEntity;
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
        $fieldMappings = $this->entityService->getFieldMappings($entityClass);
        //
        foreach ($fieldMappings as $fieldName => $fieldMapping) {
            if (!in_array($fieldName, $ignoredAttributes)) {
                $nullable = $fieldMapping['nullable'];

                //the field has been sent
                if (array_key_exists($fieldName, $data)) {
                    $originalValue = $data[$fieldName];

                    $fieldType = $fieldMapping['type'];
                    $value = $this->converterService->revert($fieldMapping['type'], $originalValue);

                    if (!$nullable) {
                        if ($value === null) {
                            throw new \LogicException('The value NULL for the field ['.$fieldName.'] is not allowed; entity ['.$entityClass.']');
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
            throw new \LogicException('The value ['.$originalValue.'] for the field ['.$fieldName.'] is not allowed; entity ['.$entityClass.']: Expected format ['.$timeFormat.']');
        }

        return $value;
    }

    /**
     *
     * @param string $entityAlias
     * @param object $entity
     * @param array  $data
     * @return object The entity
     */
    protected function updateEntity($entityAlias, $entity, $data)
    {
        $entityClass = $this->entityService->getClassByEntityAlias($entityAlias);

        return $this->updateEntityByClass($entityClass, $entity, $data);
    }

        /**
     *
     * @param string $entityClass
     * @param object $entity
     * @param array  $data
     * @return object The entity
     */
    protected function updateEntityByClass($entityClass, $entity, $data)
    {
        $this->entityService->getMetadata($entityClass);

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
        throw new \LogicException('The delete action for the api generator is not yet available');
    }
}
