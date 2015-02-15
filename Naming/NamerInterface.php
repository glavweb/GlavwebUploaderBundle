<?php

namespace Glavweb\UploaderBundle\Naming;

use Glavweb\UploaderBundle\File\FileInterface;

/**
 * Class NamerInterface
 * @package Glavweb\UploaderBundle\Naming
 */
interface NamerInterface
{
    /**
     * Name a given file and return the name
     *
     * @param  FileInterface $file
     * @return string
     */
    public function name(FileInterface $file);
}
