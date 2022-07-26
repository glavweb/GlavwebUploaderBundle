<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\EventListener;

use Glavweb\UploaderBundle\Event\ValidationEvent;
use Glavweb\UploaderBundle\Exception\ValidationException;

/**
 * Class MaxSizeValidationListener
 *
 * @package Glavweb\UploaderBundle\EventListener
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MaxSizeValidationListener
{
    /**
     * @param ValidationEvent $event
     */
    public function onValidate(ValidationEvent $event)
    {
        $config = $event->getConfig();
        $file   = $event->getFile();

        if ($file->getSize() > $config['max_size']) {
            throw new ValidationException(
                'error.maxsize', ['filesize' => $file->getSize(), 'maxFilesize' => $config['max_size']]
            );
        }
    }
}
