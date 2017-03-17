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
use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\Entity\MediaMarkRemove;
use Glavweb\UploaderBundle\Entity\MediaMarkEdit;

/**
 * Class OrmModelManager
 *
 * @package Glavweb\UploaderBundle\Model
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class OrmModelManager implements ModelManagerInterface
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @var array
     */
    private $cachedMedias = [];

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Creates an empty media instance.
     *
     * @return MediaInterface
     */
    public function createMedia()
    {
        return new Media();
    }

    /**
     * @param MediaInterface $media
     * @param string $name
     * @param string $description
     * @return bool
     */
    public function editMedia(MediaInterface $media, $name, $description)
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
     * @param MediaInterface $media
     * @param Boolean        $andFlush Whether to flush the changes (default true)
     */
    public function updateMedia(MediaInterface $media, $andFlush = true)
    {
        $em = $this->doctrine->getManager();
        $em->persist($media);

        if ($andFlush) {
            $em->flush();
        }
    }

    /**
     * @param MediaInterface $media
     * @return bool
     */
    public function removeMedia(MediaInterface $media)
    {
        $em = $this->doctrine->getManager();

        $em->remove($media);
        $em->flush();

        return true;
    }

    /**
     * @param MediaInterface $media
     * @param string $requestId
     * @return bool
     */
    public function markRemove(MediaInterface $media, $requestId)
    {
        $em = $this->doctrine->getManager();

        /** @var Media $media */
        $media->setRequestId($requestId);

        $mediaMarkRemove = new MediaMarkRemove();
        $mediaMarkRemove->setRequestId($requestId);
        $mediaMarkRemove->setMedia($media);

        $em->persist($mediaMarkRemove);
        $em->flush();

        return true;
    }

    /**
     * @param MediaInterface $media
     * @param string         $requestId
     * @param string         $name
     * @param string         $description
     * @return bool
     */
    public function markEdit(MediaInterface $media, $requestId, $name, $description)
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
     * @param MediaInterface $media
     * @param boolean        $andFlush Whether to flush the changes (default true)
     * @return void
     */
    public function deleteMedia(MediaInterface $media, $andFlush = true)
    {
        $em = $this->doctrine->getManager();
        $em->remove($media);

        if ($andFlush) {
            $em->flush();
        }
    }

    /**
     * @param int $id Media ID
     * @return MediaInterface
     */
    public function findMedia($id)
    {
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository(Media::class);

        return $repository->find($id);
    }

    /**
     * @param int  $lifetime
     * @param bool $andFlush $andFlush Whether to flush the changes (default true)
     */
    public function removeOrphans($lifetime, $andFlush = true)
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

    /**
     * @param string  $requestId
     * @param Boolean $andFlush  Whether to flush the changes (default true)
     */
    public function removeMarkedMedia($requestId, $andFlush = true)
    {
        $em = $this->doctrine->getManager();
        $repositoryMediaMarkRemove = $em->getRepository(MediaMarkRemove::class);

        $rows = $repositoryMediaMarkRemove->findBy([
            'requestId' => $requestId
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

    /**
     * @param string  $requestId
     * @param Boolean $andFlush  Whether to flush the changes (default true)
     */
    public function renameMarkedMedia($requestId, $andFlush = true)
    {
        $em = $this->doctrine->getManager();
        $repositoryMediaMarkEdit = $em->getRepository(MediaMarkEdit::class);

        $rows = $repositoryMediaMarkEdit->findBy([
            'requestId' => $requestId
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
     * Returns array of file entities
     *
     * @param string $requestId
     * @return array
     */
    public function findOrphans($requestId)
    {
        if (!isset($this->cachedMedias[$requestId])) {
            $em = $this->doctrine->getManager();
            $repository = $em->getRepository(Media::class);

            $this->cachedMedias[$requestId] = $repository->findBy([
                'requestId' => $requestId,
                'isOrphan'  => true
            ]);
        }

        return $this->cachedMedias[$requestId];
    }

    /**
     * @param string $securedId
     * @return MediaInterface|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySecuredId($securedId)
    {
        return $this->getRepository()->findOneBySecuredId($securedId);
    }

    /**
     * @param MediaInterface[] $medias    Array of media entities
     * @param array            $positions Array of positions medias like as [mediaId, mediaId,  ...]
     * @param bool             $andFlush  Whether to flush the changes (default true)
     */
    public function sortMedias(array $medias, $positions, $andFlush = true)
    {
        $em = $this->doctrine->getManager();

        foreach ($medias as $media) {
            $position = array_search($media->getId(), $positions);

            if ($position !== false && $position != $media->getPosition()) {
                $media->setPosition($position);
            }
        }

        if ($andFlush) {
            $em->flush();
        }
    }

    /**
     * @return \Glavweb\UploaderBundle\Entity\Repository\MediaRepository
     */
    private function getRepository()
    {
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository(Media::class);

        return $repository;
    }
}