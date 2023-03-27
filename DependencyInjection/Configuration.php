<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
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
                    ->end()
                ->end()
                ->scalarNode('model_manager')->defaultValue('glavweb_uploader.model_manager.orm')->end()
                ->scalarNode('base_url')->defaultValue('')->end()
                ->scalarNode('storage')->defaultValue('glavweb_uploader.storage.filesystem')->end()
                ->arrayNode('chunk_upload')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('file_id_request_parameter')->isRequired()->defaultValue('dzuuid')->end()
                        ->scalarNode('total_count_request_parameter')->isRequired()->defaultValue('dztotalchunkcount')->end()
                        ->scalarNode('current_index_request_parameter')->isRequired()->defaultValue('dzchunkindex')->end()
                        ->scalarNode('image_width_request_parameter')->isRequired()->defaultValue('image_width')->end()
                        ->scalarNode('image_height_request_parameter')->isRequired()->defaultValue('image_height')->end()
                        ->scalarNode('type_request_parameter')->isRequired()->defaultValue('type')->end()
                    ->end()
                ->end()
                ->arrayNode('mappings_defaults')
                    ->children()
                        ->arrayNode('providers')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('route_prefix')->defaultValue('')->end()
                        ->arrayNode('allowed_mimetypes')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('disallowed_mimetypes')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('max_size')
                            ->defaultValue(\PHP_INT_MAX)
                            ->info('Set max_size to -1 for gracefully downgrade this number to the systems max upload size.')
                        ->end()
                        ->scalarNode('max_files')
                            ->defaultValue(\PHP_INT_MAX)
                            ->info('Set max files.')
                        ->end()
                        ->booleanNode('use_orphanage')->defaultFalse()->end()
                        ->scalarNode('upload_directory')->end()
                        ->scalarNode('upload_directory_url')->end()
                        ->scalarNode('namer')->defaultValue('glavweb_uploader.namer.uniqid')->end()
                    ->end()
                ->end()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('id')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->arrayNode('providers')
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('route_prefix')->end()
                            ->arrayNode('allowed_mimetypes')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('disallowed_mimetypes')
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('max_size')
                                ->info('Set max_size to -1 for gracefully downgrade this number to the systems max upload size.')
                            ->end()
                            ->scalarNode('max_files')
                                ->info('Set max files.')
                            ->end()
                            ->booleanNode('use_orphanage')->end()
                            ->scalarNode('upload_directory')->end()
                            ->scalarNode('upload_directory_url')->end()
                            ->scalarNode('namer')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
