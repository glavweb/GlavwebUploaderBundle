<?php

namespace Glavweb\UploaderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearOrphansCommand
 * @package Glavweb\UploaderBundle\Command
 */
class ClearOrphansCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('glavweb:uploader:clear-orphans');
        $this->setDescription('Clear orphaned uploads according to the "lifetime" you defined in your configuration.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uploaderManager = $this->getContainer()->get('glavweb_uploader.uploader_manager');
        $uploaderManager->clearOrphanage();
    }
}
