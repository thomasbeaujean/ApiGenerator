<?php

namespace tbn\ApiGeneratorBundle\Services;

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
}
