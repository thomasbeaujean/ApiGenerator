<?php

namespace tbn\ApiGeneratorBundle\Services;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 *
 * @author Thomas BEAUJEAN
 *
 * ref: tbn.api_generator.service.entity_service
 *
 */
class EntityService
{
    protected $doctrine = null;
    protected $entityRights = null;

    /**
     *
     * @param array $entityRights
     * @param type  $doctrine
     */
    public function __construct(array $entityRights, $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->entityRights = $entityRights;
    }

    /**
     * Get the class linked to the entity alias
     *
     * @param string $entityAlias
     * @return type
     */
    public function getClassByEntityAlias($entityAlias)
    {
        return $this->entityRights[$entityAlias]['class'];
    }

    /**
     *
     * @param String $entityClass
     * @return unknown
     */
    public function getMetadata($entityClass)
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
    public function getEntityClassname($entityClass)
    {
        $meta = $this->getMetadata($entityClass);

        return $meta->name;
    }

    /**
     * Get the identifiers
     *
     * @param String $entityClass
     *
     * @return array The identifiers
     *
     */
    public function getIdentifiers($entityClass)
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
    public function hasGenerator($entityClass)
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
     * Get the field mappings
     * @param String $entityClass
     *
     * @return array
     */
    public function getFieldMappings($entityClass)
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
    public function getAssociationMappings($entityClass)
    {
        $meta = $this->getMetadata($entityClass);

        return $meta->associationMappings;
    }
}
