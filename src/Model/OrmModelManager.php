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

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\NonUniqueResultException;
use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\Entity\MediaMarkEdit;
use Glavweb\UploaderBundle\Entity\MediaMarkRemove;
use Glavweb\UploaderBundle\Entity\Repository\MediaRepository;

/**
 * Class OrmModelManager.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class OrmModelManager implements ModelManagerInterface
{
    private array $cachedMedias = [];

    public function __construct(protected Registry $doctrine)
    {
    }

    /**
     * Creates an empty media instance.
     */
    public function createMedia(): MediaInterface
    {
        return new Media();
    }

    public function editMedia(MediaInterface $media, string $name, string $description): bool
    {
        $em = $this->doctrine->getManager();
        $media->setName($name);
        $media->setDescription($description);

        $em->flush();

        return true;
    }

    /**
     * Updates a media.
     *
     * @param bool $andFlush Whether to flush the changes (default true)
     */
    public function updateMedia(MediaInterface $file, bool $andFlush = true): void
    {
        $em = $this->doctrine->getManager();
        $em->persist($file);

        if ($andFlush) {
            $em->flush();
        }
    }

    public function removeMedia(MediaInterface $media): bool
    {
        $em = $this->doctrine->getManager();

        $em->remove($media);
        $em->flush();

        return true;
    }

    public function markRemove(MediaInterface $media, string $requestId): bool
    {
        $em = $this->doctrine->getManager();

        /* @var Media $media */
        $media->setRequestId($requestId);

        $mediaMarkRemove = new MediaMarkRemove();
        $mediaMarkRemove->setRequestId($requestId);
        $mediaMarkRemove->setMedia($media);

        $em->persist($mediaMarkRemove);
        $em->flush();

        return true;
    }

    public function markEdit(MediaInterface $media, string $requestId, string $name, string $description): bool
    {
        /** @var Media $media */
        $em = $this->doctrine->getManager();

        $mediaMarkEdit = new MediaMarkEdit();
        $mediaMarkEdit->setRequestId($requestId);
        $mediaMarkEdit->setNewName($name);
        $mediaMarkEdit->setNewDescription($description);
        $mediaMarkEdit->setMedia($media);

        $em->persist($mediaMarkEdit);
        $em->flush();

        return true;
    }

    /**
     * Deletes a media.
     *
     * @param bool $andFlush Whether to flush the changes (default true)
     */
    public function deleteMedia(MediaInterface $file, bool $andFlush = true): void
    {
        $em = $this->doctrine->getManager();
        $em->remove($file);

        if ($andFlush) {
            $em->flush();
        }
    }

    /**
     * @param int $id Media ID
     */
    public function findMedia(int $id): ?MediaInterface
    {
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository(Media::class);

        return $repository->find($id);
    }

    public function removeOrphans(int $lifetime, bool $andFlush = true): void
    {
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository(Media::class);

        $mediaArray = $repository->findOldOrphans($lifetime);
        foreach ($mediaArray as $media) {
            $em->remove($media);
        }

        if ($andFlush) {
            $em->flush();
        }
    }

    public function removeMarkedMedia(string $requestId, bool $andFlush = true): void
    {
        $em = $this->doctrine->getManager();
        $repositoryMediaMarkRemove = $em->getRepository(MediaMarkRemove::class);

        $rows = $repositoryMediaMarkRemove->findBy([
            'requestId' => $requestId,
        ]);

        $changesAffected = false;
        foreach ($rows as $row) {
            $media = $row->getMedia();

            $em->remove($media);
            $changesAffected = true;
        }

        if ($changesAffected && $andFlush) {
            $em->flush();
        }
    }

    public function renameMarkedMedia(string $requestId, bool $andFlush = true): void
    {
        $em = $this->doctrine->getManager();
        $repositoryMediaMarkEdit = $em->getRepository(MediaMarkEdit::class);

        $rows = $repositoryMediaMarkEdit->findBy([
            'requestId' => $requestId,
        ]);

        $changesAffected = false;
        foreach ($rows as $row) {
            $media = $row->getMedia();
            $media->setName($row->getNewName());
            $media->setDescription($row->getNewDescription());

            $em->remove($row);
            $changesAffected = true;
        }

        if ($changesAffected && $andFlush) {
            $em->flush();
        }
    }

    /**
     * Returns array of file entities.
     */
    public function findOrphans(string $requestId): array
    {
        if (!isset($this->cachedMedias[$requestId])) {
            $em = $this->doctrine->getManager();
            $repository = $em->getRepository(Media::class);

            $this->cachedMedias[$requestId] = $repository->findBy([
                'requestId' => $requestId,
                'isOrphan' => true,
            ]);
        }

        return $this->cachedMedias[$requestId];
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneBySecuredId(string $securedId): ?MediaInterface
    {
        return $this->getRepository()->findOneBySecuredId($securedId);
    }

    /**
     * @param MediaInterface[] $medias    Array of media entities
     * @param array            $positions Array of positions medias like as [mediaId, mediaId,  ...]
     */
    public function sortMedias(array $medias, array $positions, bool $andFlush = true): void
    {
        $em = $this->doctrine->getManager();

        foreach ($medias as $media) {
            $position = array_search($media->getId(), $positions, true);

            if (false !== $position && $position != $media->getPosition()) {
                $media->setPosition($position);
            }
        }

        if ($andFlush) {
            $em->flush();
        }
    }

    private function getRepository(): MediaRepository
    {
        $em = $this->doctrine->getManager();

        /** @var MediaRepository $repository */
        $repository = $em->getRepository(Media::class);

        return $repository;
    }
}
