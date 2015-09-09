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
    protected $entityRights;

    /**
     * Constructor
     *
     * @param array $entityRights
     */
    public function __construct($entityRights)
    {
        $this->entityRights = $entityRights;
    }

    /**
     * Is the action for the entity class allowed
     *
     * @param string $entityAlias
     * @param string $action
     *
     * @return boolean
     */
    public function isEntityAliasAllowedForRequest($entityAlias, $action)
    {
        $isAllowed = false;

        if ($this->isEntityAliasSpecified($entityAlias)) {
            if ($this->entityRights[$entityAlias][$action]) {
                $isAllowed = true;
            }
        } else {
            zdebug($this->entityRights);
            throw new \Exception('The entity alias ['.$entityAlias.'] is not configured for the api-generator bundle');
        }

        return $isAllowed;
    }

    /**
     *
     * @param string $entityAlias
     * @param string $action
     * @throws \Exception
     */
    public function checkEntityAliasAction($entityAlias, $action)
    {
        if ($this->isEntityAliasAllowedForRequest($entityAlias, $action) === false) {
            throw new \Exception('The entity alias ['.$entityAlias.'] is not allowed by the api generator for '.$action);
        }
    }

    /**
     * Is the action for any entity alias allowed
     *
     * @param string $entityAlias
     * @return boolean
     */
    protected function isEntityAliasSpecified($entityAlias)
    {
        $isSpecified = false;

        if (isset($this->entityRights[$entityAlias])) {
            $isSpecified = true;
        }

        return $isSpecified;
    }
}
