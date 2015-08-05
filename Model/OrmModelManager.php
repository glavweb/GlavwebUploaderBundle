<?php

namespace Glavweb\UploaderBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Glavweb\UploaderBundle\Entity\Media;

/**
 * Class ModelManager
 * @package Glavweb\UploaderBundle\Model
 */
class OrmModelManager extends BaseModelManager
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @var array
     */
    private $cacheMediaEntities;

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
     * @param int $id
     * @return mixed
     */
    public function findUser($id)
    {
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository('GlavwebUploaderBundle:Media');

        return $repository->find($id);
    }

    /**
     * @param int  $lifetime
     * @param bool $andFlush $andFlush Whether to flush the changes (default true)
     */
    public function removeOrphans($lifetime, $andFlush = true)
    {
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository('GlavwebUploaderBundle:Media');

        $mediaArray = $repository->findOldOrphans($lifetime);
        foreach ($mediaArray as $media) {
            $em->remove($media);
        }

        if ($andFlush) {
            $em->flush();
        }
    }

    /**
     * @param string  $context
     * @param string  $requestId
     * @param Boolean $andFlush  Whether to flush the changes (default true)
     */
    public function removeMarkedMedia($context, $requestId, $andFlush = true)
    {
        $em = $this->doctrine->getManager();
        $repositoryMediaMarkRemove = $em->getRepository('GlavwebUploaderBundle:MediaMarkRemove');

        $rows = $repositoryMediaMarkRemove->findBy(array(
            'requestId' => $requestId
        ));

        $changesAffected = false;
        foreach ($rows as $row) {
            if ($row && $row->getMedia()->getContext() == $context) {
                $media = $row->getMedia();

                $em->remove($media);
                $changesAffected = true;
            }
        }

        if ($changesAffected && $andFlush) {
            $em->flush();
        }
    }

    /**
     * @param string  $context
     * @param string  $requestId
     * @param Boolean $andFlush  Whether to flush the changes (default true)
     */
    public function renameMarkedMedia($context, $requestId, $andFlush = true)
    {
        $em = $this->doctrine->getManager();
        $repositoryMediaMarkRename = $em->getRepository('GlavwebUploaderBundle:MediaMarkRename');

        $rows = $repositoryMediaMarkRename->findBy(array(
            'requestId' => $requestId
        ));

        $changesAffected = false;
        foreach ($rows as $row) {
            if ($row && $row->getMedia()->getContext() == $context) {
                $media = $row->getMedia();
                $media->setName($row->getNewName());
                $media->setDescription($row->getNewDescription());

                $changesAffected = true;
            }
        }

        if ($changesAffected && $andFlush) {
            $em->flush();
        }
    }

    /**
     * Returns array of file entities
     *
     * @param string $context
     * @return array
     */
    public function findOrphans($context, $requestId)
    {
        if (!isset($this->cacheMediaEntities[$context])) {
            $em = $this->doctrine->getManager();
            $repository = $em->getRepository('GlavwebUploaderBundle:Media');

            $this->cacheMediaEntities = $repository->findBy(array(
                'requestId' => $requestId,
                'context'   => $context,
                'isOrphan'  => true
            ));
        }

        return $this->cacheMediaEntities;
    }

    /**
     * @param $entities
     * @param $positions
     */
    public function sortMedia($entities, $positions)
    {
        $em = $this->doctrine->getManager();

        $rows = $entities->toArray();

        foreach ($rows as $row) {
            $position = array_search($row->getId(), $positions);

            if ($position!==false && $position!=$row->getPosition()) {
                $row->setPosition($position);
            }
        }
        $em->flush();
    }
}