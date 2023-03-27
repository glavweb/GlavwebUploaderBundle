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

use Exception;
use Glavweb\UploaderBundle\File\FileMetadata;
use Glavweb\UploaderBundle\Exception\CropImageException;
use Glavweb\UploaderBundle\Exception\FileCopyException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\StorageFile;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException as FlysystemFileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

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
     * @param FileInterface $file
     * @param string        $directory
     * @param string        $name
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
     * @param string     $directory
     * @param array|null $onlyFileNames
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
     * @param FileInterface $file file to copy
     * @param string|null   $newPath
     * @return FileInterface new copy file
     * @throws FileCopyException
     */
    public function copyFile(FileInterface $file, string $newPath = null): FileInterface;

    /**
     * @param StorageFile $file
     * @param string      $newPath
     * @throws FlysystemFileNotFoundException
     * @throws FileExistsException
     */
    public function moveFile(FileInterface $file, $newPath);

    /**
     * @param FileInterface $file
     * @param array $cropData
     * @return string
     * @throws CropImageException
     */
    public function cropImage(FileInterface $file, array $cropData): string;

    /**
     * @param string $filePathName
     * @return FileMetadata
     */
    public function getMetadata(string $filePathName): FileMetadata;

    /**
     * @param File   $file
     * @param string $fileId
     * @param int    $chunkIndex
     */
    public function addFileChunk(File $file, string $fileId, int $chunkIndex): void;

    /**
     * @param string $fileId
     * @param int $chunkTotal
     * @return bool
     */
    public function hasAllFileChunks(string $fileId, int $chunkTotal): bool;

    /**
     * @param File         $file
     * @param FileMetadata $metadata
     * @param string       $fileId
     * @return FileInterface
     * @throws Exception
     */
    public function concatFileChunks(File $file, FileMetadata $metadata, string $fileId): FileInterface;

    /**
     * Cleanup trash files
     *
     * @return void
     */
    public function cleanup(): void;
}
