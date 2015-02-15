<?php

namespace Glavweb\UploaderBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class MediaRepository
 * @package Glavweb\UploaderBundle\Entity\Repository
 */
class MediaRepository extends EntityRepository
{
    /**
     * @param array $inParameters
     * @return array
     */
    public function findIn(array $inParameters)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from('GlavwebUploaderBundle:Media', 't')
            ->where('t.id IN (:in_parameter)')
            ->setParameter('in_parameter', $inParameters)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $lifetime
     * @return mixed
     */
    public function findOldOrphans($lifetime)
    {
        $datetime = new \DateTime('now');
        $datetime->modify(sprintf('- %s seconds', $lifetime));

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from('GlavwebUploaderBundle:Media', 't')
            ->where('t.isOrphan = true')
            ->andWhere('t.createdAt <= :datetime')
            ->setParameter('datetime', $datetime)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $context
     * @param string $requestId
     * @return array
     */
    public function findMarkRemoveByContextAndRequestId($context, $requestId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from('GlavwebUploaderBundle:Media', 't')
            ->where('t.markRemove = true')
            ->andWhere('t.context = :context')
            ->andWhere('t.requestId = :requestId')
            ->setParameter('context', $context)
            ->setParameter('requestId', $requestId)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $context
     * @param string $requestId
     * @return array
     */
    public function findByContextAndRequestId($context, $requestId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from('GlavwebUploaderBundle:Media', 't')
            ->where('t.context = :context')
            ->andWhere('t.requestId = :requestId')
            ->setParameter('context', $context)
            ->setParameter('requestId', $requestId)
        ;

        return $qb->getQuery()->getResult();
    }
}