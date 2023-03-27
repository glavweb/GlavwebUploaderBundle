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
use Glavweb\UploaderBundle\Exception\FileCopyException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FilesystemFile;
use Glavweb\UploaderBundle\File\StorageFile;
use Glavweb\UploaderBundle\Util\CropImage;
use Glavweb\UploaderBundle\Util\FileUtils;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException as FlysystemFileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class FlysystemStorage
 *
 * @package Glavweb\UploaderBundle\Storage
 * @author  Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class FlysystemStorage extends LocalStorage
{

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * FlysystemStorage constructor.
     *
     * @param FilesystemInterface $filesystem
     * @param string              $tempDirectoryPath
     */
    public function __construct(FilesystemInterface $filesystem, string $tempDirectoryPath)
    {
        parent::__construct(new Filesystem(), $tempDirectoryPath);

        $this->filesystem = $filesystem;
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

        try {
            $source = fopen($file->getPathname(), 'rb');

            $this->filesystem->putStream($path, $source, [
                'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                'mimetype'   => $file->getMimeType()
            ]);
        } finally {
            if (isset($source) && \is_resource($source)) {
                fclose($source);
            }
        }

        $originalName = $file->getClientOriginalName();
        $size         = $file->getSize();

        $symfonyFilesystem = new Filesystem();

        $storageFile = new StorageFile($this, $path, true);
        $storageFile->setSize($size);
        $storageFile->setOriginalName($originalName);
        $storageFile->setMimeType($file->getMimeType());
        $storageFile->setWidth($file->getWidth());
        $storageFile->setHeight($file->getHeight());

        $symfonyFilesystem->remove($file);

        $storageFile->fetchMetadata();

        return $storageFile;
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
    public function clearOldFiles($directory, $lifetime)
    {
        /** @var StorageFile $file */
        foreach ($this->getFilesByDirectory($directory) as $file) {
            $nowTimestamp  = (new \DateTime())->getTimestamp();
            $fileTimestamp = $file->getLastModifiedAt()->getTimestamp();

            if (($nowTimestamp - $fileTimestamp) > $lifetime) {
                $this->removeFile($file);
            }
        }
    }

    /**
     * @inheritDoc
     * @throws FlysystemFileNotFoundException
     */
    public function removeFile(FileInterface $file)
    {
        $path = sprintf('%s/%s', $file->getPath(), $file->getBasename());

        $this->filesystem->delete($path);
    }

    /**
     * @inheritDoc
     */
    public function cropImage(FileInterface $file, array $cropData): string
    {
        try {
            $pathname   = $file->getPathname();
            $sourceFile = $this->filesystem->readStream($pathname);
            $tempFile   = tmpfile();

            stream_copy_to_stream($sourceFile, $tempFile);

            $tempFilePathname = stream_get_meta_data($tempFile)['uri'];

            $cropResult = CropImage::crop($tempFilePathname, $tempFilePathname, $cropData);

            $this->filesystem->updateStream($pathname, $tempFile);

            $updatedPathname = $pathname;
            if ($cropResult) {
                $updatedPathname = FileUtils::saveFileWithNewVersion($file);
            }

            return $updatedPathname;

        } finally {
            if (isset($sourceFile) && \is_resource($sourceFile)) {
                fclose($sourceFile);
            }
            if (isset($tempFile) && \is_resource($tempFile)) {
                fclose($tempFile);
            }
        }
    }

    /**
     * @param StorageFile $file
     * @param string      $newPath
     * @throws FlysystemFileNotFoundException
     * @throws FileExistsException
     */
    public function moveFile(FileInterface $file, $newPath)
    {
        $this->filesystem->rename($file->getPathname(), $newPath);
    }

    /**
     * @inheritdoc
     * @param FileInterface $file
     * @param string|null   $newPath
     * @return FileInterface
     * @throws FileCopyException
     * @throws FileExistsException
     * @throws FlysystemFileNotFoundException
     */
    public function copyFile(FileInterface $file, string $newPath = null): FileInterface
    {
        $path = $file->getPathname();

        if ($newPath) {
            if ($this->filesystem->has($newPath)) {
                throw new FileCopyException($file, $newPath, 'File already exists');
            }
        } else {
            $fileName = FileUtils::generateFileCopyBasename($file, function($path) use ($file) {
                return !$this->filesystem->has(FileUtils::path($file->getPath(), $path));
            });
            $newPath  = FileUtils::path($file->getPath(), $fileName);
        }

        $this->filesystem->copy($path, $newPath);

        return new StorageFile($this, $newPath, true);
    }

    /**
     * @inheritDoc
     */
    public function getFilesByDirectory($directory, array $onlyFileNames = null)
    {
        $files   = [];
        $listing = $this->filesystem->listContents($directory);

        foreach ($listing as $item) {
            $path     = $item['path'];
            $basename = $item['basename'];

            if ($onlyFileNames && !\in_array($basename, $onlyFileNames, true)) {
                continue;
            }

            $storageFile = new StorageFile($this, $path, true);
            $storageFile->setSize($item['size']);
            $storageFile->setLastModifiedAt((new \DateTime())->setTimestamp($item['timestamp']));
            $storageFile->setMimeType($item['mimetype']);

            $files[] = $storageFile;
        }

        return $files;
    }

    /**
     * @inheritDoc
     */
    public function getFile($directory, $name)
    {
        $path = sprintf('%s/%s', $directory, $name);

        return new StorageFile($this, $path, true);
    }

    /**
     * @inheritDoc
     */
    public function isFile($directory, $name)
    {
        $path = sprintf('%s/%s', $directory, $name);

        return $this->filesystem->has($path);
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(string $filePathName): FileMetadata
    {
        $object    = $this->filesystem->getMetadata($filePathName);
        $timestamp = $this->filesystem->getTimestamp($filePathName);

        $metadata                   = new FileMetadata();
        $metadata->size             = $object['size'];
        $metadata->mimeType         = $object['mimetype'];
        $metadata->modificationTime = (new \DateTime())->setTimestamp($timestamp);

        return $metadata;
    }
}