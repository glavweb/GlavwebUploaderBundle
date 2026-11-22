<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Glavweb\UploaderBundle\Model\MultipartUploadInterface;
use Glavweb\UploaderBundle\Model\MultipartUploadPartInterface;

/**
 * Class MultipartUploadPart.
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
#[ORM\Table(
    name: 'glavweb_multipart_upload_part',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'number_uniq', columns: ['multipart_upload_id', 'number']),
    ],
)]
#[ORM\Entity]
class MultipartUploadPart implements MultipartUploadPartInterface
{
    #[ORM\Column(name: 'id', type: UuidType::NAME)]
    #[ORM\Id]
    private ?Uuid $id = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    #[ORM\Column(name: 'number', type: 'integer')]
    private ?int $number = null;

    #[ORM\Column(name: 'data', type: 'json')]
    private ?array $data = null;

    #[ORM\JoinColumn(name: 'multipart_upload_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: MultipartUpload::class, inversedBy: 'parts')]
    private ?MultipartUploadInterface $multipartUpload = null;

    #[\Override]
    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    #[\Override]
    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    #[\Override]
    public function getMultipartUpload(): MultipartUploadInterface
    {
        return $this->multipartUpload;
    }

    public function setMultipartUpload(MultipartUploadInterface $multipartUpload): static
    {
        $this->multipartUpload = $multipartUpload;

        return $this;
    }
}
