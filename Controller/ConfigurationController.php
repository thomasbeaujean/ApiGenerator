<?php

namespace tbn\ApiGeneratorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use tbn\JsonAnnotationBundle\Configuration\Json;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 *
 * @author Thomas BEAUJEAN
 *
 */
class ConfigurationController extends Controller
{
    /**
     * @Route("/apigenerator-configuration")
     * @Template
     */
    public function indexAction()
    {
        $apiService = $this->get('tbn.api_generator.service.api_service');
        $enabledEntities = $apiService->getEntitiesEnabled();

        return array('availableEntities' => $enabledEntities);
    }

    /**
     *
     * @return unknown[]
     */
    public function checkEntitiesAction()
    {
        $apiService = $this->get('tbn.api_generator.service.api_service');
        $enabledEntities = $apiService->getEntitiesEnabled();

        return array('availableEntities' => $enabledEntities);
    }
}
