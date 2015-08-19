<?php

namespace tbn\ApiGeneratorBundle\Twig;

use  tbn\ApiGeneratorBundle\Services\AuthorizationService;

/**
 *
 * @author Thomas BEAUJEAN
 *
 */
class ApiGeneratorExtension extends \Twig_Extension
{
    protected $authorizationService = null;

    /**
     *
     * @param AuthorizationService $authorizationService
     */
    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Get the list of filters
     *
     * (non-PHPdoc)
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('apiGeneratorNormalize', array($this, 'decamelize')),
            new \Twig_SimpleFilter('apiGeneratorAllowed', array($this, 'isActionAllowed')),
        );
    }

    /**
     * Decamelize a string
     * The \\ are replaced by -- and the \ are replaced by a single -
     *
     * @param string $tring
     * @return string
     */
    public function decamelize($tring)
    {
        $words = explode('\\', $tring);
        $camelizedWords = array();

        foreach ($words as $word) {
            $decamelizedSubword = '';
            preg_match_all('/((?:^|[A-Z])[a-z]+)/', $word, $subwords);

            foreach ($subwords[0] as $index => $subword) {
                if ($index !== 0) {
                    $decamelizedSubword .= strtolower('-');
                }
                $decamelizedSubword .= strtolower($subword);
            }

            $camelizedWords[] = $decamelizedSubword;
        }

        $decamelized = implode('--', $camelizedWords);

        return $decamelized;
    }

    /**
     *
     * @param string $className
     * @param string $action
     *
     * @return boolean Is the action allowed
     */
    public function isActionAllowed($className, $action)
    {
        $isAllowed = $this->authorizationService->isEntityClassAllowedForRequest($className, $action);

        return $isAllowed;
    }

    /**
     * Get the name of the extension
     *
     * (non-PHPdoc)
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'api_generator_extension';
    }
}