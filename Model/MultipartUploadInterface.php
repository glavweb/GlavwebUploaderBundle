<?php

namespace Glavweb\UploaderBundle\Model;

use DateTimeInterface;

/**
 * Interface MultipartUploadInterface
 *
 * @package Glavweb\UploaderBundle\Model
 *
 * @author  Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
interface MultipartUploadInterface
{

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getKey(): string;

    /**
     * @return DateTimeInterface
     */
    public function getLastModifiedAt(): \DateTimeInterface;

    /**
     * @return MultipartUploadPartInterface[]
     */
    public function getParts(): array;

    /**
     * @param MultipartUploadPartInterface $part
     * @return void
     */
    public function addPart(MultipartUploadPartInterface $part): void;
}