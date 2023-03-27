<?php

namespace Glavweb\UploaderBundle\File;

class FileMetadata {

    /**
     * @var bool
     */
    public $isImage;

    /**
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    /**
     * @var string
     */
    public $mimeType;

    /**
     * @var string
     */
    public $originalName;

    /**
     * @var int
     */
    public $size;

    /**
     * @var DateTimeInterface
     */
    public $modificationTime;
}