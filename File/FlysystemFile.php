<?php


namespace Glavweb\UploaderBundle\File;


use Glavweb\UploaderBundle\Storage\FlysystemStorage;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\Mime\MimeTypes;

/**
 * Class FlysystemFile
 *
 * @package Glavweb\UploaderBundle\File
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class FlysystemFile implements FileInterface
{
    /**
     * @var FlysystemStorage
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
     * @var \DateTime
     */
    private $lastModifiedAt;

    /**
     * FlysystemFile constructor.
     *
     * @param FlysystemStorage $storage
     * @param string $path
     */
    public function __construct(FlysystemStorage $storage, $path)
    {
        $this->storage  = $storage;
        $this->pathname = $path;
    }

    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    public function getSize()
    {
        if (!$this->size) {
            $this->size = $this->storage->getSize($this);
        }

        return $this->size;
    }

    /**
     * @param int|null $size
     * @return FlysystemFile
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param string|null $originalName
     * @return FlysystemFile
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * @return \DateTime|null
     * @throws FileNotFoundException
     */
    public function getLastModifiedAt()
    {
        if (!$this->lastModifiedAt) {
            $this->lastModifiedAt = (new \DateTime())->setTimestamp($this->storage->getTimestamp($this));
        }

        return $this->lastModifiedAt;
    }

    /**
     * @param \DateTime|null $lastModifiedAt
     * @return FlysystemFile
     */
    public function setLastModifiedAt($lastModifiedAt)
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
        if (!$this->mimeType) {
            $this->mimeType = $this->storage->getMimeType($this);
        }

        if ($this->mimeType) {
            return $this->mimeType;
        }

        $mimeType = pathinfo($this->getPathname(), PATHINFO_BASENAME);

        if (is_array($mimeType)) {
            return $mimeType[0];
        }

        return $mimeType;
    }

    /**
     * @param mixed|null $mimeType
     * @return FlysystemFile
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
        return $this->originalName;
    }

    /**
     * @inheritDoc
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
     * @param array $data
     */
    public function setMetadata(array $data)
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