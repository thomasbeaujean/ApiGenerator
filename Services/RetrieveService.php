<?php

namespace tbn\ApiGeneratorBundle\Services;

/**
 *
 * @author Thomas BEAUJEAN
 *
 * ref: tbn.api_generator.service.retrieve_service
 *
 */
class RetrieveService
{
    protected $doctrine = null;
    protected $entityRights = null;
    protected $entityService = null;

    /**
     *
     * @param array         $entityRights
     * @param type          $doctrine
     * @param EntityService $entityService
     */
    public function __construct(array $entityRights, $doctrine, EntityService $entityService)
    {
        $this->doctrine = $doctrine;
        $this->entityRights = $entityRights;
        $this->entityService = $entityService;
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
        $entityClass = $this->entityService->getClassByEntityAlias($entityAlias);
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
        $entityClass = $this->entityService->getClassByEntityAlias($entityAlias);

        $doctrine = $this->doctrine;
        $em = $doctrine->getManager();
        $repository = $em->getRepository($entityClass);

        $entities = $repository->findAll();

        return $entities;
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

        $identifiers = $this->entityService->getIdentifiers($entityClass);

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
}
