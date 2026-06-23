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

/**
 * Class ValidationException.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ValidationException extends UploadException implements TranslatableInterface
{
    protected ?string $errorMessage = null;

    /**
     * ValidationException constructor.
     */
    public function __construct(string $message = '', protected array $details = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return $this
     */
    public function setErrorMessage(string $message): static
    {
        $this->errorMessage = $message;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        // if no error message is set, return the exception message
        if (!$this->errorMessage) {
            return $this->getMessage();
        }

        return $this->errorMessage;
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->getMessage(), $this->details, null, $locale);
    }
}
