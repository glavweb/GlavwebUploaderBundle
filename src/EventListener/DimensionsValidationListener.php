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
 * Class DimensionsValidationListener.
 *
 * @author  Sergey Zvyagintsev <nitron.ru@gmail.com>
 */
class DimensionsValidationListener
{
    public function onValidate(ValidationEvent $event): void
    {
        $config = $event->getConfig();
        $file = $event->getFile();

        $minWidth = $config['width'] ? $config['width']['min'] : null;
        $maxWidth = $config['width'] ? $config['width']['max'] : null;
        $minHeight = $config['height'] ? $config['height']['min'] : null;
        $maxHeight = $config['height'] ? $config['height']['max'] : null;

        [$width, $height] = @getimagesize($file->getPathname());

        if ((\is_int($minWidth) && $width < $minWidth)
        || (\is_int($maxWidth) && $width > $maxWidth)
        || (\is_int($minHeight) && $height < $minHeight)
        || (\is_int($maxHeight) && $height > $maxHeight)
        ) {
            throw new ValidationException('error.dimension');
        }
    }
}
