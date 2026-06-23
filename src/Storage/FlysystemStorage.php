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

use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FilesystemFile;
use Glavweb\UploaderBundle\File\FlysystemFile;
use Glavweb\UploaderBundle\Util\CropImage;
use Glavweb\UploaderBundle\Util\FileUtils;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FlysystemStorage.
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
readonly class FlysystemStorage implements StorageInterface
{
    /**
     * FlysystemStorage constructor.
     */
    public function __construct(private FilesystemOperator $filesystem)
    {
    }

    /**
     * @throws FilesystemException
     */
    public function upload(FileInterface $file, string $directory, ?string $name = null): FileInterface
    {
        /* @var File $file */
        if (null === $name) {
            $name = $file->getBasename();
        }

        $path = \sprintf('%s/%s', $directory, $name);

        try {
            $source = fopen($file->getPathname(), 'r');

            $this->filesystem->writeStream($path, $source, [
                'visibility' => Visibility::PUBLIC,
                'mimetype' => $file->getMimeType(),
            ]);
        } finally {
            if (isset($source) && \is_resource($source)) {
                fclose($source);
            }
        }

        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();

        $symfonyFilesystem = new Filesystem();
        $symfonyFilesystem->remove($file);

        $flysystemFile = new FlysystemFile($this, $path);
        $flysystemFile->setSize($size);
        $flysystemFile->setOriginalName($originalName);

        return $flysystemFile;
    }

    public function uploadTmpFileByLink(string $link): FileInterface
    {
        $file = FileUtils::getTempFileByUrl($link);

        return new FilesystemFile($file);
    }

    /**
     * @return FileInterface[]
     *
     * @throws FilesystemException
     */
    public function uploadFiles(array $files, string $directory): array
    {
        $return = [];
        foreach ($files as $file) {
            $return[] = $this->upload($file, $directory);
        }

        return $return;
    }

    /**
     * @throws FilesystemException
     */
    public function clearOldFiles($directory, $lifetime): void
    {
        /** @var FlysystemFile $file */
        foreach ($this->getFilesByDirectory($directory) as $file) {
            $nowTimestamp = new \DateTime()->getTimestamp();
            $fileTimestamp = $file->getLastModifiedAt()->getTimestamp();

            if (($nowTimestamp - $fileTimestamp) > $lifetime) {
                $this->removeFile($file);
            }
        }
    }

    /**
     * @throws FilesystemException
     */
    public function removeFile(FileInterface $file): void
    {
        $path = \sprintf('%s/%s', $file->getPath(), $file->getBasename());

        $this->filesystem->delete($path);
    }

    public function cropImage(FileInterface $file, array $cropData): string
    {
        try {
            $pathname = $file->getPathname();
            $sourceFile = $this->filesystem->readStream($pathname);
            $tempFile = tmpfile();

            stream_copy_to_stream($sourceFile, $tempFile);

            $tempFilePathname = stream_get_meta_data($tempFile)['uri'];

            $cropResult = CropImage::crop($tempFilePathname, $tempFilePathname, $cropData);

            $this->filesystem->writeStream($pathname, $tempFile);
            if ($cropResult) {
                return FileUtils::saveFileWithNewVersion($file);
            }

            return $pathname;
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
     * @throws FilesystemException
     */
    public function move(FlysystemFile $file, string $newPath): void
    {
        $this->filesystem->move($file->getPathname(), $newPath);
    }

    /**
     * @return FlysystemFile[]
     *
     * @throws FilesystemException
     */
    public function getFilesByDirectory(string $directory, ?array $onlyFileNames = null): array
    {
        $files = [];
        $listing = $this->filesystem->listContents($directory);

        foreach ($listing as $item) {
            $path = $item['path'];
            $basename = $item['basename'];

            if ($onlyFileNames && !\in_array($basename, $onlyFileNames, true)) {
                continue;
            }

            $flysystemFile = new FlysystemFile($this, $path);
            $flysystemFile->setMetadata($item);

            $files[] = $flysystemFile;
        }

        return $files;
    }

    public function getFile($directory, $name): FileInterface
    {
        $path = \sprintf('%s/%s', $directory, $name);

        return new FlysystemFile($this, $path);
    }

    /**
     * @throws FilesystemException
     */
    public function isFile($directory, $name): bool
    {
        $path = \sprintf('%s/%s', $directory, $name);

        return $this->filesystem->has($path);
    }

    /**
     * @throws FilesystemException
     */
    public function getSize(FlysystemFile $file): int
    {
        return $this->filesystem->fileSize($file->getPathname());
    }

    /**
     * @throws FilesystemException
     */
    public function getTimestamp(FlysystemFile $file): int
    {
        return $this->filesystem->lastModified($file->getPathname());
    }

    /**
     * @throws FilesystemException
     */
    public function getMimeType(FlysystemFile $file): string
    {
        return $this->filesystem->mimeType($file->getPathname());
    }
}
