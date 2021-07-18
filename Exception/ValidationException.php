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
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * Class ValidationException
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ValidationException extends UploadException implements TranslatableInterface
{
    /**
     * @var array
     */
    protected $details;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * ValidationException constructor.
     *
     * @param string         $message
     * @param array          $details
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $details = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->details = $details;
    }


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

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans($this->getMessage(), $this->details, null, $locale);
    }
}
