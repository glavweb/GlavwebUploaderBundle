<?php

namespace Glavweb\UploaderBundle\Helper;

use Glavweb\UploaderBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class MediaHelper
 * @package Glavweb\UploaderBundle\Helper
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
     * @param array $config
     * @param RequestStack $requestStack
     */
    public function __construct(array $config, RequestStack $requestStack)
    {
        $this->config  = $config;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param MediaInterface $media
     * @param bool           $isAbsolute
     * @return string
     */
    public function getUploadDirectoryUrl($context, $isAbsolute = false)
    {
        $request = $this->request;

        $basePath = '/';
        if ($isAbsolute) {
            $basePath = $request->getScheme() . '://'
                . $request->getHttpHost()
                . $request->getBasePath() . '/';
        }

        return $basePath . $this->getContextConfig($context, 'upload_directory_url');
    }

    /**
     * @param string $context
     * @param string $option
     * @return mixed
     * @throws \RuntimeException
     */
    protected function getContextConfig($context, $option = null)
    {
        if (!isset($this->config['mappings'][$context])) {
            throw new \RuntimeException('Context "' . $context . '" not defined.');
        }
        $contextConfig = $this->config['mappings'][$context];

        if ($option) {
            return $contextConfig[$option];
        }
        return $contextConfig;
    }
}