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
 * ref: tbn.api_generator.service.authorization_service
 *
 */
class AuthorizationService
{
    protected $allRights;
    protected $entityRights;
    protected $specifiedEntities;

    /**
     * Constructor
     *
     * @param array $allRights
     * @param array $entityRights
     * @param array $specifiedEntities
     */
    public function __construct($allRights, $entityRights, $specifiedEntities)
    {
        $this->allRights = $allRights;
        $this->entityRights = $entityRights;
        $this->specifiedEntities = $specifiedEntities;
    }

    /**
     * Is the action for the entity class allowed
     * @param string $entityClass
     * @param string $action
     * @return boolean
     */
    public function isEntityClassAllowedForRequest($entityClass, $action)
    {
        $isAllowed = false;

        if ($this->isEntityClassSpecified($entityClass)) {
            $isAllowed = $this->isEntityClassAllowedForRequestForEntity($entityClass, $action);
        } else {
            $isAllowed = $this->isEntityClassAllowedForRequestForAll($action);
        }

        return $isAllowed;
    }

    /**
     * Is the action for any entity class allowed
     *
     * @param string $action
     * @return boolean
     */
    protected function isEntityClassAllowedForRequestForAll($action)
    {
        $isAllowed = false;

        if ($this->allRights[$action]) {
            $isAllowed = true;
        }

        return $isAllowed;
    }

    /**
     * Is the action for any entity class allowed
     *
     * @param string $entityClass
     * @return boolean
     */
    protected function isEntityClassSpecified($entityClass)
    {
        $isSpecified = false;

        if (in_array($entityClass, $this->specifiedEntities)) {
            $isSpecified = true;
        }

        return $isSpecified;
    }

    /**
     * Is the action for any entity class allowed
     *
     * @param string $entityClass
     * @param string $action
     *
     * @return boolean
     */
    protected function isEntityClassAllowedForRequestForEntity($entityClass, $action)
    {
        $isAllowed = false;

        if ($this->entityRights[$entityClass][$action]) {
            $isAllowed = true;
        }

        return $isAllowed;
    }

    /**
     *
     * @param string $itemNamespace
     * @param string $action
     * @throws \Exception
     */
    public function checkItemNamespaceAction($itemNamespace, $action)
    {
        if ($this->isEntityClassAllowedForRequest($itemNamespace, $action) === false) {
            throw new \Exception('The entity ['.$itemNamespace.'] is not allowed by the api generator for '.$action);
        }
    }
}
