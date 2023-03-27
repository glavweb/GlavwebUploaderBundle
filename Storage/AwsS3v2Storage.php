<?php

namespace Glavweb\UploaderBundle\Storage;

use Aws\S3\Exception\NoSuchKeyException;
use Aws\S3\S3Client;
use Glavweb\UploaderBundle\File\FileMetadata;
use Glavweb\UploaderBundle\Exception\Exception;
use Glavweb\UploaderBundle\Exception\FileCopyException;
use Glavweb\UploaderBundle\Exception\FileNotFoundException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FilesystemFile;
use Glavweb\UploaderBundle\File\StorageFile;
use Glavweb\UploaderBundle\Model\MultipartUploadInterface;
use Glavweb\UploaderBundle\Model\MultipartUploadManagerInterface;
use Glavweb\UploaderBundle\Model\MultipartUploadPartInterface;
use Glavweb\UploaderBundle\Util\CropImage;
use Glavweb\UploaderBundle\Util\FileUtils;
use Guzzle\Service\Resource\Model;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Throwable;

class AwsS3v2Storage implements StorageInterface
{
    /**
     * @var S3Client
     */
    private $client;

    /**
     * @var string
     */
    private $bucket;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var MultipartUploadManagerInterface
     */
    private $multipartUploadManager;

    /**
     * @param string                          $bucket
     * @param S3Client                        $client
     * @param MultipartUploadManagerInterface $multipartUploadManager
     */
    public function __construct(string                          $bucket,
                                S3Client                        $client,
                                MultipartUploadManagerInterface $multipartUploadManager)
    {
        $this->client                 = $client;
        $this->bucket                 = $bucket;
        $this->filesystem             = new Filesystem();
        $this->multipartUploadManager = $multipartUploadManager;
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

        if ($file instanceof StorageFile && $file->isUploaded()) {
            $file->move($directory, $name);

            return $file;
        }

        $path         = sprintf('%s/%s', $directory, $name);
        $originalName = $file->getClientOriginalName();
        $size         = $file->getSize();
        $mimeType     = $file->getMimeType();

        $this->client->putObject(
            [
                'Bucket'      => $this->bucket,
                'Key'         => $path,
                'SourceFile'  => $file->getPathname(),
                'ContentType' => $mimeType,
                'Metadata'    => [
                    'is-image'      => $file->isImage() ? 1 : 0,
                    'width'         => $file->getWidth() ?: 0,
                    'height'        => $file->getHeight() ?: 0,
                    'original-name' => base64_encode($file->getClientOriginalName())
                ]
            ]
        );

        $this->filesystem->remove($file);

        $storageFile = new StorageFile($this, $path, true);
        $storageFile->setSize($size);
        $storageFile->setOriginalName($originalName);
        $storageFile->setMimeType($mimeType);

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
     * @throws FileNotFoundException
     */
    public function removeFile(FileInterface $file)
    {
        $path = sprintf('%s/%s', $file->getPath(), $file->getBasename());

        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $path
            ]);
        } catch (NoSuchKeyException $e) {
            throw new FileNotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    public function cropImage(FileInterface $file, array $cropData): string
    {
        $tempFilePathname = \tempnam(\sys_get_temp_dir(), '');
        $pathname         = $file->getPathname();

        try {
            $object = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $pathname,
                'SaveAs' => $tempFilePathname
            ]);
        } catch (NoSuchKeyException $e) {
            throw new FileNotFoundException($e->getMessage(), $e->getCode(), $e);
        }

        $cropResult = CropImage::crop($tempFilePathname, $tempFilePathname, $cropData);

        $this->client->putObject(
            [
                'Bucket'      => $this->bucket,
                'Key'         => $pathname,
                'SourceFile'  => $tempFilePathname,
                'ContentType' => $object['ContentType'],
            ]
        );

        $this->filesystem->remove($tempFilePathname);

        $updatedPathname = $pathname;
        if ($cropResult) {
            $updatedPathname = FileUtils::saveFileWithNewVersion($file);
        }

        return $updatedPathname;
    }

    /**
     * @param StorageFile $file
     * @param string      $newPath
     * @throws FileNotFoundException
     * @throws FileCopyException
     */
    public function moveFile(FileInterface $file, $newPath)
    {
        if ($this->hasObject($newPath)) {
            throw new FileCopyException($file, $newPath, 'File already exists');
        }

        $this->client->copyObject([
            'Bucket'     => $this->bucket,
            'CopySource' => "$this->bucket/{$file->getPathname()}",
            'Key'        => $newPath
        ]);

        $this->removeFile($file);
    }

    /**
     * @inheritdoc
     */
    public function copyFile(FileInterface $file, string $newPath = null): FileInterface
    {
        $path = $file->getPathname();

        if ($newPath) {
            if ($this->hasObject($newPath)) {
                throw new FileCopyException($file, $newPath, 'File already exists');
            }
        } else {
            $fileName = FileUtils::generateFileCopyBasename($file, function($path) use ($file) {
                return !$this->hasObject(FileUtils::path($file->getPath(), $path));
            });
            $newPath  = FileUtils::path($file->getPath(), $fileName);
        }

        $this->client->copyObject([
            'Bucket'     => $this->bucket,
            'CopySource' => $path,
            'Key'        => $newPath
        ]);

        return new StorageFile($this, $newPath, true);
    }

    /**
     * @inheritDoc
     */
    public function getFilesByDirectory($directory, array $onlyFileNames = null)
    {
        $files = [];

        $iterator = $this->client->getIterator('ListObjects', [
            'Bucket' => $this->bucket,
            'Prefix' => $directory
        ]);

        foreach ($iterator as $object) {
            $path     = $object['Key'];
            $basename = \basename($object['Key']);

            if ($onlyFileNames && !\in_array($basename, $onlyFileNames, true)) {
                continue;
            }

            $storageFile = new StorageFile($this, $path, true);
            $storageFile->setSize((int)$object['Size']);
            $storageFile->setLastModifiedAt(new \DateTime($object['LastModified']));

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

        return $this->hasObject($path);
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(string $filePathName): FileMetadata
    {
        $object = $this->headObject($filePathName);

        $metadata                   = new FileMetadata();
        $metadata->size             = (int)$object['ContentLength'];
        $metadata->mimeType         = $object['ContentType'];
        $metadata->modificationTime = new \DateTime($object['LastModified']);
        $metadata->isImage          = (bool)$object->getPath('Metadata/is-image');
        $metadata->width            = (int)$object->getPath('Metadata/width');
        $metadata->height           = (int)$object->getPath('Metadata/height');
        $metadata->originalName     = base64_decode($object->getPath('Metadata/original-name'));

        return $metadata;
    }

    /**
     * @inheritDoc
     */
    public function addFileChunk(File $file, string $fileId, int $chunkIndex): void
    {
        if ($this->multipartUploadManager->has($fileId)) {
            $multipartUpload = $this->multipartUploadManager->get($fileId);
        } else {
            $multipartUpload = $this->createMultipartUpload($fileId);
        }

        try {
            $fileResource = fopen($file->getPathname(), 'rb');
            $partNumber   = $chunkIndex + 1;

            $result = $this->client->uploadPart([
                'Body'       => $fileResource,
                'Bucket'     => $this->bucket,
                'Key'        => $this->createTempFileKey($fileId),
                'PartNumber' => $partNumber,
                'UploadId'   => $multipartUpload->getId()
            ]);

            $this->multipartUploadManager->addPart($multipartUpload, $partNumber, [
                'ETag' => substr($result['ETag'], 1, -1)
            ]);
        } finally {
            if (isset($fileResource) && \is_resource($fileResource)) {
                fclose($fileResource);
            }
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function hasAllFileChunks(string $fileId, int $chunkTotal): bool
    {
        return $this->multipartUploadManager->countParts($fileId) === $chunkTotal;
    }

    /**
     * @param FileMetadata $metadata
     * @inheritDoc
     */
    public function concatFileChunks(File $file, FileMetadata $metadata, string $fileId): FileInterface
    {
        $multipartUpload = $this->multipartUploadManager->get($fileId);

        $key = $this->createTempFileKey($fileId);

        $parts = $multipartUpload->getParts();
        usort($parts, static function(MultipartUploadPartInterface $a, MultipartUploadPartInterface $b) {
            return $a->getNumber() - $b->getNumber();
        });

        $partsParameter = array_map(static function(MultipartUploadPartInterface $part) {
            return [
                'ETag'       => $part->getData()['ETag'],
                'PartNumber' => $part->getNumber(),
            ];
        }, $parts);

        $this->client->completeMultipartUpload([
            'Bucket'   => $this->bucket,
            'Key'      => $key,
            'UploadId' => $multipartUpload->getId(),
            'Parts'    => $partsParameter
        ]);

        $this->multipartUploadManager->delete($multipartUpload);

        $this->client->waitUntilObjectExists([
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);

        $this->client->copyObject([
            'Bucket'            => $this->bucket,
            'CopySource'        => "$this->bucket/$key",
            'Key'               => $key,
            'ContentType'       => $metadata->mimeType,
            'MetadataDirective' => 'REPLACE',
            'Metadata'          => [
                'is-image'      => $metadata->isImage ? 1 : 0,
                'width'         => $metadata->width ?: 0,
                'height'        => $metadata->height ?: 0,
                'original-name' => base64_encode($metadata->originalName)
            ]
        ]);

        return new StorageFile($this, $key, true);
    }

    /**
     * @inheritDoc
     */
    public function cleanup(): void
    {
        $actualTime = new \DateTime('-1 hour');

        foreach ($this->multipartUploadManager->list() as $multipartUpload) {
            if ($multipartUpload->getLastModifiedAt() < $actualTime) {
                $this->client->abortMultipartUpload([
                    'Bucket'   => $this->bucket,
                    'Key'      => $multipartUpload->getKey(),
                    'UploadId' => $multipartUpload->getId()
                ]);
                $this->multipartUploadManager->delete($multipartUpload);
            }
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    private function hasObject(string $key): bool
    {
        return $this->client->doesObjectExist($this->bucket, $key);
    }

    /**
     * @param string $key
     * @return Model
     * @throws FileNotFoundException
     */
    private function headObject(string $key): Model
    {
        try {
            return $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);
        } catch (NoSuchKeyException $e) {
            throw new FileNotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $fileId
     * @return MultipartUploadInterface
     * @throws Throwable
     */
    private function createMultipartUpload(string $fileId): MultipartUploadInterface
    {
        $key = $this->createTempFileKey($fileId);

        if ($this->client->doesObjectExist($this->bucket, $key)) {
            throw new Exception('File already exists');
        }

        $result = $this->client->createMultipartUpload([
            'Bucket' => $this->bucket,
            'Key'    => $key
        ]);

        $uploadId = $result['UploadId'];

        try {
            return $this->multipartUploadManager->create($fileId, $uploadId);
        } catch (\Throwable $e) {
            $this->client->abortMultipartUpload([
                'Bucket'   => $this->bucket,
                'Key'      => $result['Key'],
                'UploadId' => $result['UploadId']
            ]);

            throw $e;
        }
    }

    /**
     * @param string $fileId
     * @return string
     */
    private function createTempFileKey(string $fileId): string
    {
        return "tmp/$fileId";
    }
}