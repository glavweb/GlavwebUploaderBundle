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

use DateTime;
use Exception;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FilesystemFile;
use Glavweb\UploaderBundle\File\FlysystemFile;
use Glavweb\UploaderBundle\Util\CropImage;
use Glavweb\UploaderBundle\Util\FileUtils;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class FlysystemStorage
 *
 * @package Glavweb\UploaderBundle\Storage
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class FlysystemStorage implements StorageInterface
{

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * FlysystemStorage constructor.
     *
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
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

        $this->filesystem->put($path, file_get_contents($file->getPathname()), [
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
            'mimetype'   => $file->getMimeType()
        ]);

        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();

        $symfonyFilesystem = new Filesystem();
        $symfonyFilesystem->remove($file);

        $flysystemFile = new FlysystemFile($this, $path);
        $flysystemFile->setSize($size);
        $flysystemFile->setOriginalName($originalName);

        return $flysystemFile;
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
        try {
            $return = [];
            foreach ($files as $file) {
                $return[] = $this->upload($file, $directory);
            }

            return $return;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function clearOldFiles($directory, $lifetime)
    {
        /** @var FlysystemFile $file */
        foreach ($this->getFilesByDirectory($directory) as $file) {
            $nowTimestamp  = (new DateTime())->getTimestamp();
            $fileTimestamp = $file->getLastModifiedAt()->getTimestamp();

            if (($nowTimestamp - $fileTimestamp) > $lifetime) {
                $this->removeFile($file);
            }
        }
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    public function removeFile(FileInterface $file)
    {
        $path = sprintf('%s/%s', $file->getPath(), $file->getBasename());

        $this->filesystem->delete($path);
    }

    /**
     * @inheritDoc
     */
    public function cropImage(FileInterface $file, array $cropData)
    {
        $pathname = $file->getPathname();
        $cropResult = CropImage::crop($pathname, $pathname, $cropData);

        $updatedPathname = $pathname;
        if ($cropResult) {
            $updatedPathname = FileUtils::saveFileWithNewVersion($file);
        }

        return $updatedPathname;
    }

    /**
     * @param FlysystemFile $file
     * @param string $newPath
     * @throws FileNotFoundException
     * @throws FileExistsException
     */
    public function move(FlysystemFile $file, $newPath)
    {
        $this->filesystem->rename($file->getPathname(), $newPath);
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

            if ($onlyFileNames && !in_array($basename, $onlyFileNames, true)) {
                continue;
            }

            $flysystemFile = new FlysystemFile($this, $path);
            $flysystemFile->setMetadata($item);

            $files[] = $flysystemFile;
        }

        return $files;
    }

    /**
     * @inheritDoc
     */
    public function getFile($directory, $name)
    {
        $path = sprintf('%s/%s', $directory, $name);

        return new FlysystemFile($this, $path);
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
     * @param FlysystemFile $file
     * @return false|int
     * @throws FileNotFoundException
     */
    public function getSize(FlysystemFile $file)
    {
        return $this->filesystem->getSize($file->getPathname());
    }

    /**
     * @param FlysystemFile $file
     * @return false|int
     * @throws FileNotFoundException
     */
    public function getTimestamp(FlysystemFile $file)
    {
        return $this->filesystem->getTimestamp($file->getPathname());
    }

    /**
     * @param FlysystemFile $file
     * @return false|string
     * @throws FileNotFoundException
     */
    public function getMimeType(FlysystemFile $file)
    {
        return $this->filesystem->getMimetype($file->getPathname());
    }
}