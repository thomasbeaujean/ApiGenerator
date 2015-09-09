<?php

namespace tbn\ApiGeneratorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ApiGeneratorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $entitiesConfiguration = [];

        foreach ($config['entity'] as $entityAlias => $entityConfiguration) {
            $entitiesConfiguration[$entityAlias] = $entityConfiguration;

            //set the entity alias in the array
            $entitiesConfiguration[$entityAlias]['alias'] = $entityAlias;

            $defaultActions = [
                'create',
                'update',
                'delete',
                'get_one',
                'get_one_deep',
                'get_all',
                'get_all_deep',
            ];

            //use default values
            foreach ($defaultActions as $defaultAction) {
                if (!isset($entitiesConfiguration[$entityAlias][$defaultAction])) {
                    $entitiesConfiguration[$entityAlias][$defaultAction] = $config['default'][$defaultAction];
                }
            }
        }

        $container->setParameter('tbn.api_generator.entities', $entitiesConfiguration);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
