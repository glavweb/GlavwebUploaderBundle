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
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Glavweb\UploaderBundle\Response\ResponseInterface;

/**
 * Class PreUploadEvent
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class PreUploadEvent extends Event
{
    /**
     * @var FileInterface
     */
    protected $file;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $type;

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
     * @param ResponseInterface $response
     * @param Request           $request
     * @param string            $type
     * @param array             $config
     */
    public function __construct(FileInterface $file, ResponseInterface $response, Request $request, $type, array $config)
    {
        $this->file     = $file;
        $this->request  = $request;
        $this->response = $response;
        $this->type     = $type;
        $this->config   = $config;
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
    public function getType()
    {
        return $this->type;
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
