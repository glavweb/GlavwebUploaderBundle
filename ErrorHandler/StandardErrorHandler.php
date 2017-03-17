<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\ErrorHandler;

use Glavweb\UploaderBundle\Response\Response;

/**
 * Class StandardErrorHandler
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
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
