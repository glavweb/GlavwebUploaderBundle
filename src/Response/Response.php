<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Response;

/**
 * Class Response.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class Response implements \ArrayAccess, ResponseInterface
{
    /**
     * @var mixed[]
     */
    protected array $data = [];

    public function offsetSet(mixed $offset, mixed $value): void
    {
        null === $offset ? $this->data[] = $value : $this->data[$offset] = $value;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * @return mixed|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * @return mixed[]
     */
    public function assemble(): array
    {
        return $this->data;
    }
}
