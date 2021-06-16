<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Helper;

use Glavweb\UploaderBundle\Manager\UploaderManager;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class MediaHelper
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MediaHelper
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var UploaderManager
     */
    private $uploaderManager;

    /**
     * @param array $config
     * @param RequestStack $requestStack
     */
    public function __construct(array $config, RequestStack $requestStack, UploaderManager $uploaderManager)
    {
        $this->config          = $config;
        $this->request         = $requestStack->getCurrentRequest();
        $this->uploaderManager = $uploaderManager;
    }

    /**
     * @param string $context
     * @param bool   $isAbsolute
     * @return string
     */
    public function getUploadDirectoryUrl($context, $isAbsolute = false)
    {
        $uploadDirectoryUrl = '/' . $this->uploaderManager->getContextConfig($context, 'upload_directory_url');
        if ($isAbsolute) {
            return $this->getAbsoluteUri($uploadDirectoryUrl);
        }

        return $uploadDirectoryUrl;
    }

    /**
     * Takes a URI and converts it to absolute if it is not already absolute.
     *
     * @param string $uri A URI
     * @return string An absolute URI
     */
    public function getAbsoluteUri($uri)
    {
        if (isset($this->config['base_url'])) {
            return $this->config['base_url'] . $uri;
        }
        // already absolute?
        if (0 === strpos($uri, 'http://') || 0 === strpos($uri, 'https://')) {
            return $uri;
        }

        $isHosts  = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? true : false;
        $httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $currentUri = sprintf('http%s://%s/', $isHosts ? 's' : '', $httpHost);

        // protocol relative URL
        if (0 === strpos($uri, '//')) {
            return parse_url($currentUri, PHP_URL_SCHEME) . ':' . $uri;
        }

        // anchor?
        if (!$uri || '#' == $uri[0]) {
            return preg_replace('/#.*?$/', '', $currentUri) . $uri;
        }

        if ('/' !== $uri[0]) {
            $path = parse_url($currentUri, PHP_URL_PATH);

            if ('/' !== substr($path, -1)) {
                $path = substr($path, 0, strrpos($path, '/') + 1);
            }

            $uri = $path . $uri;
        }

        return preg_replace('#^(.*?//[^/]+)\/.*$#', '$1', $currentUri) . $uri;
    }

    /**
     * @param MediaInterface $media
     * @param bool           $isAbsolute
     * @return string
     */
    public function getContentPath($media, $isAbsolute = false)
    {
        $context     = $media->getContext();
        $contentPath = $this->getUploadDirectoryUrl($context, $isAbsolute) . '/' . $media->getContentPath();

        return $contentPath;
    }

    /**
     * @param MediaInterface $media
     * @param bool           $isAbsolute
     * @return string
     */
    public function getThumbnailPath($media, $isAbsolute = false)
    {
        $context     = $media->getContext();
        $contentPath = $this->getUploadDirectoryUrl($context, $isAbsolute) . '/' . $media->getThumbnailPath();

        return $contentPath;
    }
}