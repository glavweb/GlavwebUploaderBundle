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
 * Class YoutubeProvider
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class YoutubeProvider extends BaseLinkProvider
{
    /**
     * @return string
     */
    public function getProviderName()
    {
        return 'glavweb_uploader.provider.youtube';
    }

    /**
     * @param string $url
     * @throws \RuntimeException
     */
    public function parse($url)
    {
        $providerReference = $this->getProviderReferenceByUrl($url);

        if (!$providerReference) {
            throw new \RuntimeException('The provider reference not found in URL "' . $url . '"');
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
     * @param string $providerReference
     * @return mixed
     * @throws \RuntimeException
     */
    protected function getMetadataByReference($providerReference)
    {
        $apiUrl = sprintf('http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=%s&format=json', $providerReference);

        try {
            $response = file_get_contents($apiUrl);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to retrieve the video information for :' . $apiUrl, null, $e);
        }

        $metadata = json_decode($response, true);
        if (!$metadata) {
            throw new \RuntimeException('Unable to decode the video information for :' . $apiUrl);
        }

        return array(
            'title'         => $metadata['title'],
            'thumbnail_url' => $metadata['thumbnail_url'],
            'height'        => $metadata['height'],
            'width'         => $metadata['width']
        );
    }

    /**
     * @param string $url
     * @return string
     */
    public function getProviderReferenceByUrl($url)
    {
        if (preg_match("/(?<=v(\=|\/))([-a-zA-Z0-9_]+)|(?<=youtu\.be\/)([-a-zA-Z0-9_]+)/", $url, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * @param FileInterface|string $link
     * @return bool
     */
    public function checkLink($link)
    {
        return $this->getProviderReferenceByUrl($link) !== null;
    }

    /**
     * @param MediaInterface $media
     * @param array $options
     * @return string
     */
    public function display(MediaInterface $media, array $options = array())
    {
        $options = array_merge(array(
            'width'  => $media->getWidth(),
            'height' => $media->getHeight()
        ), $options);

        return '<iframe width="' . $options['width'] . '" height="' . $options['height'] . '" src="//www.youtube.com/embed/' .$media->getProviderReference() . '" frameborder="0" allowfullscreen></iframe>';
    }
}