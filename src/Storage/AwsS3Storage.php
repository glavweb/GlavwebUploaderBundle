<?php

namespace Glavweb\UploaderBundle\Storage;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Glavweb\UploaderBundle\Exception\Exception;
use Glavweb\UploaderBundle\Exception\FileCopyException;
use Glavweb\UploaderBundle\Exception\FileNotFoundException;
use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\File\FileMetadata;
use Glavweb\UploaderBundle\File\FilesystemFile;
use Glavweb\UploaderBundle\File\StorageFile;
use Glavweb\UploaderBundle\Model\MultipartUploadInterface;
use Glavweb\UploaderBundle\Model\MultipartUploadManagerInterface;
use Glavweb\UploaderBundle\Model\MultipartUploadPartInterface;
use Glavweb\UploaderBundle\Util\CropImage;
use Glavweb\UploaderBundle\Util\FileUtils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class AwsS3Storage.
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class AwsS3Storage implements StorageInterface
{
    private ?S3Client $client;

    private string $bucket;

    private Filesystem $filesystem;

    private MultipartUploadManagerInterface $multipartUploadManager;

    public function __construct(string $bucket, ?S3Client $client, MultipartUploadManagerInterface $multipartUploadManager)
    {
        $this->bucket = $bucket;
        $this->client = $client;
        $this->filesystem = new Filesystem();
        $this->multipartUploadManager = $multipartUploadManager;
    }

    private function getClient(): S3Client
    {
        if (null === $this->client) {
            throw new \LogicException('S3 client is not configured.');
        }

        return $this->client;
    }

    public function upload(FileInterface $file, string $directory, ?string $name = null, bool $attachment = false): FileInterface
    {
        /* @var File $file */
        if (null === $name) {
            $name = $file->getBasename();
        }

        if ($file instanceof StorageFile && $file->isUploaded()) {
            $file->move($directory, $name);

            return $file;
        }

        $path = \sprintf('%s/%s', $directory, $name);
        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();
        $mimeType = $file->getMimeType();

        $this->getClient()->putObject([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'SourceFile' => $file->getPathname(),
            'ContentDisposition' => $attachment ? 'attachment' : 'inline',
            'ContentType' => $mimeType,
            'Metadata' => [
                'Is-Image' => $file->isImage() ? 1 : 0,
                'Width' => $file->getWidth() ?: 0,
                'Height' => $file->getHeight() ?: 0,
                'Original-Name' => base64_encode($originalName),
            ],
        ]);

        $this->filesystem->remove($file->getPathname());

        $storageFile = new StorageFile($this, $path, true);
        $storageFile->setSize($size);
        $storageFile->setOriginalName($originalName);
        $storageFile->setMimeType($mimeType);

        return $storageFile;
    }

    public function uploadTmpFileByLink(string $link): FileInterface
    {
        $file = FileUtils::getTempFileByUrl($link);

        return new FilesystemFile($file);
    }

    public function uploadFiles(array $files, string $directory): array
    {
        $return = [];
        foreach ($files as $file) {
            $return[] = $this->upload($file, $directory);
        }

        return $return;
    }

    public function clearOldFiles($directory, $lifetime): void
    {
        /** @var StorageFile $file */
        foreach ($this->getFilesByDirectory($directory) as $file) {
            $nowTimestamp = (new \DateTime())->getTimestamp();
            $fileTimestamp = $file->getLastModifiedAt()->getTimestamp();

            if (($nowTimestamp - $fileTimestamp) > $lifetime) {
                $this->removeFile($file);
            }
        }
    }

    /**
     * @throws FileNotFoundException
     */
    public function removeFile(FileInterface $file): void
    {
        $path = \sprintf('%s/%s', $file->getPath(), $file->getBasename());

        try {
            $this->getClient()->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);
        } catch (S3Exception $e) {
            if ('NoSuchKey' === $e->getAwsErrorCode()) {
                throw new FileNotFoundException($e->getMessage(), $e->getCode(), $e);
            }

            throw $e;
        }
    }

    /**
     * @throws FileNotFoundException
     */
    public function cropImage(FileInterface $file, array $cropData): string
    {
        $tempFilePathname = \tempnam(\sys_get_temp_dir(), '');
        $pathname = $file->getPathname();

        try {
            $object = $this->getClient()->getObject([
                'Bucket' => $this->bucket,
                'Key' => $pathname,
                'SaveAs' => $tempFilePathname,
            ]);
        } catch (S3Exception $e) {
            if ('NoSuchKey' === $e->getAwsErrorCode()) {
                throw new FileNotFoundException($e->getMessage(), $e->getCode(), $e);
            }

            throw $e;
        }

        $cropResult = CropImage::crop($tempFilePathname, $tempFilePathname, $cropData);

        $this->getClient()->putObject([
            'Bucket' => $this->bucket,
            'Key' => $pathname,
            'SourceFile' => $tempFilePathname,
            'ContentType' => $object['ContentType'],
        ]);

        $this->filesystem->remove($tempFilePathname);

        if ($cropResult) {
            return FileUtils::saveFileWithNewVersion($file);
        }

        return $pathname;
    }

    /**
     * @throws FileCopyException
     * @throws FileNotFoundException
     */
    public function moveFile(FileInterface $file, string $newPath): void
    {
        if ($this->hasObject($newPath)) {
            throw new FileCopyException($file, $newPath, 'File already exists');
        }

        $this->getClient()->copyObject([
            'Bucket' => $this->bucket,
            'CopySource' => "$this->bucket/{$file->getPathname()}",
            'Key' => $newPath,
        ]);

        $this->removeFile($file);
    }

    /**
     * @throws FileCopyException
     */
    public function copyFile(FileInterface $file, ?string $newPath = null): FileInterface
    {
        $path = $file->getPathname();

        if ($newPath) {
            if ($this->hasObject($newPath)) {
                throw new FileCopyException($file, $newPath, 'File already exists');
            }
        } else {
            $fileName = FileUtils::generateFileCopyBasename($file, fn (string $path): bool => !$this->hasObject(FileUtils::path($file->getPath(), $path)));
            $newPath = FileUtils::path($file->getPath(), $fileName);
        }

        $this->getClient()->copyObject([
            'Bucket' => $this->bucket,
            'CopySource' => "$this->bucket/{$path}",
            'Key' => $newPath,
        ]);

        return new StorageFile($this, $newPath, true);
    }

    public function getFilesByDirectory(string $directory, ?array $onlyFileNames = null): array
    {
        $files = [];

        $iterator = $this->getClient()->getIterator('ListObjects', [
            'Bucket' => $this->bucket,
            'Prefix' => $directory,
        ]);

        foreach ($iterator as $object) {
            $path = $object['Key'];
            $basename = \basename($object['Key']);

            if ($onlyFileNames && !\in_array($basename, $onlyFileNames, true)) {
                continue;
            }

            $storageFile = new StorageFile($this, $path, true);
            $storageFile->setSize((int) $object['Size']);
            $storageFile->setLastModifiedAt(new \DateTime($object['LastModified']));

            $files[] = $storageFile;
        }

        return $files;
    }

    public function getFile($directory, $name): FileInterface
    {
        $path = \sprintf('%s/%s', $directory, $name);

        return new StorageFile($this, $path, true);
    }

    public function isFile($directory, $name): bool
    {
        $path = \sprintf('%s/%s', $directory, $name);

        return $this->hasObject($path);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getMetadata(string $filePathName): FileMetadata
    {
        $object = $this->headObject($filePathName);

        $metadata = new FileMetadata();
        $metadata->size = (int) $object['ContentLength'];
        $metadata->mimeType = $object['ContentType'];
        $metadata->modificationTime = new \DateTime($object['LastModified']);
        $metadata->isImage = (bool) ($object['Metadata']['Is-Image'] ?? null);
        $metadata->width = (int) ($object['Metadata']['Width'] ?? 0);
        $metadata->height = (int) ($object['Metadata']['Height'] ?? 0);
        $metadata->originalName = base64_decode($object['Metadata']['Original-Name'] ?? '');

        return $metadata;
    }

    public function addFileChunk(File $file, string $fileId, int $chunkIndex): void
    {
        if ($this->multipartUploadManager->has($fileId)) {
            $multipartUpload = $this->multipartUploadManager->get($fileId);
        } else {
            $multipartUpload = $this->createMultipartUpload($fileId);
        }

        try {
            $fileResource = fopen($file->getPathname(), 'rb');
            $partNumber = $chunkIndex + 1;

            $result = $this->getClient()->uploadPart([
                'Body' => $fileResource,
                'Bucket' => $this->bucket,
                'Key' => $this->createTempFileKey($fileId),
                'PartNumber' => $partNumber,
                'UploadId' => $multipartUpload->getId(),
            ]);

            $this->multipartUploadManager->addPart($multipartUpload, $partNumber, [
                'ETag' => substr($result['ETag'], 1, -1),
            ]);
        } finally {
            if (isset($fileResource) && \is_resource($fileResource)) {
                fclose($fileResource);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function hasAllFileChunks(string $fileId, int $chunkTotal): bool
    {
        return $this->multipartUploadManager->countParts($fileId) === $chunkTotal;
    }

    public function concatFileChunks(File $file, FileMetadata $metadata, string $fileId): FileInterface
    {
        $multipartUpload = $this->multipartUploadManager->get($fileId);

        $key = $this->createTempFileKey($fileId);

        $parts = $multipartUpload->getParts();
        usort($parts, static fn (MultipartUploadPartInterface $a, MultipartUploadPartInterface $b): int => $a->getNumber() - $b->getNumber());

        $partsParameter = array_map(static fn (MultipartUploadPartInterface $part): array => [
            'ETag' => $part->getData()['ETag'],
            'PartNumber' => $part->getNumber(),
        ], $parts);

        $this->getClient()->completeMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'UploadId' => $multipartUpload->getId(),
            'Parts' => $partsParameter,
        ]);

        $this->multipartUploadManager->delete($multipartUpload);

        $this->getClient()->waitUntil('ObjectExists', [
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        $this->getClient()->copyObject([
            'Bucket' => $this->bucket,
            'CopySource' => "$this->bucket/$key",
            'Key' => $key,
            'ContentType' => $metadata->mimeType,
            'MetadataDirective' => 'REPLACE',
            'Metadata' => [
                'Is-Image' => $metadata->isImage ? 1 : 0,
                'Width' => $metadata->width ?: 0,
                'Height' => $metadata->height ?: 0,
                'Original-Name' => base64_encode($metadata->originalName),
            ],
        ]);

        return new StorageFile($this, $key, true);
    }

    public function cleanup(): void
    {
        $actualTime = new \DateTime('-1 hour');

        foreach ($this->multipartUploadManager->list() as $multipartUpload) {
            if ($multipartUpload->getLastModifiedAt() < $actualTime) {
                try {
                    $this->getClient()->abortMultipartUpload([
                        'Bucket' => $this->bucket,
                        'Key' => $multipartUpload->getKey(),
                        'UploadId' => $multipartUpload->getId(),
                    ]);
                } catch (S3Exception $e) {
                    if ('NoSuchUpload' !== $e->getAwsErrorCode()) {
                        throw $e;
                    }
                }

                $this->multipartUploadManager->delete($multipartUpload);
            }
        }
    }

    private function hasObject(string $key): bool
    {
        return $this->getClient()->doesObjectExist($this->bucket, $key);
    }

    /**
     * @throws FileNotFoundException
     */
    private function headObject(string $key): Result
    {
        try {
            return $this->getClient()->headObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
        } catch (S3Exception $e) {
            if ('NoSuchKey' === $e->getAwsErrorCode()) {
                throw new FileNotFoundException($e->getMessage(), $e->getCode(), $e);
            }

            throw $e;
        }
    }

    /**
     * @throws \Throwable
     */
    private function createMultipartUpload(string $fileId): MultipartUploadInterface
    {
        $key = $this->createTempFileKey($fileId);

        if ($this->getClient()->doesObjectExist($this->bucket, $key)) {
            throw new Exception('File already exists');
        }

        $result = $this->getClient()->createMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        $uploadId = $result['UploadId'];

        try {
            return $this->multipartUploadManager->create($fileId, $uploadId);
        } catch (\Throwable $e) {
            $this->getClient()->abortMultipartUpload([
                'Bucket' => $this->bucket,
                'Key' => $result['Key'],
                'UploadId' => $result['UploadId'],
            ]);

            throw $e;
        }
    }

    private function createTempFileKey(string $fileId): string
    {
        return "tmp/$fileId";
    }
}
