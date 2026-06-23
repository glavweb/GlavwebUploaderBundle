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
use Glavweb\UploaderBundle\Helper\MediaHelper;
use Glavweb\UploaderBundle\Model\MediaInterface;

/**
 * Class FileProvider.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FileProvider extends BaseFileProvider
{
    public function __construct(protected readonly MediaHelper $mediaHelper)
    {
    }

    public function getProviderName(): string
    {
        return 'glavweb_uploader.provider.file';
    }

    /**
     * @throws \RuntimeException
     */
    public function parse(FileInterface|string $link): void
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

    public function checkLink(FileInterface|string $link): bool
    {
        return $link instanceof FileInterface;
    }

    public function display(MediaInterface $media, array $options = []): string
    {
        $options = array_merge([
            'link_attributes' => [],
        ], $options);

        $uploadDir = $this->mediaHelper->getUploadDirectoryUrl($media->getContext(), true);
        $href = $uploadDir.$media->getContentPath();

        $attributes = '';
        foreach ($options['link_attributes'] as $attrKey => $attrValue) {
            $attributes .= ' '.$attrKey.'="'.$attrValue.'"';
        }

        return '<a '.$attributes.' href="'.$href.'" title="'.$media->getName().'">'.$media->getName().'</a>';
    }
}
