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

use Glavweb\UploaderBundle\Exception\CropImageException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FilesystemFile;
use Glavweb\UploaderBundle\Util\CropImage;
use Glavweb\UploaderBundle\Util\FileUtils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Finder\Finder;
use function dirname;

/**
 * Class FilesystemStorage
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FilesystemStorage implements StorageInterface
{

    /**
     * @param FileInterface $file
     * @param string        $directory
     * @param string        $name
     * @return FileInterface
     */
    public function upload(FileInterface $file, $directory, $name = null)
    {
        /** @var File $file */
        if ($name === null) {
            $name = $file->getBasename();
        }

        $path = sprintf('%s/%s', $directory, $name);
        $targetName = basename($path);
        $targetDir  = dirname($path);

        $file = $file->move($targetDir, $targetName);
        $file = new FilesystemFile($file);

        return $file;
    }

    /**
     * @param string $link
     * @return FileInterface|false
     */
    public function uploadTmpFileByLink($link)
    {
        $file = FileUtils::getTempFileByUrl($link);

        return new FilesystemFile($file);
    }

    /**
     * @param array  $files
     * @param string $directory
     * @return array
     */
    public function uploadFiles(array $files, $directory)
    {
        try {
            $return = array();
            foreach ($files as $file) {
                $return[] = $this->upload($file, $directory);
            }

            return $return;
        } catch (\Exception $e) {
            return array();
        }
    }

    /**
     * @param string $directory
     * @param array  $onlyFileNames
     * @return array
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
            $finder->append(array());
        }

        // filter
        if ($onlyFileNames) {
            $finder->filter(function($file) use ($onlyFileNames) {
                /** @var \Symfony\Component\Finder\SplFileInfo $file */
                return in_array($file->getFilename(), $onlyFileNames);
            });
        }

        $files = array();
        foreach ($finder as $file) {
            /** @var File $file */
            $files[] = new FilesystemFile(new File($file->getPathname()));
        }
        return $files;
    }

    /**
     * @param $directory
     * @param $lifetime
     */
    public function clearOldFiles($directory, $lifetime)
    {
        $filesystem = new Filesystem();
        $finder = new Finder();

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
     * @param $directory
     * @param $name
     * @return FilesystemFile
     */
    public function getFile($directory, $name)
    {
        $path = sprintf('%s/%s', $directory, $name);
        $file = new File($path);

        return new FilesystemFile($file);
    }

    /**
     * @param $directory
     * @param $name
     * @return bool
     */
    public function isFile($directory, $name)
    {
        $path = sprintf('%s/%s', $directory, $name);

        return is_file($path);
    }

    /**
     * @param FileInterface $file
     */
    public function removeFile(FileInterface $file)
    {
        $filesystem = new Filesystem();
        $filesystem->remove($file);
    }

    /**
     * @param FileInterface $file
     * @param array $cropData
     * @return string
     * @throws CropImageException
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
}
