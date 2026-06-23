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
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class StandardErrorHandler.
 *
 * @author  Andrey Nilov <nilov@glavweb.ru>
 */
readonly class StandardErrorHandler implements ErrorHandlerInterface
{
    /**
     * StandardErrorHandler constructor.
     */
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function addException(Response $response, \Exception $exception): void
    {
        if ($exception instanceof TranslatableInterface) {
            $message = $exception->trans($this->translator);
        } else {
            $message = $exception->getMessage();
        }

        $response['error'] = $message;
    }
}
