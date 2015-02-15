<?php

namespace Glavweb\UploaderBundle\File;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FilesystemFile
 * @package Glavweb\UploaderBundle\File
 */
class FilesystemFile extends UploadedFile implements FileInterface
{
    /**
     * @param File $file
     */
    public function __construct(File $file)
    {
        if ($file instanceof UploadedFile) {
            parent::__construct(
                $file->getPathname(),
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                $file->getClientSize(),
                $file->getError(),
                true
            );

        } else {
            parent::__construct(
                $file->getPathname(),
                $file->getBasename(),
                $file->getMimeType(),
                $file->getSize(),
                0,
                true
            );
        }
    }

    /**
     * @return mixed|string
     */
    public function getExtension()
    {
        return $this->getClientOriginalExtension();
    }
}
