<?php

namespace Glavweb\UploaderBundle\Model;

/**
 * Interface MultipartUploadPartInterface
 *
 * @package Glavweb\UploaderBundle\Model
 *
 * @author  Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
interface MultipartUploadPartInterface
{
    /**
     * @return int
     */
    public function getNumber(): int;

    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @return MultipartUploadInterface
     */
    public function getMultipartUpload(): MultipartUploadInterface;
}