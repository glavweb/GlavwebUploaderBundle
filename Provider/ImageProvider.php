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

use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\Model\MediaInterface;

/**
 * Class ImageProvider
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ImageProvider extends FileProvider
{
    /**
     * @return string
     */
    public function getProviderName()
    {
        return 'glavweb_uploader.provider.image';
    }

    /**
     * @param FileInterface $link
     * @throws \RuntimeException
     */
    public function parse($link)
    {
        if (!$link instanceof FileInterface) {
            throw new \RuntimeException('$link must be FileInterface.');
        }

        $file = $link;
        $this->file = $file;

        list($width, $height) = @getimagesize($file->getPathname());

        $this->setName($file->getClientOriginalName());
        $this->setProviderReference(null);
        $this->setContentSize($file->getSize());
        $this->setContentType($file->getMimeType());
        $this->setHeight($height);
        $this->setWidth($width);
        $this->setDescription(null);
        $this->setThumbnailUrl(null);

        $this->isParsed = true;
    }

    /**
     * @param FileInterface|string $link
     * @return bool
     */
    public function checkLink($link)
    {
        return $link instanceof FileInterface && @getimagesize($link->getPathname());
    }

    /**
     * @param MediaInterface $media
     * @param array $options
     * @return string
     */
    public function display(MediaInterface $media, array $options = array())
    {
        $options = array_merge(array(
            'thumbnail_content' => true,
            'use_link'          => true,
            'link_attributes'   => array(),
            'use_filter'        => false,
            'filter_name'       => null,
        ), $options);

        $uploadDir     = $this->mediaHelper->getUploadDirectoryUrl($media->getContext(), false);
        $thumbnailPath = $uploadDir . '/' . $media->getThumbnailPath();
        $contentPath   = $uploadDir . '/' . $media->getContentPath();

        $src = $options['thumbnail_content'] ? $thumbnailPath : $contentPath;
        $imgTag = '<img src="' . $src . '">';

        if ($options['use_link']) {
            $href = $contentPath;

            $attributes = '';
            foreach ($options['link_attributes'] as $attrKey => $attrValue) {
                $attributes .= ' ' . $attrKey . '="' . $attrValue . '"';
            }

            return '<a ' . $attributes . ' href="' . $href . '" title="' . $media->getName() . '">' . $imgTag . '</a>';
        }

        return $imgTag;
    }
}