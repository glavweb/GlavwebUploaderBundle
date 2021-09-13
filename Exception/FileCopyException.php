<?php

namespace Glavweb\UploaderBundle\Exception;

use Glavweb\UploaderBundle\File\FileInterface;

/**
 * Class FileAlreadyExistsException
 *
 * @package Glavweb\UploaderBundle\Exception
 *
 * @author  Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class FileCopyException extends Exception
{
    /**
     * @param FileInterface $file
     * @param string        $path
     * @param string        $message
     */
    public function __construct(FileInterface $file, string $path, string $message)
    {
        parent::__construct("Can't create copy of file \"{$file->getPathname()}\" with new path \"$path\": $message");
    }
}