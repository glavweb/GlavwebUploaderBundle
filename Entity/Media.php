<?php

namespace Glavweb\UploaderBundle\Entity;

use Glavweb\UploaderBundle\Model\MediaInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Media
 * @package Glavweb\UploaderBundle\Entity
 */
class Media implements MediaInterface
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $context;

    /**
     * @var string
     */
    private $providerName;

    /**
     * @var string
     */
    private $providerReference;

    /**
     * @var string
     */
    private $contentPath;

    /**
     * @var string
     */
    private $thumbnailPath;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $width;

    /**
     * @var integer
     */
    private $height;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var integer
     */
    private $contentSize;

    /**
     * @var boolean
     */
    private $isOrphan;

    /**
     * @var string
     */
    private $requestId;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * Lifecycle callback (pre persist)
     */
    public function prePersist()
    {
        $date = new \DateTime('NOW');

        $this->setCreatedAt($date);
        $this->setUpdatedAt($date);
    }

    /**
     * Lifecycle callback (pre persist)
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime('NOW'));
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set context
     *
     * @param string $context
     * @return Media
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context
     *
     * @return string 
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set providerName
     *
     * @param string $providerName
     * @return Media
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * Get providerName
     *
     * @return string 
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * Set providerReference
     *
     * @param string $providerReference
     * @return Media
     */
    public function setProviderReference($providerReference)
    {
        $this->providerReference = $providerReference;

        return $this;
    }

    /**
     * Get providerReference
     *
     * @return string 
     */
    public function getProviderReference()
    {
        return $this->providerReference;
    }

    /**
     * Set contentPath
     *
     * @param string $contentPath
     * @return Media
     */
    public function setContentPath($contentPath)
    {
        $this->contentPath = $contentPath;

        return $this;
    }

    /**
     * Get contentPath
     *
     * @return string
     */
    public function getContentPath()
    {
        return $this->contentPath;
    }

    /**
     * Set thumbnailPath
     *
     * @param string $thumbnailPath
     * @return Media
     */
    public function setThumbnailPath($thumbnailPath)
    {
        $this->thumbnailPath = $thumbnailPath;

        return $this;
    }

    /**
     * Get thumbnailPath
     *
     * @return string
     */
    public function getThumbnailPath()
    {
        return $this->thumbnailPath;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Media
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Media
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set width
     *
     * @param integer $width
     * @return Media
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return Media
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer 
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set contentType
     *
     * @param string $contentType
     * @return Media
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get contentType
     *
     * @return string 
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set contentSize
     *
     * @param integer $contentSize
     * @return Media
     */
    public function setContentSize($contentSize)
    {
        $this->contentSize = $contentSize;

        return $this;
    }

    /**
     * Get contentSize
     *
     * @return integer 
     */
    public function getContentSize()
    {
        return $this->contentSize;
    }

    /**
     * Set isOrphan
     *
     * @param boolean $isOrphan
     * @return Media
     */
    public function setIsOrphan($isOrphan)
    {
        $this->isOrphan = $isOrphan;

        return $this;
    }

    /**
     * Get isOrphan
     *
     * @return boolean 
     */
    public function getIsOrphan()
    {
        return $this->isOrphan;
    }

    /**
     * Set requestId
     *
     * @param string $requestId
     * @return Media
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * Get requestId
     *
     * @return string 
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Media
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Media
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
