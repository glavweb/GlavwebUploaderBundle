<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Event;

use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Glavweb\UploaderBundle\Response\ResponseInterface;

/**
 * Class PostUploadEvent
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class PostUploadEvent extends Event
{
    /**
     * @var FileInterface
     */
    protected $file;

    /**
     * @var MediaInterface
     */
    protected $media;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param FileInterface     $file
     * @param MediaInterface    $media
     * @param ResponseInterface $response
     * @param Request           $request
     * @param string            $context
     * @param array             $config
     */
    public function __construct(FileInterface $file, MediaInterface $media, ResponseInterface $response, Request $request, $context, array $config)
    {
        $this->file     = $file;
        $this->media    = $media;
        $this->request  = $request;
        $this->response = $response;
        $this->context  = $context;
        $this->config   = $config;
    }

    /**
     * @return MediaInterface
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return FileInterface
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
