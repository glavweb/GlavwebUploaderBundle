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
 * Interface StorageInterface.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
interface StorageInterface
{
    /**
     * Uploads a File instance to the configured storage.
     */
    public function upload(FileInterface $file, string $directory, ?string $name): FileInterface;

    public function uploadTmpFileByLink(string $link): FileInterface;

    public function uploadFiles(array $files, string $directory): array;

    public function getFilesByDirectory(string $directory, ?array $onlyFileNames = null): array;

    public function clearOldFiles($directory, $lifetime);

    public function getFile($directory, $name): FileInterface;

    public function isFile($directory, $name): bool;

    public function removeFile(FileInterface $file);

    /**
     * @throws CropImageException
     */
    public function cropImage(FileInterface $file, array $cropData): string;
}
