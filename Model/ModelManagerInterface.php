<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Model;

/**
 * Interface ModelManagerInterface
 *
 * @package Glavweb\UploaderBundle\Model
 * @author Andrey Nilov <nilov@glavweb.ru>
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

    /**
     * @param int $id Media ID
     * @return MediaInterface
     */
    public function findMedia($id);

    /**
     * @param int  $lifetime
     */
    public function removeOrphans($lifetime);

    /**
     * @param string  $requestId
     */
    public function removeMarkedMedia($requestId);

    /**
     * @param string  $requestId
     */
    public function renameMarkedMedia($requestId);

    /**
     * Returns array of file entities
     *
     * @param string $requestId
     * @return array
     */
    public function findOrphans($requestId);

    /**
     * @param string $securedId
     * @return MediaInterface|null
     */
    public function findOneBySecuredId($securedId);

    /**
     * @param MediaInterface[] $medias    Array of media entities
     * @param array            $positions Array of positions medias like as [mediaId, mediaId,  ...]
     */
    public function sortMedias(array $medias, $positions);

    /**
     * @param MediaInterface $media
     * @return bool
     */
    public function removeMedia(MediaInterface $media);

    /**
     * @param MediaInterface $media
     * @param string         $requestId
     * @return bool
     */
    public function markRemove(MediaInterface $media, $requestId);

    /**
     * @param MediaInterface $media
     * @param string         $name
     * @param string         $description
     * @return bool
     */
    public function editMedia(MediaInterface $media, $name, $description);

    /**
     * @param MediaInterface $media
     * @param string         $requestId
     * @param string         $name
     * @param string         $description
     * @return bool
     */
    public function markEdit(MediaInterface $media, $requestId, $name, $description);
}