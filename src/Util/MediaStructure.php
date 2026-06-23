<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Util;

use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\Helper\MediaHelper;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * Class MediaStructure.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MediaStructure
{
    /**
     * MediaStructure constructor.
     */
    public function __construct(private readonly MediaHelper $mediaHelper, private readonly CacheManager $cacheManager)
    {
    }

    /**
     * @param Media[] $medias
     *
     * @return mixed[][]
     */
    public function getStructure(array $medias, ?string $thumbnailFilter = null, bool $securedId = false, bool $isAbsolute = false): array
    {
        return array_map(fn (Media $media): array => $this->getMediaStructure($media, $thumbnailFilter, $securedId, $isAbsolute), $medias);
    }

    /**
     * @return array<string, int|string|null>
     */
    public function getMediaStructure(
        MediaInterface $media,
        ?string $thumbnailFilter = null,
        bool $securedId = false,
        bool $isAbsolute = true
    ): array {
        $contentPath = $this->mediaHelper->getContentPath($media, $isAbsolute);

        $thumbnailPath = null;
        if ($media->getThumbnailPath()) {
            if ($thumbnailFilter) {
                $thumbnailPath = $this->mediaHelper->getThumbnailPath($media);
                $thumbnailPath = $this->cacheManager->getBrowserPath($thumbnailPath, $thumbnailFilter);
            } else {
                $thumbnailPath = $this->mediaHelper->getThumbnailPath($media, $isAbsolute);
            }
        }

        return [
            'id' => $securedId ? $this->getSecuredId($media) : $media->getId(),
            'name' => $media->getName(),
            'description' => $media->getDescription(),
            'thumbnail_path' => $thumbnailPath,
            'content_path' => $contentPath,
            'content_type' => $media->getContentType(),
            'content_size' => $media->getContentSize(),
            'width' => $media->getWidth(),
            'height' => $media->getHeight(),
            'provider_reference' => $media->getProviderReference(),
        ];
    }

    public function getSecuredId(MediaInterface $media): string
    {
        return $media->getId().'-'.$media->getToken();
    }
}
