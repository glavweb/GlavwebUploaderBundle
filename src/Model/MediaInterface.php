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
 * Interface MediaInterface.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
interface MediaInterface
{
    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set context.
     *
     * @return MediaInterface
     */
    public function setContext(string $context);

    /**
     * Get context.
     *
     * @return string
     */
    public function getContext();

    /**
     * Set providerName.
     *
     * @return MediaInterface
     */
    public function setProviderName(string $providerName);

    /**
     * Get providerName.
     *
     * @return string
     */
    public function getProviderName();

    /**
     * Set providerReference.
     *
     * @return MediaInterface
     */
    public function setProviderReference(string $providerReference);

    /**
     * Get providerReference.
     *
     * @return string
     */
    public function getProviderReference();

    /**
     * Set contentPath.
     *
     * @return MediaInterface
     */
    public function setContentPath(string $contentPath);

    /**
     * Get contentPath.
     *
     * @return string
     */
    public function getContentPath();

    /**
     * Set thumbnailPath.
     *
     * @return MediaInterface
     */
    public function setThumbnailPath(string $thumbnailPath);

    /**
     * Get thumbnailPath.
     *
     * @return string
     */
    public function getThumbnailPath();

    /**
     * Set name.
     *
     * @return MediaInterface
     */
    public function setName(string $name);

    /**
     * Get name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set description.
     *
     * @return MediaInterface
     */
    public function setDescription(string $description);

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set width.
     *
     * @return MediaInterface
     */
    public function setWidth(int $width);

    /**
     * Get width.
     *
     * @return int
     */
    public function getWidth();

    /**
     * Set height.
     *
     * @return MediaInterface
     */
    public function setHeight(int $height);

    /**
     * Get height.
     *
     * @return int
     */
    public function getHeight();

    /**
     * Set contentType.
     *
     * @return MediaInterface
     */
    public function setContentType(string $contentType);

    /**
     * Get contentType.
     *
     * @return string
     */
    public function getContentType();

    /**
     * Set contentSize.
     *
     * @return MediaInterface
     */
    public function setContentSize(int $contentSize);

    /**
     * Get contentSize.
     *
     * @return int
     */
    public function getContentSize();

    /**
     * Set isOrphan.
     *
     * @return MediaInterface
     */
    public function setIsOrphan(bool $isOrphan);

    /**
     * Get isOrphan.
     *
     * @return bool
     */
    public function getIsOrphan();

    /**
     * Set requestId.
     *
     * @return MediaInterface
     */
    public function setRequestId(string $requestId);

    /**
     * Get requestId.
     *
     * @return string
     */
    public function getRequestId();

    /**
     * Set token.
     *
     * @return MediaInterface
     */
    public function setToken(string $token);

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken();

    /**
     * Set position.
     *
     * @return MediaInterface
     */
    public function setPosition(int $position);

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition();

    /**
     * Set updatedAt.
     *
     * @return MediaInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt);

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Set createdAt.
     *
     * @return MediaInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt();
}
