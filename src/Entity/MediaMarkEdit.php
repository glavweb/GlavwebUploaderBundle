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
 * Class MediaMarkEdit.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
#[ORM\Table(name: 'glavweb_media_mark_edit')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class MediaMarkEdit
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'request_id', type: 'string')]
    private ?string $requestId = null;

    #[ORM\Column(name: 'new_name', type: 'string', nullable: true)]
    private ?string $newName = null;

    #[ORM\Column(name: 'new_description', type: 'string', nullable: true)]
    private ?string $newDescription = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist'], inversedBy: 'mediaMarkEdits')]
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
     * Set newName.
     */
    public function setNewName(?string $newName): static
    {
        $this->newName = $newName;

        return $this;
    }

    /**
     * Get newName.
     */
    public function getNewName(): ?string
    {
        return $this->newName;
    }

    /**
     * Set newDescription.
     */
    public function setNewDescription(?string $newDescription): static
    {
        $this->newDescription = $newDescription;

        return $this;
    }

    /**
     * Get newDescription.
     */
    public function getNewDescription(): ?string
    {
        return $this->newDescription;
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
