<?php

namespace Glavweb\UploaderBundle\EventListener;

use Glavweb\UploaderBundle\Event\ValidationEvent;
use Glavweb\UploaderBundle\Exception\ValidationException;

class DisallowedMimetypeValidationListener
{
    public function onValidate(ValidationEvent $event)
    {
        $config = $event->getConfig();
        $file   = $event->getFile();

        if (count($config['disallowed_mimetypes']) == 0) {
            return;
        }

        $mimetype = $file->getExtension();

        if (in_array($mimetype, $config['disallowed_mimetypes'])) {
            throw new ValidationException('error.blacklist');
        }
    }
}
