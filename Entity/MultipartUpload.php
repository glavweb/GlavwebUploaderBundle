<?php

namespace Glavweb\UploaderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Glavweb\UploaderBundle\Model\MultipartUploadInterface;
use Glavweb\UploaderBundle\Model\MultipartUploadPartInterface;

/**
 * Class MultipartUpload.
 *
 * @ORM\Entity
 * @ORM\Table(name="glavweb_multipart_upload")
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class MultipartUpload implements MultipartUploadInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string")
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string")
     */
    private $key;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="last_modified_at", type="datetimetz")
     */
    private $lastModifiedAt;

    /**
     * @var Collection<int, MultipartUploadPart>
     *
     * @ORM\OneToMany(
     *     targetEntity="Glavweb\UploaderBundle\Entity\MultipartUploadPart",
     *     indexBy="number",
     *     mappedBy="multipartUpload",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    private $parts;

    public function __construct(string $id)
    {
        $this->id    = $id;
        $this->parts = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return MultipartUpload
     */
    public function setKey(string $key): MultipartUpload
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLastModifiedAt(): \DateTimeInterface
    {
        return $this->lastModifiedAt;
    }

    /**
     * @param \DateTimeInterface $lastModifiedAt
     * @return MultipartUpload
     */
    public function setLastModifiedAt(\DateTimeInterface $lastModifiedAt): MultipartUpload
    {
        $this->lastModifiedAt = $lastModifiedAt;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPartsCollection(): Collection
    {
        return $this->parts;
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return $this->parts->toArray();
    }

    /**
     * @inheritDoc
     */
    public function addPart(MultipartUploadPartInterface $part): void
    {
        $this->parts->add($part);
    }
}