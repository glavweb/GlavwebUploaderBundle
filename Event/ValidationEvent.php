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
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ValidationEvent
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ValidationEvent extends Event
{
    /**
     * @var FileInterface
     */
    protected $file;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param FileInterface $file
     * @param Request       $request
     * @param array         $config
     * @param string        $type
     */
    public function __construct(FileInterface $file, Request $request, array $config, $type)
    {
        $this->file = $file;
        $this->config = $config;
        $this->type = $type;
        $this->request = $request;
    }

    /**
     * @return FileInterface
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
