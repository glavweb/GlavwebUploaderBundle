<?php

namespace Glavweb\UploaderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MediaMarkRemove
 */
class MediaMarkRemove
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $requestId;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \Glavweb\UploaderBundle\Entity\Media
     */
    private $media;

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
     * Set requestId
     *
     * @param string $requestId
     * @return MediaMarkRemove
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return MediaMarkRemove
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
     * Set media
     *
     * @param \Glavweb\UploaderBundle\Entity\Media $media
     * @return MediaMarkRemove
     */
    public function setMedia(\Glavweb\UploaderBundle\Entity\Media $media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return \Glavweb\UploaderBundle\Entity\Media 
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime());
    }
}
