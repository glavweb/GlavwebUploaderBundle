<?php

namespace Glavweb\UploaderBundle\File;

use Glavweb\UploaderBundle\Exception\Exception;
use Glavweb\UploaderBundle\Exception\FileNotFoundException;
use Glavweb\UploaderBundle\Storage\StorageInterface;
use Symfony\Component\Mime\MimeTypes;

/**
 * Class StorageFile
 *
 * @package Glavweb\UploaderBundle\File
 *
 * @author  Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class StorageFile implements FileInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var string
     */
    private $pathname;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $originalName;

    /**
     * @var
     */
    private $mimeType;

    /**
     * @var bool
     */
    private $isImage = false;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $width;

    /**
     * @var \DateTime
     */
    private $lastModifiedAt;

    /**
     * @var bool
     */
    private $uploaded;

    /**
     * FlysystemFile constructor.
     *
     * @param StorageInterface $storage
     * @param string           $path
     * @param bool             $uploaded
     */
    public function __construct(StorageInterface $storage, string $path, bool $uploaded)
    {
        $this->storage  = $storage;
        $this->pathname = $path;
        $this->uploaded = $uploaded;
    }

    /**
     * @inheritDoc
     */
    public function getSize()
    {
        if (!isset($this->size) && $this->isUploaded()){
            $this->fetchMetadata();
        }

        return $this->size;
    }

    /**
     * @param int|null $size
     * @return StorageFile
     */
    public function setSize(?int $size): StorageFile
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param string|null $originalName
     * @return StorageFile
     */
    public function setOriginalName(?string $originalName): StorageFile
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastModifiedAt()
    {
        if (!isset($this->lastModifiedAt) && $this->isUploaded()) {
            $this->fetchMetadata();
        }

        return $this->lastModifiedAt;
    }

    /**
     * @param \DateTimeInterface|null $lastModifiedAt
     * @return StorageFile
     */
    public function setLastModifiedAt(?\DateTimeInterface $lastModifiedAt): StorageFile
    {
        $this->lastModifiedAt = $lastModifiedAt;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPathname()
    {
        return $this->pathname;
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return pathinfo($this->getPathname(), PATHINFO_DIRNAME);
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    public function getMimeType()
    {
        if (!isset($this->mimeType) && $this->isUploaded()) {
            $this->fetchMetadata();
        }

        return $this->mimeType;
    }

    /**
     * @param mixed|null $mimeType
     * @return StorageFile
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBasename()
    {
        return pathinfo($this->getPathname(), PATHINFO_BASENAME);
    }

    /**
     * @inheritDoc
     */
    public function getExtension()
    {
        return pathinfo($this->getPathname(), PATHINFO_EXTENSION);
    }

    /**
     * @inheritDoc
     */
    public function getClientOriginalName()
    {
        if (!isset($this->originalName) && $this->isUploaded()) {
            $this->fetchMetadata();
        }

        return $this->originalName;
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    public function guessExtension()
    {
        $extensions = MimeTypes::getDefault()->getExtensions($this->getMimeType());

        if (isset($extensions[0])) {
            return $extensions[0];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function move($directory, $name = null)
    {
        if (!$name) {
            $name = $this->getBasename();
        }

        $newPath = sprintf('%s/%s', $directory, $name);

        $this->storage->moveFile($this, $newPath);

        $this->pathname = $newPath;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function copy(string $directory = null, string $name = null): FileInterface
    {
        $newPath = null;

        if ($directory) {
            if (!$name) {
                $name = $this->getBasename();
            }

            $newPath = sprintf('%s/%s', $directory, $name);
        }

        return $this->storage->copyFile($this, $newPath);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function fetchMetadata(): void
    {
        if (!$this->isUploaded()) {
            throw new Exception('File isn\'t uploaded');
        }

        $metadata = $this->storage->getMetadata($this->getPathname());

        $this->setMetadata($metadata);
    }

    /**
     * @param FileMetadata $metadata
     */
    public function setMetadata(FileMetadata $metadata): void
    {
        if (isset($metadata->size)) {
            $this->setSize($metadata->size);
        }

        if (isset($metadata->modificationTime)) {
            $this->setLastModifiedAt($metadata->modificationTime);
        }

        if (isset($metadata->mimeType)) {
            $this->setMimeType($metadata->mimeType);
        }

        if (isset($metadata->originalName)) {
            $this->setOriginalName($metadata->originalName);
        }

        if (isset($metadata->height)) {
            $this->setHeight($metadata->height);
        }

        if (isset($metadata->isImage)) {
            $this->setIsImage($metadata->isImage);
        }

        if (isset($metadata->width)) {
            $this->setWidth($metadata->width);
        }
    }

    /**
     * @return bool
     */
    public function isUploaded(): bool
    {
        return $this->uploaded;
    }

    /**
     * @param bool $uploaded
     * @return StorageFile
     */
    public function setUploaded(bool $uploaded): StorageFile
    {
        $this->uploaded = $uploaded;

        return $this;
    }

    /**
     * @return bool
     */
    public function isImage(): ?bool
    {
        if (!isset($this->isImage) && $this->isUploaded()) {
            $this->fetchMetadata();
        }

        return $this->isImage;
    }

    /**
     * @param bool $isImage
     * @return StorageFile
     */
    public function setIsImage(bool $isImage): StorageFile
    {
        $this->isImage = $isImage;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): ?int
    {
        if (!isset($this->height) && $this->isUploaded()) {
            $this->fetchMetadata();
        }

        return $this->height;
    }

    /**
     * @param int $height
     * @return StorageFile
     */
    public function setHeight(int $height): StorageFile
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): ?int
    {
        if (!isset($this->width) && $this->isUploaded()) {
            $this->fetchMetadata();
        }

        return $this->width;
    }

    /**
     * @param int $width
     * @return StorageFile
     */
    public function setWidth(int $width): StorageFile
    {
        $this->width = $width;

        return $this;
    }
}