<?php

namespace tbn\ApiGeneratorBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use tbn\JsonAnnotationBundle\Configuration\Json;

/**
 * @Route("/apigenerator")
 */
class ApiController extends Controller
{
    /**
     * @Route("/{entityAlias}",name="api_generator_save")
     *
     * @Method({"POST"})
     * @Json
     *
     * @param Request $request
     * @param string  $entityAlias
     * @return type
     */
    public function handleAction(Request $request, $entityAlias)
    {
        $apiService = $this->get('tbn.api_generator.service.api_service');

        $entities = $apiService->handleAction($request, $entityAlias);

        $normalizer = $this->get('get_set_foreign_normalizer');

        $data = $normalizer->normalize($entities);

        return $data;
    }

    /**
     * @Route("/{entityAlias}",name="api_generator_all")
     *
     * @Method({"GET"})
     *
     * @Json
     *
     * @param string $entityAlias
     *
     * @return type
     */
    public function getAllAction($entityAlias)
    {
        $this->checkEntityAliasAction($entityAlias, 'get_all');

        $apiService = $this->get('tbn.api_generator.service.retrieve_service');

        $entity = $apiService->retrieveAllEntities($entityAlias);
        $normalizer = $this->get('get_set_foreign_normalizer');

        $data = $normalizer->normalize($entity);

        return $data;
    }

    /**
     * @Route("/{entityAlias}/deep",name="api_generator_all_deep")
     *
     * @Method({"GET"})
     *
     * @Json
     *
     * @param type $entityAlias
     *
     * @return type
     */
    public function getAllDeepAction($entityAlias)
    {
        $this->checkEntityAliasAction($entityAlias, 'get_all_deep');

        $apiService = $this->get('tbn.api_generator.service.retrieve_service');

        $entity = $apiService->retrieveAllEntities($entityAlias);
        $normalizer = $this->get('get_set_foreign_normalizer');

        $data = $normalizer->normalize($entity, true);

        return $data;
    }

    /**
     * @Route("/{entityAlias}/{id}",name="api_generator_one")
     *
     * @Method({"GET"})
     *
     * @Json
     *
     * @param string $entityAlias
     * @param int    $id
     *
     * @return type
     */
    public function getByIdAction($entityAlias, $id)
    {
        $this->checkEntityAliasAction($entityAlias, 'get_one');

        $apiService = $this->get('tbn.api_generator.service.retrieve_service');

        $data['id'] = $id;
        $entity = $apiService->retrieveEntity($entityAlias, $data);
        $normalizer = $this->get('get_set_foreign_normalizer');

        $data = $normalizer->normalize($entity);

        return $data;
    }

    /**
     * @Route("/{entityAlias}/{id}/deep",name="api_generator_one_deep")
     *
     * @Method({"GET"})
     *
     * @Json
     *
     * @param string $entityAlias
     * @param int    $id
     *
     * @return type
     */
    public function getByIdDeepAction($entityAlias, $id)
    {
        $this->checkEntityAliasAction($entityAlias, 'get_one_deep');

        $apiService = $this->get('tbn.api_generator.service.retrieve_service');

        $data['id'] = $id;
        $entity = $apiService->retrieveEntity($entityAlias, $data);
        $normalizer = $this->get('get_set_foreign_normalizer');

        $data = $normalizer->normalize($entity, true);

        return $data;
    }

    /**
     * @Route("/{entityAlias}/{id}",name="api_generator_delete")
     *
     * @Method({"DELETE"})
     *
     * @Json
     *
     * @param string $entityAlias
     * @param int    $id
     * @return type
     */
    public function deleteByIdAction($entityAlias, $id)
    {
        $this->checkEntityAliasAction($entityAlias, 'delete');

        $apiService = $this->get('tbn.api_generator.service.api_service');

        $data['id'] = $id;
        $entity = $apiService->retrieveEntity($entityAlias, $data);

        $em = $this->get('doctrine')->getManager();
        $em->remove($entity);
        $em->flush($entity);

        return array();
    }

    /**
     *
     * @param string $entityAlias
     * @param string $action
     * @throws \Exception
     */
    protected function checkEntityAliasAction($entityAlias, $action)
    {
        $authorizationService = $this->get('tbn.api_generator.service.authorization_service');
        $authorizationService->checkEntityAliasAction($entityAlias, $action);
    }
}
