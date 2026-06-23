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
 * Class YoutubeProvider.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class YoutubeProvider extends BaseLinkProvider
{
    public function getProviderName(): string
    {
        return 'glavweb_uploader.provider.youtube';
    }

    /**
     * @throws \RuntimeException
     */
    public function parse(FileInterface|string $link): void
    {
        $providerReference = $this->getProviderReferenceByUrl($link);

        if (!$providerReference) {
            throw new \RuntimeException('The provider reference not found in URL "'.$link.'"');
        }

        $providerMetadata = $this->getMetadataByReference($providerReference);

        $this->setName($providerMetadata['title']);
        $this->setProviderReference($providerReference);
        $this->setContentSize(null);
        $this->setContentType('video/x-flv');
        $this->setDescription(null);
        $this->setThumbnailUrl($providerMetadata['thumbnail_url']);
        $this->setHeight($providerMetadata['height']);
        $this->setWidth($providerMetadata['width']);

        $this->isParsed = true;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    protected function getMetadataByReference(string $providerReference): array
    {
        $apiUrl = \sprintf('https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=%s&format=json', $providerReference);

        try {
            $response = file_get_contents($apiUrl);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to retrieve the video information for :'.$apiUrl, null, $e);
        }

        $metadata = json_decode($response, true);
        if (!$metadata) {
            throw new \RuntimeException('Unable to decode the video information for :'.$apiUrl);
        }

        return [
            'title' => $metadata['title'],
            'thumbnail_url' => $metadata['thumbnail_url'],
            'height' => $metadata['height'],
            'width' => $metadata['width'],
        ];
    }

    public function getProviderReferenceByUrl(string $url): ?string
    {
        if (preg_match("/(?<=v(\=|\/))([-a-zA-Z0-9_]+)|(?<=youtu\.be\/)([-a-zA-Z0-9_]+)/", $url, $matches)) {
            return $matches[0];
        }

        return null;
    }

    public function checkLink(FileInterface|string $link): bool
    {
        return null !== $this->getProviderReferenceByUrl($link);
    }

    public function display(MediaInterface $media, array $options = []): string
    {
        $options = array_merge([
            'width' => $media->getWidth(),
            'height' => $media->getHeight(),
        ], $options);

        return sprintf(
            '<iframe width="%s" height="%s" src="//www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe>',
            $options['width'],
            $options['height'],
            $media->getProviderReference()
        );
    }
}
