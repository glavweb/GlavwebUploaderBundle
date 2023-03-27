<?php

namespace Glavweb\UploaderBundle\Model;

/**
 * Interface MultipartUploadManagerInterface
 *
 * @package Glavweb\UploaderBundle\Model
 *
 * @author  Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
interface MultipartUploadManagerInterface
{
    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @return MultipartUploadInterface
     */
    public function get(string $key): MultipartUploadInterface;

    /**
     * @return MultipartUploadInterface[]
     */
    public function list(): array;

    /**
     * @param string $key
     * @param string $externalId
     * @return MultipartUploadInterface
     */
    public function create(string $key, string $externalId): MultipartUploadInterface;

    /**
     * @param MultipartUploadInterface $multipartUpload
     * @return void
     */
    public function delete(MultipartUploadInterface $multipartUpload): void;

    /**
     * @param MultipartUploadInterface $multipartUpload
     * @param int                      $number
     * @param array                    $data
     * @return mixed
     */
    public function addPart(MultipartUploadInterface $multipartUpload,
                            int                      $number,
                            array                    $data = []): MultipartUploadPartInterface;

    /**
     * @param string $key
     * @return int
     */
    public function countParts(string $key): int;
}