<?php

namespace Glavweb\UploaderBundle\Model;

/**
 * Class ModelManagerInterface
 * @package Glavweb\UploaderBundle\Model
 */
interface ModelManagerInterface
{
    /**
     * Creates an empty media instance.
     *
     * @return MediaInterface
     */
    public function createMedia();

    /**
     * Updates a media.
     *
     * @param MediaInterface $file
     * @return void
     */
    public function updateMedia(MediaInterface $file);

    /**
     * Deletes a media.
     *
     * @param MediaInterface $file
     * @return void
     */
    public function deleteMedia(MediaInterface $file);
}