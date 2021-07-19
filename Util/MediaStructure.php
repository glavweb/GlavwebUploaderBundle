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
use Glavweb\UploaderBundle\Exception\Exception;
use Glavweb\UploaderBundle\Helper\MediaHelper;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Templating\Helper\FilterHelper;

/**
 * Class MediaStructure
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MediaStructure
{
    /**
     * @var MediaHelper
     */
    private $mediaHelper;

    /**
     * @var FilterHelper
     */
    private $cacheManager;

    /**
     * MediaStructure constructor.
     *
     * @param MediaHelper  $mediaHelper
     * @param CacheManager $cacheManager
     */
    public function __construct(MediaHelper $mediaHelper, CacheManager $cacheManager)
    {
        $this->mediaHelper  = $mediaHelper;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param array $medias
     * @param string $thumbnailFilter
     * @param bool $securedId
     * @param bool $isAbsolute
     * @return array
     */
    public function getStructure(array $medias, $thumbnailFilter = null, $securedId = false, $isAbsolute = false)
    {
        $structure = array_map(function (Media $media) use ($thumbnailFilter, $securedId, $isAbsolute) {
            return $this->getMediaStructure($media, $thumbnailFilter, $securedId, $isAbsolute);
        }, $medias);

        return $structure;
    }

    /**
     * @param MediaInterface $media
     * @param string $thumbnailFilter
     * @param bool $securedId
     * @param bool $isAbsolute
     * @return array
     * @throws Exception
     */
    public function getMediaStructure(MediaInterface $media, $thumbnailFilter = null, $securedId = false, $isAbsolute = true)
    {
        $contentPath   = $this->mediaHelper->getContentPath($media, $isAbsolute);

        $thumbnailPath = null;
        if ($media->getThumbnailPath()) {
            if ($thumbnailFilter) {
                $thumbnailPath = $this->mediaHelper->getThumbnailPath($media, false);
                $thumbnailPath = $this->cacheManager->getBrowserPath($thumbnailPath, $thumbnailFilter);

            } else {
                $thumbnailPath = $this->mediaHelper->getThumbnailPath($media, $isAbsolute);
            }
        }

        return [
            'id'                 => $securedId ? $this->getSecuredId($media) : $media->getId(),
            'name'               => $media->getName(),
            'description'        => $media->getDescription(),
            'thumbnail_path'     => $thumbnailPath,
            'content_path'       => $contentPath,
            'content_type'       => $media->getContentType(),
            'content_size'       => $media->getContentSize(),
            'width'              => $media->getWidth(),
            'height'             => $media->getHeight(),
            'provider_reference' => $media->getProviderReference(),
        ];
    }

    /**
     * @param MediaInterface $media
     * @return string
     */
    public function getSecuredId(MediaInterface $media)
    {
        return $media->getId() . '-' . $media->getToken();
    }
}