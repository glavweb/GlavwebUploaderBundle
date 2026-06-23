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
 * Interface ModelManagerInterface.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
interface ModelManagerInterface
{
    /**
     * Creates an empty media instance.
     */
    public function createMedia(): MediaInterface;

    /**
     * Updates a media.
     */
    public function updateMedia(MediaInterface $file): void;

    /**
     * Deletes a media.
     */
    public function deleteMedia(MediaInterface $file): void;

    /**
     * @param int $id Media ID
     */
    public function findMedia(int $id): ?MediaInterface;

    public function removeOrphans(int $lifetime);

    public function removeMarkedMedia(string $requestId);

    public function renameMarkedMedia(string $requestId);

    /**
     * Returns array of file entities.
     */
    public function findOrphans(string $requestId): array;

    public function findOneBySecuredId(string $securedId): ?MediaInterface;

    /**
     * @param MediaInterface[] $medias    Array of media entities
     * @param array            $positions Array of positions medias like as [mediaId, mediaId,  ...]
     */
    public function sortMedias(array $medias, array $positions);

    public function removeMedia(MediaInterface $media): bool;

    public function markRemove(MediaInterface $media, string $requestId): bool;

    public function editMedia(MediaInterface $media, string $name, string $description): bool;

    public function markEdit(MediaInterface $media, string $requestId, string $name, string $description): bool;
}
