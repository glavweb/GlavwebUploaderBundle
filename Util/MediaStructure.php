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
use Liip\ImagineBundle\Templating\Helper\ImagineHelper;

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
     * @var ImagineHelper
     */
    private $imagineHelper;

    /**
     * MediaStructure constructor.
     *
     * @param MediaHelper $mediaHelper
     */
    public function __construct(MediaHelper $mediaHelper)
    {
        $this->mediaHelper   = $mediaHelper;
    }

    /**
     * @param ImagineHelper $imagineHelper
     */
    public function setImagineHelper($imagineHelper)
    {
        $this->imagineHelper = $imagineHelper;
    }

    /**
     * @param array  $medias
     * @param string $thumbnailFilter
     * @param bool   $securedId
     * @return array
     */
    public function getStructure(array $medias, $thumbnailFilter = null, $securedId = false)
    {
        $structure = array_map(function (Media $media) use ($thumbnailFilter, $securedId) {
            return $this->getMediaStructure($media, $thumbnailFilter, $securedId);
        }, $medias);

        return $structure;
    }

    /**
     * @param MediaInterface $media
     * @param string         $thumbnailFilter
     * @param bool           $securedId
     * @return array
     * @throws Exception
     */
    public function getMediaStructure(MediaInterface $media, $thumbnailFilter = null, $securedId = false)
    {
        $thumbnailPath = $this->mediaHelper->getThumbnailPath($media);
        $contentPath   = $this->mediaHelper->getContentPath($media);

        if ($thumbnailFilter) {
            if (!$this->imagineHelper instanceof ImagineHelper) {
                throw new Exception('ImagineHelper is not defined. You need use Liip\ImagineBundle.');
            }

            $thumbnailPath = $this->imagineHelper->filter($thumbnailPath, $thumbnailFilter);
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