<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Glavweb\UploaderBundle\Model\MediaInterface;

/**
 * Class Media
 *
 * @ORM\Table(name="glavweb_media")
 * @ORM\Entity(repositoryClass="Glavweb\UploaderBundle\Entity\Repository\MediaRepository")
 * @ORM\EntityListeners({"Glavweb\UploaderBundle\Entity\Listener\MediaListener"})
 * @ORM\HasLifecycleCallbacks
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class Media implements MediaInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * 
     * @ORM\Column(name="context", type="string")
     */
    private $context;

    /**
     * @var string
     * 
     * @ORM\Column(name="provider_name", type="string")
     */
    private $providerName;

    /**
     * @var string
     * 
     * @ORM\Column(name="provider_reference", type="string", nullable=true)
     */
    private $providerReference;

    /**
     * @var string
     * 
     * @ORM\Column(name="content_path", type="string", nullable=true)
     */
    private $contentPath;

    /**
     * @var string
     * 
     * @ORM\Column(name="thumbnail_path", type="string", nullable=true)
     */
    private $thumbnailPath;

    /**
     * @var string
     * 
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var string
     * 
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer
     * 
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;

    /**
     * @var integer
     * 
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;

    /**
     * @var string
     * 
     * @ORM\Column(name="content_type", type="string")
     */
    private $contentType;

    /**
     * @var integer
     * 
     * @ORM\Column(name="content_size", type="integer", nullable=true)
     */
    private $contentSize;

    /**
     * @var boolean
     * 
     * @ORM\Column(name="is_orphan", type="boolean")
     */
    private $isOrphan;

    /**
     * @var string
     * 
     * @ORM\Column(name="request_id", type="string", nullable=true)
     */
    private $requestId;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string")
     */
    private $token;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Glavweb\UploaderBundle\Entity\MediaMarkRemove", mappedBy="media")
     */
    private $mediaMarkRemoves;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Glavweb\UploaderBundle\Entity\MediaMarkEdit", mappedBy="media")
     */
    private $mediaMarkEdits;

    /**
     * Media constructor.
     */
    public function __construct()
    {
        $this->mediaMarkRemoves = new ArrayCollection();
        $this->mediaMarkEdits   = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * @ORM\PrePersist
     * 
     * Lifecycle callback (pre persist)
     */
    public function prePersist()
    {
        $date = new \DateTime();

        $this->setCreatedAt($date);
        $this->setUpdatedAt($date);
        $this->setToken($this->generateToken());
    }

    /**
     * @ORM\PreUpdate
     *
     * Lifecycle callback (pre persist)
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @return string
     */
    public function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
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
     * Set token
     *
     * @param string $token
     * @return Media
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set position
     *
     * @param int $position
     * @return Media
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
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

    /**
     * Add mediaMarkRemove
     *
     * @param MediaMarkRemove $mediaMarkRemove
     *
     * @return Media
     */
    public function addMediaMarkRemove(MediaMarkRemove $mediaMarkRemove)
    {
        $this->mediaMarkRemoves[] = $mediaMarkRemove;

        return $this;
    }

    /**
     * Remove mediaMarkRemove
     *
     * @param MediaMarkRemove $mediaMarkRemove
     */
    public function removeMediaMarkRemove(MediaMarkRemove $mediaMarkRemove)
    {
        $this->mediaMarkRemoves->removeElement($mediaMarkRemove);
    }

    /**
     * @return ArrayCollection
     */
    public function getMediaMarkRemoves()
    {
        return $this->mediaMarkRemoves;
    }

    /**
     * Add mediaMarkEdit
     *
     * @param MediaMarkEdit $mediaMarkEdit
     *
     * @return Media
     */
    public function addMediaMarkEdit(MediaMarkEdit $mediaMarkEdit)
    {
        $this->mediaMarkEdits[] = $mediaMarkEdit;

        return $this;
    }

    /**
     * Remove mediaMarkEdit
     *
     * @param MediaMarkEdit $mediaMarkEdit
     */
    public function removeMediaMarkEdit(MediaMarkEdit $mediaMarkEdit)
    {
        $this->mediaMarkEdits->removeElement($mediaMarkEdit);
    }

    /**
     * @return ArrayCollection
     */
    public function getMediaMarkEdits()
    {
        return $this->mediaMarkEdits;
    }
}
