<?php

namespace Glavweb\UploaderBundle\Provider;

use Glavweb\UploaderBundle\File\FileInterface;

/**
 * Class ProviderFileInterface
 * @package Glavweb\UploaderBundle\Provider
 */
interface ProviderFileInterface extends ProviderInterface
{
    /**
     * @return FileInterface
     */
    public function getFile();
}