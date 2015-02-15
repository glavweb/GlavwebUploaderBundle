<?php

namespace Glavweb\UploaderBundle\EventListener;

use Glavweb\UploaderBundle\Event\ValidationEvent;
use Glavweb\UploaderBundle\Exception\ValidationException;

class AllowedMimetypeValidationListener
{
    public function onValidate(ValidationEvent $event)
    {
        $config = $event->getConfig();
        $file   = $event->getFile();

        if (count($config['allowed_mimetypes']) == 0) {
            return;
        }

        $mimetype = $file->getMimeType();

        if (!in_array($mimetype, $config['allowed_mimetypes'])) {
            throw new ValidationException('error.whitelist');
        }
    }
}
