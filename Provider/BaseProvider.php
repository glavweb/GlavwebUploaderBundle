<?php

namespace Glavweb\UploaderBundle\Provider;
use Glavweb\UploaderBundle\Model\MediaInterface;

/**
 * Class BaseProvider
 * @package Glavweb\UploaderBundle\Provider
 */
abstract class BaseProvider implements ProviderInterface
{
    protected $isParsed = false;
    protected $name;
    protected $description;
    protected $providerReference;
    protected $width;
    protected $height;
    protected $contentType;
    protected $contentSize;
    protected $thumbnailUrl;

    /*
    public function loadByMedia(MediaInterface $media)
    {
        $this->setName($media->getName());
        $this->setProviderReference($media->getProviderReference());
        $this->setContentSize($media->getContentSize());
        $this->setContentType($media->getContentType());
        $this->setDescription($media->getDescription());
        $this->setThumbnailUrl(null);
        $this->setHeight($media->getHeight());
        $this->setWidth($media->getWidth());
    }
    */

    /**
     * @return bool
     */
    public function isParsed()
    {
        return $this->isParsed;
    }

    /**
     * @param mixed $contentSize
     */
    public function setContentSize($contentSize)
    {
        $this->contentSize = $contentSize;
    }

    /**
     * @return mixed
     */
    public function getContentSize()
    {
        return $this->contentSize;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $providerReference
     */
    public function setProviderReference($providerReference)
    {
        $this->providerReference = $providerReference;
    }

    /**
     * @return mixed
     */
    public function getProviderReference()
    {
        return $this->providerReference;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $thumbnailUrl
     */
    public function setThumbnailUrl($thumbnailUrl)
    {
        $this->thumbnailUrl = $thumbnailUrl;
    }

    /**
     * @return mixed
     */
    public function getThumbnailUrl()
    {
        return $this->thumbnailUrl;
    }
}