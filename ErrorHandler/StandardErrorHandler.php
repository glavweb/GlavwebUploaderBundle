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
 * Class StandardErrorHandler
 *
 * @package Glavweb\UploaderBundle
 * @author  Andrey Nilov <nilov@glavweb.ru>
 */
class StandardErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * StandardErrorHandler constructor.
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Response   $response
     * @param \Exception $exception
     */
    public function addException(Response $response, \Exception $exception)
    {
        if ($exception instanceof TranslatableInterface) {
            $message = $exception->trans($this->translator);
        } else {
            $message = $exception->getMessage();
        }

        $response['error'] = $message;
    }
}
