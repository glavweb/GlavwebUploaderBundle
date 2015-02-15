<?php

namespace Glavweb\UploaderBundle\Storage;

use Glavweb\UploaderBundle\File\FileInterface;

interface StorageInterface
{
    /**
     * Uploads a File instance to the configured storage.
     *
     * @param        $file
     * @param string $name
     * @param string $directory
     * @return FileInterface
     */
    public function upload(FileInterface $file, $name, $directory);
}
