<?php

namespace Glavweb\UploaderBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('glavweb_uploader');

        $rootNode
            ->children()
                ->arrayNode('orphanage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('lifetime')->defaultValue(604800)->end()
                        ->scalarNode('directory')->defaultNull()->end()
                    ->end()
                ->end()
                ->scalarNode('model_manager')->defaultValue('glavweb_uploader.model_manager.orm')->end()
                ->scalarNode('storage')->defaultValue('glavweb_uploader.storage.filesystem')->end()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('id')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->arrayNode('providers')
                                ->prototype('scalar')
                                    ->defaultValue(array())
                                ->end()
                            ->end()
                            ->scalarNode('route_prefix')->defaultValue('')->end()
                            ->arrayNode('allowed_mimetypes')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('disallowed_mimetypes')
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('error_handler')->defaultNull()->end()
                            ->scalarNode('max_size')
                                ->defaultValue(\PHP_INT_MAX)
                                ->info('Set max_size to -1 for gracefully downgrade this number to the systems max upload size.')
                            ->end()
                            ->scalarNode('max_files')
                                ->defaultValue(\PHP_INT_MAX)
                                ->info('Set max files.')
                            ->end()
                            ->booleanNode('use_orphanage')->defaultFalse()->end()
                            ->booleanNode('enable_progress')->defaultFalse()->end()
                            ->booleanNode('enable_cancelation')->defaultFalse()->end()
                            ->scalarNode('upload_directory')->end()
                            ->scalarNode('upload_directory_url')->end()
                            ->scalarNode('namer')->defaultValue('glavweb_uploader.namer.uniqid')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
