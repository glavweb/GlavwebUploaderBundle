<?php

namespace Glavweb\UploaderBundle\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\Manager\UploaderManager;

/**
 * Class MediaListener
 * @package Glavweb\UploaderBundle\Entity\Listener
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
     * @param Media               $media
     * @param LifecycleEventArgs $event
     */
    public function postRemoveHandler(Media $media, LifecycleEventArgs $event)
    {
        $this->uploaderManager->removeMediaFromStorage($media);
    }
}