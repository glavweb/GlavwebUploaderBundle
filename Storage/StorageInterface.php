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

/**
 * Interface StorageInterface
 *
 * @package Glavweb\UploaderBundle\Storage
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
interface StorageInterface
{
    /**
     * Uploads a File instance to the configured storage.
     *
     * @param        $file
     * @param string $directory
     * @param string $name
     * @return FileInterface
     */
    public function upload(FileInterface $file, $directory, $name);

    /**
     * @param string $link
     * @return FileInterface|false
     */
    public function uploadTmpFileByLink($link);

    /**
     * @param array  $files
     * @param string $directory
     * @return array
     */
    public function uploadFiles(array $files, $directory);

    /**
     * @param string $directory
     * @param array  $onlyFileNames
     * @return array
     */
    public function getFilesByDirectory($directory, array $onlyFileNames = null);

    /**
     * @param $directory
     * @param $lifetime
     */
    public function clearOldFiles($directory, $lifetime);

    /**
     * @param $directory
     * @param $name
     * @return FileInterface
     */
    public function getFile($directory, $name);

    /**
     * @param $directory
     * @param $name
     * @return bool
     */
    public function isFile($directory, $name);

    /**
     * @param FileInterface $file
     */
    public function removeFile(FileInterface $file);

    /**
     * @param FileInterface $file
     * @param array $cropData
     * @return string
     * @throws CropImageException
     */
    public function cropImage(FileInterface $file, array $cropData): string;
}
