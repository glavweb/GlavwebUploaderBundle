<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Entity\Listener;

use Doctrine\ORM\Mapping\PreRemove;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\Manager\UploaderManager;

/**
 * Class MediaListener.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
readonly class MediaListener
{
    public function __construct(protected UploaderManager $uploaderManager)
    {
    }

    #[PreRemove]
    public function preRemove(Media $media, LifecycleEventArgs $event): void
    {
        $this->uploaderManager->removeMediaFromStorage($media);
    }
}
