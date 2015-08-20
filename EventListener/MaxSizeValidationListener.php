<?php

namespace Glavweb\UploaderBundle\EventListener;

use Glavweb\UploaderBundle\Event\ValidationEvent;
use Glavweb\UploaderBundle\Exception\ValidationException;

class MaxSizeValidationListener
{
    public function onValidate(ValidationEvent $event)
    {
        $config = $event->getConfig();
        $file   = $event->getFile();

        if ($file->getSize() > $config['max_size']*(1024*1024)) {
            throw new ValidationException('error.maxsize');
        }
    }
}
