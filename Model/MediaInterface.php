<?php

namespace Glavweb\UploaderBundle\Model;

/**
 * Class MediaInterface
 * @package Glavweb\UploaderBundle\Model
 */
interface MediaInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set context
     *
     * @param string $context
     * @return Media
     */
    public function setContext($context);

    /**
     * Get context
     *
     * @return string
     */
    public function getContext();

    /**
     * Set providerName
     *
     * @param string $providerName
     * @return Media
     */
    public function setProviderName($providerName);

    /**
     * Get providerName
     *
     * @return string
     */
    public function getProviderName();

    /**
     * Set providerReference
     *
     * @param string $providerReference
     * @return Media
     */
    public function setProviderReference($providerReference);

    /**
     * Get providerReference
     *
     * @return string
     */
    public function getProviderReference();

    /**
     * Set contentPath
     *
     * @param string $contentPath
     * @return Media
     */
    public function setContentPath($contentPath);

    /**
     * Get contentPath
     *
     * @return string
     */
    public function getContentPath();

    /**
     * Set thumbnailPath
     *
     * @param string $thumbnailPath
     * @return Media
     */
    public function setThumbnailPath($thumbnailPath);

    /**
     * Get thumbnailPath
     *
     * @return string
     */
    public function getThumbnailPath();

    /**
     * Set name
     *
     * @param string $name
     * @return Media
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set description
     *
     * @param string $description
     * @return Media
     */
    public function setDescription($description);

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set width
     *
     * @param integer $width
     * @return Media
     */
    public function setWidth($width);

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth();

    /**
     * Set height
     *
     * @param integer $height
     * @return Media
     */
    public function setHeight($height);

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight();

    /**
     * Set contentType
     *
     * @param string $contentType
     * @return Media
     */
    public function setContentType($contentType);

    /**
     * Get contentType
     *
     * @return string
     */
    public function getContentType();

    /**
     * Set contentSize
     *
     * @param integer $contentSize
     * @return Media
     */
    public function setContentSize($contentSize);

    /**
     * Get contentSize
     *
     * @return integer
     */
    public function getContentSize();

    /**
     * Set isOrphan
     *
     * @param boolean $isOrphan
     * @return Media
     */
    public function setIsOrphan($isOrphan);

    /**
     * Get isOrphan
     *
     * @return boolean
     */
    public function getIsOrphan();

    /**
     * Set requestId
     *
     * @param string $requestId
     * @return Media
     */
    public function setRequestId($requestId);

    /**
     * Get requestId
     *
     * @return string
     */
    public function getRequestId();

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Media
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Media
     */
    public function setCreatedAt($createdAt);

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt();
}