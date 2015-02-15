<?php

namespace Glavweb\UploaderBundle\ErrorHandler;

use Glavweb\UploaderBundle\Response\Response;

/**
 * Interface ErrorHandlerInterface
 * @package Glavweb\UploaderBundle\ErrorHandler
 */
interface ErrorHandlerInterface
{
    /**
     * Adds an exception to a given response
     *
     * @param Response   $response
     * @param \Exception $exception
     *
     * @return void
     */
    public function addException(Response $response, \Exception $exception);
}
