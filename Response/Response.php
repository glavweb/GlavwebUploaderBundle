<?php

namespace Glavweb\UploaderBundle\Response;

/**
 * Class Response
 * @package Glavweb\UploaderBundle\Response
 */
class Response implements \ArrayAccess, ResponseInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->data = array();
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        is_null($offset) ? $this->data[] = $value : $this->data[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * @return array
     */
    public function assemble()
    {
        return $this->data;
    }
}
