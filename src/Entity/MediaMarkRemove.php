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
 * Class MediaMarkRemove.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
#[ORM\Table(name: 'glavweb_media_mark_remove')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class MediaMarkRemove
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'request_id', type: 'string')]
    private ?string $requestId = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist'], inversedBy: 'mediaMarkRemoves')]
    private ?Media $media = null;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set requestId.
     */
    public function setRequestId(?string $requestId): static
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * Get requestId.
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * Set createdAt.
     */
    public function setCreatedAt(?\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Set media.
     */
    public function setMedia(?Media $media): static
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media.
     */
    public function getMedia(): ?Media
    {
        return $this->media;
    }
}
