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
 * Class AllowedMimetypeValidationListener.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class AllowedMimetypeValidationListener
{
    public function onValidate(ValidationEvent $event): void
    {
        $config = $event->getConfig();
        $file = $event->getFile();

        if (0 === \count($config['allowed_mimetypes'])) {
            return;
        }

        $mimeType = $file->getMimeType();

        if (!\in_array($mimeType, $config['allowed_mimetypes'])) {
            throw new ValidationException('error.invalid_type');
        }
    }
}
