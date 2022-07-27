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
        $treeBuilder = new TreeBuilder('glavweb_uploader');
        $rootNode = $treeBuilder->getRootNode();

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
                ->scalarNode('temp_directory')->defaultValue('%kernel.project_dir%/var/temp')
                    ->cannotBeEmpty()->end()
                ->arrayNode('chunk_upload')
                    ->children()
                        ->scalarNode('file_id_request_parameter')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('total_count_request_parameter')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('current_index_request_parameter')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('mappings_defaults')
                    ->addDefaultsIfNotSet()
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
                            ->info('Maximum file size in bytes. Set max_size to -1 for gracefully downgrade this number to the systems max upload size.')
                        ->end()
                        ->scalarNode('max_files')
                            ->defaultValue(\PHP_INT_MAX)
                            ->info('Set max files.')
                        ->end()
                        ->arrayNode('width')
                            ->info('Image width in pixels.')
                            ->beforeNormalization()
                                ->ifTrue(function($v) { return \is_int($v); })
                                ->then(function ($v) { return ['min' => $v, 'max' => $v]; })
                            ->end()
                            ->children()
                                ->integerNode('min')->end()
                                ->integerNode('max')->end()
                            ->end()
                        ->end()
                        ->arrayNode('height')
                            ->info('Image height in pixels.')
                            ->beforeNormalization()
                                ->ifTrue(function($v) { return \is_int($v); })
                                ->then(function ($v) { return ['min' => $v, 'max' => $v]; })
                            ->end()
                            ->children()
                                ->integerNode('min')->end()
                                ->integerNode('max')->end()
                            ->end()
                        ->end()
                        ->booleanNode('use_orphanage')->defaultFalse()->end()
                        ->scalarNode('upload_directory')->end()
                        ->scalarNode('upload_directory_url')->end()
                        ->scalarNode('namer')->defaultValue('glavweb_uploader.namer.uniqid')->end()
                        ->booleanNode('description_enabled')
                            ->info('Enables description field of media file model.')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('id')
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
                                ->info('Maximum file size in bytes. Set max_size to -1 for gracefully downgrade this number to the systems max upload size.')
                            ->end()
                            ->scalarNode('max_files')
                                ->info('Set max files.')
                            ->end()
                            ->arrayNode('width')
                                ->info('Image width in pixels.')
                                ->beforeNormalization()
                                    ->ifTrue(function($v) { return \is_int($v); })
                                    ->then(function ($v) { return ['min' => $v, 'max' => $v]; })
                                ->end()
                                ->children()
                                    ->integerNode('min')->end()
                                    ->integerNode('max')->end()
                                ->end()
                            ->end()
                            ->arrayNode('height')
                                ->info('Image height in pixels.')
                                ->beforeNormalization()
                                    ->ifTrue(function($v) { return \is_int($v); })
                                    ->then(function ($v) { return ['min' => $v, 'max' => $v]; })
                                ->end()
                                ->children()
                                    ->integerNode('min')->end()
                                    ->integerNode('max')->end()
                                ->end()
                            ->end()
                            ->booleanNode('use_orphanage')->end()
                            ->scalarNode('upload_directory')->end()
                            ->scalarNode('upload_directory_url')->end()
                            ->scalarNode('namer')->end()
                            ->booleanNode('description_enabled')
                                ->info('Enables description field of media file model.')
                                ->defaultTrue()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
