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
 * Class ImageProvider.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ImageProvider extends FileProvider
{
    #[\Override]
    public function getProviderName(): string
    {
        return 'glavweb_uploader.provider.image';
    }

    /**
     * @throws \RuntimeException
     */
    #[\Override]
    public function parse(FileInterface|string $link): void
    {
        if (!$link instanceof FileInterface) {
            throw new \RuntimeException('$link must be FileInterface.');
        }

        $file = $link;
        $this->file = $file;

        [$width, $height] = @getimagesize($file->getPathname());

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

    #[\Override]
    public function checkLink(FileInterface|string $link): bool
    {
        return $link instanceof FileInterface && (@getimagesize($link->getPathname()) || $this->isSvgFile($link));
    }

    #[\Override]
    public function display(MediaInterface $media, array $options = []): string
    {
        $options = array_merge([
            'thumbnail_content' => true,
            'use_link' => true,
            'link_attributes' => [],
            'image_attributes' => [],
            'use_filter' => false,
            'filter_name' => null,
        ], $options);

        $uploadDir = $this->mediaHelper->getUploadDirectoryUrl($media->getContext());
        $thumbnailPath = $uploadDir.'/'.$media->getThumbnailPath();
        $contentPath = $uploadDir.'/'.$media->getContentPath();

        $src = $options['thumbnail_content'] ? $thumbnailPath : $contentPath;
        $imageAttributes = '';
        foreach ($options['image_attributes'] as $attrKey => $attrValue) {
            $imageAttributes .= ' '.$attrKey.'="'.$attrValue.'"';
        }

        $imgTag = '<img '.$imageAttributes.' src="'.$src.'">';

        if ($options['use_link']) {
            $href = $contentPath;

            $attributes = '';
            foreach ($options['link_attributes'] as $attrKey => $attrValue) {
                $attributes .= ' '.$attrKey.'="'.$attrValue.'"';
            }

            return '<a '.$attributes.' href="'.$href.'" title="'.$media->getName().'">'.$imgTag.'</a>';
        }

        return $imgTag;
    }

    private function isSvgFile(FileInterface $file): bool
    {
        return 'image/svg+xml' === $file->getMimeType();
    }
}
