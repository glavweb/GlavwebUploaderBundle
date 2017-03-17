<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Model;

/**
 * Interface MediaInterface
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
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
     * @return MediaInterface
     */
    public function setRequestId($requestId);

    /**
     * Get requestId
     *
     * @return string
     */
    public function getRequestId();

    /**
     * Set token
     *
     * @param string $token
     * @return MediaInterface
     */
    public function setToken($token);

    /**
     * Get token
     *
     * @return string
     */
    public function getToken();

    /**
     * Set position
     *
     * @param int $position
     * @return MediaInterface
     */
    public function setPosition($position);

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition();

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return MediaInterface
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
     * @return MediaInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt();
}