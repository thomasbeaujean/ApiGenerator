<?php

namespace tbn\ApiGeneratorBundle\Services;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use tbn\DoctrineRelationVisualizerBundle\Entity\Entity;
use tbn\DoctrineRelationVisualizerBundle\Entity\AssociationEntity;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;
use tbn\GetSetForeignNormalizerBundle\Component\Serializer\Normalizer\GetSetPrimaryMethodNormalizer;
use Symfony\Component\Filesystem\Filesystem;
use tbn\DoctrineRelationVisualizerBundle\Entity\Field;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityNotFoundException;

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

    /**
     *
     * @param AuthorizationService $authorizationService
     * @param unknown $doctrine
     * @param unknown $validator
     */
    public function __construct(AuthorizationService $authorizationService, $doctrine, $validator)
    {
        $this->authorizationService = $authorizationService;
        $this->doctrine = $doctrine;
        $this->validator = $validator;
    }

    /**
     *
     * @param Request $request
     * @param unknown $itemNamespace
     * @return \tbn\ApiGeneratorBundle\Services\NULL[][]
     */
    public function handleAction(Request $request, $itemNamespace)
    {
        $parameters = $request->request->all();

        if (count($parameters) === 0) {
            throw new \Exception('No data was prodived');
        }

        $entities = array();

        foreach ($parameters as $data) {
            $entity = $this->getEntityByData($itemNamespace, $data);
            $entities[] = $entity;
        }

        //validate assertions
        $this->validateEntities($entities);

        //persist before check authorization
        //because the authorization uses doctrine to know if we create update or delete
        $this->persistEntities($entities);
        $this->checkEntitiesAuthorization($entities, $itemNamespace);

        //finally flush entities
        $em = $this->doctrine->getManager();
        $em->flush();

        return $entities;
    }

    /**
     * Check that the action is allowed
     * @param array $entities
     * @param string $itemNamespace
     */
    protected function checkEntitiesAuthorization($entities, $itemNamespace)
    {
        $em = $this->doctrine->getManager();
        $uow = $em->getUnitOfWork();

        $authorizationService = $this->authorizationService;

        foreach ($entities as $entity) {
            if ($uow->isScheduledForInsert($entity)) {
                $authorizationService->checkItemNamespaceAction($itemNamespace, 'create');
            }
            if ($uow->isScheduledForUpdate($entity)) {
                $authorizationService->checkItemNamespaceAction($itemNamespace, 'update');
            }
            if ($uow->isScheduledForDelete($entity)) {
                $authorizationService->checkItemNamespaceAction($itemNamespace, 'delete');
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
     * @param string $itemNamespace
     * @param array $data
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
                //update
                $entity = $this->updateEntity($entityClass, $data);
            } else {
                $this->deleteEntity($data);
            }
        }

        return $entity;
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
     * @param String $itemNamespace
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
     *
     * @param string $itemNamespace
     * @param object $entity
     */
    protected function createEntity($entityClass, $data)
    {
        $entityClassname = $this->getEntityClassname($entityClass);

        $entity = new $entityClassname;

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
                                throw new \Exception('The value NULL for the field ['.$associationName.'] is not allowed; enity ['.$entityClass.']');
                            }
                        }

                        //Get the associated entity
                        $associatedEntity = $this->getEntityByData($targetEntityClass, $value);

                        $method = 'set'.ucfirst($associationName);

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
     * @param array $data
     * @param array $ignoredAttributes
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
                    $value = $data[$fieldName];

                    if (!$nullable) {
                        if ($value === null) {
                            throw new \Exception('The value NULL for the field ['.$fieldName.'] is not allowed; entity ['.$entityClass.']');
                        }
                    }

                    $method = 'set'.ucfirst($fieldName);

                    //set The value
                    call_user_method($method, $entity, $value);
                }
            }
        }

        return $entity;
    }

    /**
     *
     * @param string $entityClass
     * @param array $data
     * @return object The entity
     */
    protected function updateEntity($entityClass, $data)
    {
        $entity = $this->retrieveEntity($entityClass, $data);

        $meta = $this->getMetadata($entityClass);

        $generatorType = $meta->generatorType;

        //
        $this->setFields($entity, $entityClass, $data);
        $this->setAssociations($entity, $entityClass, $data);

        return $entity;
    }

    /**
     * Retrieve an entity by the identifier listed in the data
     *
     * @param string $entityClass
     * @param object $entity
     *
     * @throws Exception
     */
    public function retrieveEntity($entityClass, $data)
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

        if ($entity === null) {
            throw new \Exception('The entity ['.$entityClass.'] with the id ['.print_r($criteria, true).'] was not found.');
        }

        return $entity;
    }

    /**
     * Retrieve an entity by the identifier listed in the data
     *
     * @param string $entityClass
     * @param object $entity
     *
     * @throws Exception
     */
    public function retrieveAllEntities($entityClass)
    {
        $doctrine = $this->doctrine;
        $em = $doctrine->getManager();
        $repository = $em->getRepository($entityClass);

        $entities = $repository->findAll();

        return $entities;
    }

    /**
     *
     * @param unknown $entity
     * @throws \Exception
     */
    protected function deleteEntity($entity)
    {
        throw new \Exception('The delete action for the api generator is not yet available');
    }

    /**
     * Get the list of entities that are enabled in the bundle
     *
     * @return array
     */
    public function getEntitiesEnabled()
    {
        $entities = array();

        $em = $this->doctrine->getManager();
        $meta = $em->getMetadataFactory()->getAllMetadata();

        foreach ($meta as $m) {

            $entities[] = $m->rootEntityName;
        }

        return $entities;
    }
}
