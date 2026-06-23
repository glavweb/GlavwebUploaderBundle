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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Glavweb\UploaderBundle\Entity\Listener\MediaListener;
use Glavweb\UploaderBundle\Entity\Repository\MediaRepository;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Random\RandomException;

/**
 * Class Media.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
#[ORM\Table(name: 'glavweb_media')]
#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\EntityListeners([MediaListener::class])]
#[ORM\HasLifecycleCallbacks]
class Media implements MediaInterface, \Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'context', type: 'string')]
    private ?string $context = null;

    #[ORM\Column(name: 'provider_name', type: 'string')]
    private ?string $providerName = null;

    #[ORM\Column(name: 'provider_reference', type: 'string', nullable: true)]
    private ?string $providerReference = null;

    #[ORM\Column(name: 'content_path', type: 'string', nullable: true)]
    private ?string $contentPath = null;

    #[ORM\Column(name: 'thumbnail_path', type: 'string', nullable: true)]
    private ?string $thumbnailPath = null;

    #[ORM\Column(name: 'name', type: 'string')]
    private ?string $name = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'width', type: 'integer', nullable: true)]
    private ?int $width = null;

    #[ORM\Column(name: 'height', type: 'integer', nullable: true)]
    private ?int $height = null;

    #[ORM\Column(name: 'content_type', type: 'string')]
    private ?string $contentType = null;

    #[ORM\Column(name: 'content_size', type: 'integer', nullable: true)]
    private ?int $contentSize = null;

    #[ORM\Column(name: 'is_orphan', type: 'boolean')]
    private bool $isOrphan = true;

    #[ORM\Column(name: 'request_id', type: 'string', nullable: true)]
    private ?string $requestId = null;

    #[ORM\Column(name: 'token', type: 'string')]
    private ?string $token = null;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTime $createdAt = null;

    #[ORM\OneToMany(targetEntity: MediaMarkRemove::class, mappedBy: 'media')]
    private Collection $mediaMarkRemoves;

    #[ORM\OneToMany(targetEntity: MediaMarkEdit::class, mappedBy: 'media')]
    private Collection $mediaMarkEdits;

    /**
     * Media constructor.
     */
    public function __construct()
    {
        $this->mediaMarkRemoves = new ArrayCollection();
        $this->mediaMarkEdits = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    /**
     * @throws RandomException
     */
    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $date = new \DateTime();

        $this->setCreatedAt($date);
        $this->setUpdatedAt($date);
        $this->setToken($this->generateToken());
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @throws RandomException
     */
    public function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set context.
     */
    public function setContext(?string $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context.
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * Set providerName.
     */
    public function setProviderName(?string $providerName): static
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * Get providerName.
     */
    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    /**
     * Set providerReference.
     */
    public function setProviderReference(?string $providerReference): static
    {
        $this->providerReference = $providerReference;

        return $this;
    }

    /**
     * Get providerReference.
     */
    public function getProviderReference(): ?string
    {
        return $this->providerReference;
    }

    /**
     * Set contentPath.
     */
    public function setContentPath(?string $contentPath): static
    {
        $this->contentPath = $contentPath;

        return $this;
    }

    /**
     * Get contentPath.
     */
    public function getContentPath(): string
    {
        return $this->contentPath;
    }

    /**
     * Set thumbnailPath.
     */
    public function setThumbnailPath(?string $thumbnailPath): static
    {
        $this->thumbnailPath = $thumbnailPath;

        return $this;
    }

    /**
     * Get thumbnailPath.
     */
    public function getThumbnailPath(): ?string
    {
        return $this->thumbnailPath;
    }

    /**
     * Set name.
     */
    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set description.
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set width.
     */
    public function setWidth(?int $width): static
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width.
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * Set height.
     */
    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height.
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * Set contentType.
     */
    public function setContentType(?string $contentType): static
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get contentType.
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * Set contentSize.
     */
    public function setContentSize(int $contentSize): static
    {
        $this->contentSize = $contentSize;

        return $this;
    }

    /**
     * Get contentSize.
     */
    public function getContentSize(): ?int
    {
        return $this->contentSize;
    }

    /**
     * Set isOrphan.
     */
    public function setIsOrphan(bool $isOrphan): static
    {
        $this->isOrphan = $isOrphan;

        return $this;
    }

    /**
     * Get isOrphan.
     */
    public function getIsOrphan(): bool
    {
        return $this->isOrphan;
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
     * Set token.
     */
    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token.
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Set position.
     */
    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * Set updatedAt.
     */
    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
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
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Add mediaMarkRemove.
     */
    public function addMediaMarkRemove(MediaMarkRemove $mediaMarkRemove): static
    {
        $this->mediaMarkRemoves->add($mediaMarkRemove);

        return $this;
    }

    /**
     * Remove mediaMarkRemove.
     */
    public function removeMediaMarkRemove(MediaMarkRemove $mediaMarkRemove): void
    {
        $this->mediaMarkRemoves->removeElement($mediaMarkRemove);
    }

    /**
     * @return Collection<int, MediaMarkRemove>
     */
    public function getMediaMarkRemoves(): Collection
    {
        return $this->mediaMarkRemoves;
    }

    /**
     * Add mediaMarkEdit.
     */
    public function addMediaMarkEdit(MediaMarkEdit $mediaMarkEdit): static
    {
        $this->mediaMarkEdits->add($mediaMarkEdit);

        return $this;
    }

    /**
     * Remove mediaMarkEdit.
     */
    public function removeMediaMarkEdit(MediaMarkEdit $mediaMarkEdit): void
    {
        $this->mediaMarkEdits->removeElement($mediaMarkEdit);
    }

    /**
     * @return Collection<int, MediaMarkEdit>
     */
    public function getMediaMarkEdits(): Collection
    {
        return $this->mediaMarkEdits;
    }
}
