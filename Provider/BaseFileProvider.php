<?php

namespace Glavweb\UploaderBundle\Provider;

/**
 * Class BaseProvider
 * @package Glavweb\UploaderBundle\Provider
 */
abstract class BaseFileProvider extends BaseProvider implements ProviderFileInterface
{
    /**
     * @var FileInterface
     */
    protected $file;

    /**
     * @return FileInterface
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getProviderType()
    {
        return ProviderTypes::FILE;
    }
}