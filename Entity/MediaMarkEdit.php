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

use Doctrine\ORM\Mapping as ORM;

/**
 * Class MediaMarkEdit
 *
 * @ORM\Table(name="glavweb_media_mark_edit")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MediaMarkEdit
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
     * @ORM\Column(name="request_id", type="string")
     */
    private $requestId;

    /**
     * @var string
     *
     * @ORM\Column(name="new_name", type="string", nullable=true)
     */
    private $newName;

    /**
     * @var string
     *
     * @ORM\Column(name="new_description", type="string", nullable=true)
     */
    private $newDescription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Glavweb\UploaderBundle\Entity\Media", inversedBy="mediaMarkEdits", cascade={"persist"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $media;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime());
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
     * Set requestId
     *
     * @param string $requestId
     * @return MediaMarkEdit
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
     * Set newName
     *
     * @param string $newName
     * @return MediaMarkEdit
     */
    public function setNewName($newName)
    {
        $this->newName = $newName;

        return $this;
    }

    /**
     * Get newName
     *
     * @return string 
     */
    public function getNewName()
    {
        return $this->newName;
    }

    /**
     * Set newDescription
     *
     * @param string $newDescription
     * @return MediaMarkEdit
     */
    public function setNewDescription($newDescription)
    {
        $this->newDescription = $newDescription;

        return $this;
    }

    /**
     * Get newDescription
     *
     * @return string
     */
    public function getNewDescription()
    {
        return $this->newDescription;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return MediaMarkEdit
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
     * @return MediaMarkEdit
     */
    public function setMedia(Media $media)
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
}
