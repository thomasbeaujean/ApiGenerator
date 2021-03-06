<?php

namespace tbn\ApiGeneratorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('api_generator');

        $rootNode
            ->children()
                ->arrayNode('default')
                    ->children()
                        ->booleanNode('create')->defaultFalse()->end()
                        ->booleanNode('update')->defaultFalse()->end()
                        ->booleanNode('delete')->defaultFalse()->end()
                        ->booleanNode('get_one')->defaultFalse()->end()
                        ->booleanNode('get_one_deep')->defaultFalse()->end()
                        ->booleanNode('get_all')->defaultFalse()->end()
                        ->booleanNode('get_all_deep')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('entity')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')->isRequired()->end()
                            ->booleanNode('create')->end()
                            ->booleanNode('update')->end()
                            ->booleanNode('delete')->end()
                            ->booleanNode('get_one')->end()
                            ->booleanNode('get_one_deep')->end()
                            ->booleanNode('get_all')->end()
                            ->booleanNode('get_all_deep')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
