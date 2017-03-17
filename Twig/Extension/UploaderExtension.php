<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Twig\Extension;

use Glavweb\UploaderBundle\Manager\UploaderManager;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Glavweb\UploaderBundle\Helper\MediaHelper;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class UploaderExtension
 * @package Glavweb\UploaderBundle\Twig\Extension
 */
class UploaderExtension extends \Twig_Extension
{
    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Glavweb\UploaderBundle\Helper\MediaHelper
     */
    protected $mediaHelper;

    /**
     * @var \Glavweb\UploaderBundle\Manager\UploaderManager
     */
    protected $uploaderManager;

    /**
     * @param RequestStack    $requestStack
     * @param MediaHelper     $mediaHelper
     * @param UploaderManager $uploaderManager
     */
    public function __construct(RequestStack $requestStack, MediaHelper $mediaHelper, UploaderManager $uploaderManager)
    {
        $this->request         = $requestStack->getCurrentRequest();
        $this->mediaHelper     = $mediaHelper;
        $this->uploaderManager = $uploaderManager;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'glavweb_uploader_extension';
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('glavweb_uploader_thumbnail', array($this, 'thumbnail')),
            new \Twig_SimpleFilter('glavweb_uploader_content_path', array($this, 'contentPath'))
        );
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('glavweb_uploader_render_files', array($this, 'renderFiles')),
            new \Twig_SimpleFunction('glavweb_uploader_display', array($this, 'display'), array('is_safe' => array('html')))
        );
    }

    /**
     * @param MediaInterface[] $medias
     * @return string
     */
    public function renderFiles(array $medias)
    {
        $request = $this->request;
        $medias = array_map(function(MediaInterface $media) {
            $context = $media->getContext();

            return array(
                'id'   => $media->getId(),
                'path' => $this->mediaHelper->getUploadDirectoryUrl($context)  . '/' . $media->getThumbnailPath(),
                'name' => $media->getName()
            );
        }, $medias);

        $output = '';
        $baseUrl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        foreach ($medias as $media) {
            $output .=' <div><a class="uploaded-file" target="_blank" rel="group_uploaded_file" href="' . $baseUrl . $media['path'] . '">' . $media['name'] . '</a></div>';
        }

        return $output;
    }

    /**
     * @param MediaInterface $media
     * @param bool           $isAbsolute
     * @return string
     */
    public function thumbnail(MediaInterface $media, $isAbsolute = false)
    {
        $thumbnailPath = $media->getThumbnailPath();
        if ($thumbnailPath) {
            $context = $media->getContext();
            return $this->mediaHelper->getUploadDirectoryUrl($context, $isAbsolute) . '/' . $thumbnailPath;
        }

        return null;
    }

    /**
     * @param MediaInterface $media
     * @param bool           $isAbsolute
     * @return string
     */
    public function contentPath(MediaInterface $media, $isAbsolute = false)
    {
        $contentPath = $media->getContentPath();
        if ($contentPath) {
            $context = $media->getContext();
            return $this->mediaHelper->getUploadDirectoryUrl($context, $isAbsolute) . '/' . $contentPath;
        }

        return null;
    }

    /**
     * @param MediaInterface $media
     * @param array          $options
     * @return string
     */
    public function display(MediaInterface $media, array $options = array())
    {
        $provider = $this->uploaderManager->getProviderByName($media->getProviderName());
        return $provider->display($media, $options);
    }
}