<?php

namespace Glavweb\UploaderBundle\Provider;

use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\Helper\MediaHelper;
use Glavweb\UploaderBundle\Model\MediaInterface;

/**
 * Class FileProvider
 * @package Glavweb\UploaderBundle\Provider
 */
class FileProvider extends BaseFileProvider
{
    /**
     * @var \Glavweb\UploaderBundle\Helper\MediaHelper
     */
    protected $mediaHelper;

    /**
     * @param MediaHelper $mediaHelper
     */
    public function __construct(MediaHelper $mediaHelper)
    {
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return 'glavweb_uploader.provider.file';
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

        $this->setName($file->getClientOriginalName());
        $this->setProviderReference(null);
        $this->setContentSize($file->getSize());
        $this->setContentType($file->getMimeType());
        $this->setDescription(null);
        $this->setThumbnailUrl(null);
        $this->setHeight(null);
        $this->setWidth(null);

        $this->isParsed = true;
    }

    /**
     * @param FileInterface|string $link
     * @return bool
     */
    public function checkLink($link)
    {
        return $link instanceof FileInterface;
    }

    /**
     * @param MediaInterface $media
     * @param array $options
     * @return string
     */
    public function display(MediaInterface $media, array $options = array())
    {
        $options = array_merge(array(
            'link_attributes' => array(),
        ), $options);

        $uploadDir = $this->mediaHelper->getUploadDirectoryUrl($media->getContext(), true);
        $href = $uploadDir . $media->getContentPath();

        $attributes = '';
        foreach ($options['link_attributes'] as $attrKey => $attrValue) {
            $attributes .= ' ' . $attrKey . '="' . $attrValue . '"';
        }

        return '<a ' . $attributes . ' href="' . $href . '" title="' . $media->getName() . '">' . $media->getName() . '</a>';
    }
}