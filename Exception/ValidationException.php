<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Exception;

use Symfony\Component\HttpFoundation\File\Exception\UploadException;

/**
 * Class ValidationException
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ValidationException extends UploadException
{
    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @param string $message
     * @return $this
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        // if no error message is set, return the exception message
        if (!$this->errorMessage) {
            return $this->getMessage();
        }

        return $this->errorMessage;
    }
}
