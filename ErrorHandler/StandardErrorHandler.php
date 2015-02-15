<?php

namespace Glavweb\UploaderBundle\ErrorHandler;

use Glavweb\UploaderBundle\Response\Response;

/**
 * Class StandardErrorHandler
 * @package Glavweb\UploaderBundle\ErrorHandler
 */
class StandardErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param Response   $response
     * @param \Exception $exception
     */
    public function addException(Response $response, \Exception $exception)
    {
        $message = $exception->getMessage();
        $response['error'] = $message;
    }
}
