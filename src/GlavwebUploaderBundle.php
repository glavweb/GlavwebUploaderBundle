<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Class GlavwebUploaderBundle.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class GlavwebUploaderBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__.'/../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        $configurator->import(__DIR__.'/../config/services.yaml');

        if (!empty($config['mappings_defaults'])) {
            $config = $this->applyMappingsDefaults($config);
        }

        $container->setParameter('glavweb_uploader.config', $config);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function applyMappingsDefaults(array $config): array
    {
        $defaults = $config['mappings_defaults'];
        $mappings = &$config['mappings'];

        foreach ($mappings as &$contextConfig) {
            foreach ($contextConfig as $key => $value) {
                if (([] === $value) || null === $value) {
                    $contextConfig[$key] = $defaults[$key];
                }
            }

            foreach ($defaults as $defaultKey => $defaultValue) {
                if (!isset($contextConfig[$defaultKey])) {
                    $contextConfig[$defaultKey] = $defaultValue;
                }
            }
        }

        return $config;
    }
}
