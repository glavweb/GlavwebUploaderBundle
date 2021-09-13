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

use Glavweb\UploaderBundle\Exception\FileCopyException;
use Glavweb\UploaderBundle\Util\FileUtils;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
                $file->getClientSize(),
                $file->getError(),
                true
            );

        } else {
            parent::__construct(
                $file->getPathname(),
                $originalName ?? $file->getBasename(),
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

    /**
     * @inheritDoc
     * @throws FileCopyException
     */
    public function copy(string $directory = null, string $name = null): FileInterface
    {
        $newPath = null;

        if ($directory) {
            if (!$name) {
                $name = $this->getBasename();
            }

            $newPath = sprintf('%s/%s', $directory, $name);
        }

        if ($newPath) {
            if (file_exists($newPath)) {
                throw new FileCopyException($this, $newPath, "File already exists");
            }
        } else {
            $directory = $this->getPath();

            $fileName = FileUtils::generateFileCopyBasename($this, function($name) use ($directory) {
                return !file_exists(FileUtils::path($directory, $name));
            });

            $newPath  = FileUtils::path($directory, $fileName);
        }

        $fileInfo = new \SplFileInfo($newPath);

        $target = $this->getTargetFile($fileInfo->getPath(), $fileInfo->getBasename());

        set_error_handler(static function ($type, $msg) use (&$error) { $error = $msg; });
        $copied = copy($this->getPathname(), $target);
        restore_error_handler();

        if (!$copied) {
            throw new FileException(sprintf('Could not copy the file "%s" to "%s" (%s).', $this->getPathname(), $target,
                strip_tags($error)));
        }

        @chmod($target, 0666 & ~umask());

        return new self($target, $this->getClientOriginalName());
    }
}
