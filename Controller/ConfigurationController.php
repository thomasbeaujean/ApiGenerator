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
        $enabledEntities = $this->getParameter('tbn.api_generator.entities');

        return array('availableEntities' => $enabledEntities);
    }
}
