<?php

namespace Glavweb\UploaderBundle\Response;

interface ResponseInterface
{
    /**
     * Transforms this object to an array of data
     *
     * @return array
     */
    public function assemble();
}
