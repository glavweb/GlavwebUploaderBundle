<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\File;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FilesystemFile
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FilesystemFile extends UploadedFile implements FileInterface
{
    /**
     * @param File $file
     * @param string|null $originalName
     */
    public function __construct(File $file, string $originalName = null)
    {
        if ($file instanceof UploadedFile) {
            parent::__construct(
                $file->getPathname(),
                $originalName ?? $file->getClientOriginalName(),
                $file->getClientMimeType(),
                $file->getError(),
                true
            );

        } else {
            parent::__construct(
                $file->getPathname(),
                $originalName ?? $file->getBasename(),
                $file->getMimeType(),
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
