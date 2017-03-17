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
 * Interface ErrorHandlerInterface
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
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
