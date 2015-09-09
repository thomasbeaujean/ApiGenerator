<?php

namespace tbn\ApiGeneratorBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     *
     * @return type
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
