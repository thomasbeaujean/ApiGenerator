<?php

namespace tbn\ApiGeneratorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use tbn\JsonAnnotationBundle\Configuration\Json;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/apigenerator")
 */
class ApiController extends Controller
{
    /**
     * @Route("/{itemNamespace}",name="api_generator_save")
     *
     * @Method({"POST"})
     * @Json
     */
    public function handleAction(Request $request, $itemNamespace)
    {
        $apiService = $this->get('tbn.api_generator.service.api_service');

        $entities = $apiService->handleAction($request, $this->decamelize($itemNamespace));

        $normalizer = $this->get('get_set_foreign_normalizer');

        $data = $normalizer->normalize($entities);

        return $data;
    }

    /**
     * @Route("/{itemNamespace}",name="api_generator_all")
     *
     * @Method({"GET"})
     *
     * @Json
     */
    public function getAllAction(Request $request, $itemNamespace)
    {
        $this->checkItemNamespaceAction($itemNamespace, 'get_all');

        $apiService = $this->get('tbn.api_generator.service.api_service');

        $entityClass = $this->decamelize($itemNamespace);
        $entity = $apiService->retrieveAllEntities($entityClass);
        $normalizer = $this->get('get_set_foreign_normalizer');

        $data = $normalizer->normalize($entity);

        return $data;
    }

    /**
     * @Route("/{itemNamespace}/deep",name="api_generator_all_deep")
     *
     * @Method({"GET"})
     *
     * @Json
     */
    public function getAllDeepAction(Request $request, $itemNamespace)
    {
        $this->checkItemNamespaceAction($itemNamespace, 'get_all_deep');

        $apiService = $this->get('tbn.api_generator.service.api_service');

        $entityClass = $this->decamelize($itemNamespace);
        $entity = $apiService->retrieveAllEntities($entityClass);
        $normalizer = $this->get('get_set_foreign_normalizer');
        $normalizer->setWatchDogLimit(9000);
        $normalizer->setDeepNormalization(true);

        $data = $normalizer->normalize($entity);

        return $data;
    }

    /**
     * @Route("/{itemNamespace}/{id}",name="api_generator_one")
     *
     * @Method({"GET"})
     *
     * @Json
     */
    public function getByIdAction(Request $request, $itemNamespace, $id)
    {
        $this->checkItemNamespaceAction($itemNamespace, 'get_one');

        $apiService = $this->get('tbn.api_generator.service.api_service');

        $data['id'] = $id;
        $entityClass = $this->decamelize($itemNamespace);
        $entity = $apiService->retrieveEntity($entityClass, $data);
        $normalizer = $this->get('get_set_foreign_normalizer');

        $data = $normalizer->normalize($entity);

        return $data;
    }

    /**
     * @Route("/{itemNamespace}/{id}/deep",name="api_generator_one_deep")
     *
     * @Method({"GET"})
     *
     * @Json
     */
    public function getByIdDeepAction(Request $request, $itemNamespace, $id)
    {
        $this->checkItemNamespaceAction($itemNamespace, 'get_one_deep');

        $apiService = $this->get('tbn.api_generator.service.api_service');

        $data['id'] = $id;
        $entityClass = $this->decamelize($itemNamespace);
        $entity = $apiService->retrieveEntity($entityClass, $data);
        $normalizer = $this->get('get_set_foreign_normalizer');
        $normalizer->setDeepNormalization(true);

        $data = $normalizer->normalize($entity);

        return $data;
    }

    /**
     * @Route("/{itemNamespace}/{id}",name="api_generator_delete")
     *
     * @Method({"DELETE"})
     *
     * @Json
     */
    public function deleteByIdAction(Request $request, $itemNamespace, $id)
    {
        $this->checkItemNamespaceAction($itemNamespace, 'delete');

        $apiService = $this->get('tbn.api_generator.service.api_service');

        $data['id'] = $id;
        $entityClass = $this->decamelize($itemNamespace);
        $entity = $apiService->retrieveEntity($entityClass, $data);

        $em = $this->get('doctrine')->getManager();
        $em->remove($entity);
        $em->flush($entity);

        return array();
    }

    /**
     *
     * @param string $itemNamespace
     * @return string
     */
    protected function decamelize($itemNamespace)
    {
        $startWithLowerCase = false;
        if (substr($itemNamespace, 0, 1) === '-') {
            $startWithLowerCase = true;
        }

        $words = explode('--', $itemNamespace);
        $camelizedWords = array();

        foreach ($words as $word) {
            $decamelizedSubword = '';
            $subwords = explode('-', $word);

            foreach ($subwords as $subword) {
                $decamelizedSubword .= ucfirst($subword);
            }

            $camelizedWords[] = $decamelizedSubword;
        }

        $decamelized = implode('\\', $camelizedWords);

        if ($startWithLowerCase) {
            $decamelized = lcfirst($decamelized);
        }

        return $decamelized;
    }

    /**
     *
     * @param string $itemNamespace
     * @param string $action
     * @throws \Exception
     */
    protected function checkItemNamespaceAction($itemNamespace, $action)
    {
        $authorizationService = $this->get('tbn.api_generator.service.authorization_service');
        $authorizationService->checkItemNamespaceAction($itemNamespace, $action);
    }
}
