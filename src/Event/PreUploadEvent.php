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
use Glavweb\UploaderBundle\Response\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class PreUploadEvent.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class PreUploadEvent extends Event
{
    public function __construct(
        protected FileInterface $file,
        protected ResponseInterface $response,
        protected Request $request,
        protected string $type,
        protected array $config,
    ) {
    }

    public function getFile(): FileInterface
    {
        return $this->file;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
