<?php

namespace Glavweb\UploaderBundle\File;

use Glavweb\UploaderBundle\Storage\FlysystemStorage;
use League\Flysystem\FilesystemException;
use Symfony\Component\Mime\MimeTypes;

/**
 * Class FlysystemFile.
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class FlysystemFile implements FileInterface
{
    private ?int $size = null;

    private ?string $originalName = null;

    private ?string $mimeType = null;

    private ?\DateTime $lastModifiedAt = null;

    /**
     * FlysystemFile constructor.
     */
    public function __construct(private readonly FlysystemStorage $storage, private string $pathname)
    {
    }

    /**
     * @throws FilesystemException
     */
    public function getSize(): int
    {
        if (!$this->size) {
            $this->size = $this->storage->getSize($this);
        }

        return $this->size;
    }

    public function setSize(?int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function setOriginalName(?string $originalName): static
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * @throws FilesystemException
     */
    public function getLastModifiedAt(): ?\DateTime
    {
        if (!$this->lastModifiedAt instanceof \DateTime) {
            $this->lastModifiedAt = new \DateTime()->setTimestamp($this->storage->getTimestamp($this));
        }

        return $this->lastModifiedAt;
    }

    public function setLastModifiedAt(?\DateTime $lastModifiedAt): static
    {
        $this->lastModifiedAt = $lastModifiedAt;

        return $this;
    }

    public function getPathname(): string
    {
        return $this->pathname;
    }

    public function getPath(): string
    {
        return pathinfo($this->getPathname(), \PATHINFO_DIRNAME);
    }

    /**
     * @throws FilesystemException
     */
    public function getMimeType(): ?string
    {
        if (!$this->mimeType) {
            $this->mimeType = $this->storage->getMimeType($this);
        }

        if ('' !== $this->mimeType && '0' !== $this->mimeType) {
            return $this->mimeType;
        }

        $mimeType = pathinfo($this->getPathname(), \PATHINFO_BASENAME);

        if (\is_array($mimeType)) {
            return $mimeType[0];
        }

        return $mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getBasename(): string
    {
        return pathinfo($this->getPathname(), \PATHINFO_BASENAME);
    }

    public function getExtension(): string
    {
        return pathinfo($this->getPathname(), \PATHINFO_EXTENSION);
    }

    public function getClientOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * @throws FilesystemException
     */
    public function guessExtension(): ?string
    {
        $extensions = MimeTypes::getDefault()->getExtensions($this->getMimeType());

        return $extensions[0] ?? null;
    }

    /**
     * @throws FilesystemException
     */
    public function move($directory, $name = null): static
    {
        $newPath = \sprintf('%s/%s', $directory, $name);

        $this->storage->move($this, $newPath);

        $this->pathname = $newPath;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setMetadata(array $data): void
    {
        if (isset($data['size'])) {
            $this->setSize($data['size']);
        }

        if (isset($data['timestamp'])) {
            $lastModifiedAt = new \DateTime();
            $lastModifiedAt->setTimestamp($data['timestamp']);

            $this->setLastModifiedAt($lastModifiedAt);
        }

        if (isset($data['mimetype'])) {
            $this->setMimeType($data['mimetype']);
        }
    }
}
