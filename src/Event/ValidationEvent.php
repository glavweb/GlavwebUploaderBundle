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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ValidationEvent.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ValidationEvent extends Event
{
    public function __construct(protected FileInterface $file, protected Request $request, protected array $config, protected string $type)
    {
    }

    public function getFile(): FileInterface
    {
        return $this->file;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
