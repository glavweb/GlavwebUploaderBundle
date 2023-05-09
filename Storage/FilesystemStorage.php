<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Storage;

use Glavweb\UploaderBundle\File\FileMetadata;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FilesystemFile;
use Glavweb\UploaderBundle\Util\CropImage;
use Glavweb\UploaderBundle\Util\FileUtils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;

/**
 * Class FilesystemStorage
 *
 * @package Glavweb\UploaderBundle
 * @author  Andrey Nilov <nilov@glavweb.ru>
 */
class FilesystemStorage extends LocalStorage
{
    /**
     * @param string $tempDirectoryPath
     */
    public function __construct(string $tempDirectoryPath)
    {
        parent::__construct(new Filesystem(), $tempDirectoryPath);
    }

    /**
     * @inheritDoc
     */
    public function upload(FileInterface $file, $directory, $name = null)
    {
        /** @var File $file */
        if ($name === null) {
            $name = $file->getBasename();
        }

        $path = sprintf('%s/%s', $directory, $name);
        $targetName = FileUtils::basename($path);
        $targetDir  = \dirname($path);

        $file = $file->move($targetDir, $targetName);

        return new FilesystemFile($file);
    }

    /**
     * @inheritDoc
     */
    public function uploadTmpFileByLink($link)
    {
        $file = FileUtils::getTempFileByUrl($link);

        return new FilesystemFile($file);
    }

    /**
     * @inheritDoc
     */
    public function uploadFiles(array $files, $directory)
    {
        $return = [];
        foreach ($files as $file) {
            $return[] = $this->upload($file, $directory);
        }

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function getFilesByDirectory($directory, array $onlyFileNames = null)
    {
        $finder = new Finder();

        try {
            $finder->in($directory)->files();

        } catch (\InvalidArgumentException $e) {
            //catch non-existing directory exception.
            //This can happen if getFilesByDirectory is called and no file has yet been uploaded

            //push empty array into the finder so we can emulate no files found
            $finder->append([]);
        }

        // filter
        if ($onlyFileNames) {
            $finder->filter(function($file) use ($onlyFileNames) {
                /** @var SplFileInfo $file */
                return \in_array($file->getFilename(), $onlyFileNames, true);
            });
        }

        $files = [];
        foreach ($finder as $file) {
            /** @var File $file */
            $files[] = new FilesystemFile(new File($file->getPathname()));
        }

        return $files;
    }

    /**
     * @inheritDoc
     */
    public function clearOldFiles($directory, $lifetime)
    {
        $filesystem = new Filesystem();
        $finder     = new Finder();

        try {
            $finder->in($directory)->date('<=' . -1 * (int)$lifetime . 'seconds')->files();
        } catch (\InvalidArgumentException $e) {
            // the finder will throw an exception of type InvalidArgumentException
            // if the directory he should search in does not exist
            // in that case we don't have anything to clean
            return;
        }

        foreach ($finder as $file) {
            $filesystem->remove($file);
        }
    }

    /**
     * @inheritDoc
     */
    public function getFile($directory, $name)
    {
        $path = sprintf('%s/%s', $directory, $name);
        $file = new File($path);

        return new FilesystemFile($file);
    }

    /**
     * @inheritDoc
     */
    public function isFile($directory, $name)
    {
        $path = sprintf('%s/%s', $directory, $name);

        return is_file($path);
    }

    /**
     * @inheritDoc
     */
    public function removeFile(FileInterface $file)
    {
        $filesystem = new Filesystem();
        $filesystem->remove($file);
    }

    /**
     * @inheritDoc
     */
    public function copyFile(FileInterface $file, string $newPath = null): FileInterface
    {
        if (!$file instanceof FilesystemFile) {
            throw new \InvalidArgumentException('$file must be instance of ' . FilesystemFile::class);
        }

        if ($newPath) {
            $fileInfo = new \SplFileInfo($newPath);

            return $file->copy($fileInfo->getPath(), $fileInfo->getBasename());
        }

        return $file->copy();
    }

    /**
     * @inheritDoc
     */
    public function moveFile(FileInterface $file, $newPath)
    {
        $filesystem = new Filesystem();
        $filesystem->rename($file->getPathname(), $newPath);
    }

    /**
     * @inheritDoc
     */
    public function cropImage(FileInterface $file, array $cropData): string
    {
        $pathname   = $file->getPathname();
        $cropResult = CropImage::crop($pathname, $pathname, $cropData);

        $updatedPathname = $pathname;
        if ($cropResult) {
            $updatedPathname = FileUtils::saveFileWithNewVersion($file);
        }

        return $updatedPathname;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(string $filePathName): FileMetadata
    {
        $metadata                   = new FileMetadata();
        $metadata->size             = $this->getSize($filePathName);
        $metadata->mimeType         = $this->getMimeType($filePathName);
        $metadata->modificationTime = new \DateTime($this->getTimestamp($filePathName));
        $metadata->isImage          = false;

        [$width, $height] = getimagesize($filePathName);

        if (isset($width) || isset($height)) {
            $metadata->isImage = true;
            $metadata->width   = $width;
            $metadata->height  = $height;
        }

        return $metadata;
    }

    /**
     * @inheritDoc
     */
    public function getMimeType(string $filePathName)
    {
        return MimeTypes::getDefault()->guessMimeType($filePathName) ?: false;
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp(string $filePathName)
    {
        return \filemtime($filePathName);
    }

    /**
     * @inheritDoc
     */
    public function getSize(string $filePathName)
    {
        return \filesize($filePathName);
    }
}
