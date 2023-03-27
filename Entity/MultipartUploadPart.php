<?php

namespace Glavweb\UploaderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Glavweb\UploaderBundle\Model\MultipartUploadInterface;
use Glavweb\UploaderBundle\Model\MultipartUploadPartInterface;

/**
 * Class MultipartUploadPart.
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="glavweb_multipart_upload_part",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="number_uniq", columns={"multipart_upload_id", "number"})
 *     }
 * )
 *
 * @author Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class MultipartUploadPart implements MultipartUploadPartInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="number", type="integer", nullable=false)
     */
    private $number;

    /**
     * @var array
     *
     * @ORM\Column(name="data", type="array")
     */
    private $data;

    /**
     * @var MultipartUploadInterface
     *
     * @ORM\ManyToOne(targetEntity="Glavweb\UploaderBundle\Entity\MultipartUpload", inversedBy="parts")
     * @ORM\JoinColumn(name="multipart_upload_id", nullable=false)
     */
    private $multipartUpload;

    /**
     * @inheritDoc
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     * @return MultipartUploadPart
     */
    public function setNumber(int $number): MultipartUploadPart
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return MultipartUploadPart
     */
    public function setData(array $data): MultipartUploadPart
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMultipartUpload(): MultipartUploadInterface
    {
        return $this->multipartUpload;
    }

    /**
     * @param MultipartUploadInterface $multipartUpload
     * @return MultipartUploadPart
     */
    public function setMultipartUpload(MultipartUploadInterface $multipartUpload): MultipartUploadPart
    {
        $this->multipartUpload = $multipartUpload;

        return $this;
    }
}