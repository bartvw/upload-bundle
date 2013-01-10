<?php

namespace BVW\UploadBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bvw_upload');

        $rootNode->children()
          ->arrayNode('upload')
            ->addDefaultsIfNotSet()
            ->children()
              ->scalarNode('base_path')
                ->defaultValue('../web/upload/temp')
              ->end()
              ->scalarNode('base_url')
                ->defaultValue('/upload/temp')
              ->end()
            ->end()
          ->end()
          ->scalarNode('secret')->defaultValue('changeme')->end()
          ->arrayNode('formats')
            ->useAttributeAsKey('name')
            ->prototype('array')
              ->useAttributeAsKey('name')
              ->prototype('array')
                ->children()
                  ->scalarNode('width')->defaultValue(0)->end()
                  ->scalarNode('height')->defaultValue(0)->end()
                  ->scalarNode('mode')->defaultValue('fit')->end()
                  ->scalarNode('background_color')->defaultValue('ffffff')->end()
                ->end()
              ->end()
            ->end()
          ->end()

          ->scalarNode('storage')
            ->defaultValue('local')
            ->validate()
            ->ifNotInArray(array('s3', 'local'))
              ->thenInvalid('Invalid storage type %s')
            ->end()
          ->end()

          ->arrayNode('s3storage')
            ->children()
              ->scalarNode('key')->isRequired()->end()
              ->scalarNode('secret')->isRequired()->end()
              ->scalarNode('bucket')->isRequired()->end()
              ->scalarNode('region')->isRequired()->end()
            ->end()
          ->end()

          ->arrayNode('localstorage')
            ->addDefaultsIfNotSet()
            ->children()
              ->scalarNode('base_path')->defaultValue('../web/upload')->end()
              ->scalarNode('base_url')->defaultValue('/upload')
            ->end()
          ->end()
        ->end();


        return $treeBuilder;
    }
}
