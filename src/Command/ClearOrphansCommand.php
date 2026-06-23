<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Command;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Class ClearOrphansCommand.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
#[AsCommand(
    name: 'glavweb:uploader:clear-orphans',
    description: 'Clear orphaned uploads according to the "lifetime" you defined in your configuration.'
)]
readonly class ClearOrphansCommand
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(): int
    {
        $uploaderManager = $this->container->get('glavweb_uploader.uploader_manager');
        $uploaderManager->clearOrphanage();

        return 0;
    }
}
