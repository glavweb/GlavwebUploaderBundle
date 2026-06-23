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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class MediaHelper.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MediaHelper
{
    protected ?Request $request;

    public function __construct(protected array $config, RequestStack $requestStack, protected UploaderManager $uploaderManager)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getUploadDirectoryUrl(string $context, bool $isAbsolute = false): string
    {
        $uploadDirectoryUrl = '/'.$this->uploaderManager->getContextConfig($context, 'upload_directory_url');
        if ($isAbsolute) {
            return $this->getAbsoluteUri($uploadDirectoryUrl);
        }

        return $uploadDirectoryUrl;
    }

    /**
     * Takes a URI and converts it to absolute if it is not already absolute.
     *
     * @param string $uri A URI
     *
     * @return string An absolute URI
     */
    public function getAbsoluteUri(string $uri): string
    {
        if (isset($this->config['base_url'])) {
            return $this->config['base_url'].$uri;
        }

        // already absolute?
        if (str_starts_with($uri, 'http://') || str_starts_with($uri, 'https://')) {
            return $uri;
        }

        $isHosts = isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'];
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $currentUri = \sprintf('http%s://%s/', $isHosts ? 's' : '', $httpHost);

        // protocol relative URL
        if (str_starts_with($uri, '//')) {
            return parse_url($currentUri, \PHP_URL_SCHEME).':'.$uri;
        }

        // anchor?
        if (!$uri || '#' == $uri[0]) {
            return preg_replace('/#.*?$/', '', $currentUri).$uri;
        }

        if ('/' !== $uri[0]) {
            $path = parse_url($currentUri, \PHP_URL_PATH);

            if (!str_ends_with($path, '/')) {
                $path = substr($path, 0, strrpos($path, '/') + 1);
            }

            $uri = $path.$uri;
        }

        return preg_replace('#^(.*?//[^/]+)\/.*$#', '$1', $currentUri).$uri;
    }

    public function getContentPath(MediaInterface $media, bool $isAbsolute = false): string
    {
        $context = $media->getContext();

        return $this->getUploadDirectoryUrl($context, $isAbsolute).'/'.$media->getContentPath();
    }

    public function getThumbnailPath(MediaInterface $media, bool $isAbsolute = false): string
    {
        $context = $media->getContext();

        return $this->getUploadDirectoryUrl($context, $isAbsolute).'/'.$media->getThumbnailPath();
    }
}
