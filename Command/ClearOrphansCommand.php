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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class ClearOrphansCommand
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ClearOrphansCommand extends Command implements ContainerAwareInterface
{
    protected static $defaultName = 'glavweb:uploader:clear-orphans';

    use ContainerAwareTrait;

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setDescription('Clear orphaned uploads according to the "lifetime" you defined in your configuration.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uploaderManager = $this->container->get('glavweb_uploader.uploader_manager');
        $uploaderManager->clearOrphanage();
    }
}
