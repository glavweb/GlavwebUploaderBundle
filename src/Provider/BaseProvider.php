<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Provider;

/**
 * Class BaseProvider.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class BaseProvider implements ProviderInterface
{
    protected bool $isParsed = false;

    protected ?string $name = null;

    protected ?string $description = null;

    protected ?string $providerReference = null;

    protected ?int $width = null;

    protected ?int $height = null;

    protected ?string $contentType = null;

    protected ?int $contentSize = null;

    protected ?string $thumbnailUrl = null;

    public function isParsed(): bool
    {
        return $this->isParsed;
    }

    public function setContentSize(?int $contentSize): void
    {
        $this->contentSize = $contentSize;
    }

    public function getContentSize(): int
    {
        return $this->contentSize;
    }

    public function setContentType(?string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setProviderReference(?string $providerReference): void
    {
        $this->providerReference = $providerReference;
    }

    public function getProviderReference(): ?string
    {
        return $this->providerReference;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setThumbnailUrl(?string $thumbnailUrl): void
    {
        $this->thumbnailUrl = $thumbnailUrl;
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }
}
