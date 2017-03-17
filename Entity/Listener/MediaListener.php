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

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\PreRemove;
use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\Manager\UploaderManager;

/**
 * Class MediaListener
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MediaListener
{
    /**
     * @var \Glavweb\UploaderBundle\Manager\UploaderManager
     */
    protected $uploaderManager;

    /**
     * @param UploaderManager $uploaderManager
     */
    public function __construct(UploaderManager $uploaderManager)
    {
        $this->uploaderManager = $uploaderManager;
    }

    /**
     * @PreRemove
     *
     * @param Media              $media
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Media $media, LifecycleEventArgs $event)
    {
        $this->uploaderManager->removeMediaFromStorage($media);
    }
}