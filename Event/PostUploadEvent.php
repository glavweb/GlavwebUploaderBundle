<?php

namespace Glavweb\UploaderBundle\Event;

use Glavweb\UploaderBundle\File\FileInterface;
use Glavweb\UploaderBundle\Model\MediaInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Glavweb\UploaderBundle\Response\ResponseInterface;

/**
 * Class PostUploadEvent
 * @package Glavweb\UploaderBundle\Event
 */
class PostUploadEvent extends Event
{
    protected $file;
    protected $media;
    protected $request;
    protected $context;
    protected $response;
    protected $config;

    /**
     * @param FileInterface      $file
     * @param MediaInterface $media
     * @param ResponseInterface  $response
     * @param Request            $request
     * @param string             $context
     * @param array              $config
     */
    public function __construct(FileInterface $file, MediaInterface $media, ResponseInterface $response, Request $request, $context, array $config)
    {
        $this->file      = $file;
        $this->media = $media;
        $this->request   = $request;
        $this->response  = $response;
        $this->context   = $context;
        $this->config    = $config;
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
