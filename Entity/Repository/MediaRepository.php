<?php

/*
 * This file is part of the Glavweb UploaderBundle package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\UploaderBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class MediaRepository
 *
 * @package Glavweb\UploaderBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MediaRepository extends EntityRepository
{
    /**
     * @param string $securedId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySecuredId($securedId)
    {
        $pos = strpos($securedId, '-');

        if (!$pos) {
            return null;
        }

        $id    = substr($securedId, 0, $pos);
        $token = substr($securedId, $pos + 1);

        $qb = $this->createQueryBuilder('t')
            ->where('t.id = :id AND t.token = :token')
            ->setParameter('id', $id)
            ->setParameter('token', $token)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param array $inParameters
     * @return array
     */
    public function findIn(array $inParameters)
    {
        $qb = $this->createQueryBuilder('t')
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

        $qb = $this->createQueryBuilder('t')
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
        $qb = $this->createQueryBuilder('t')
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
        $qb = $this->createQueryBuilder('t')
            ->where('t.context = :context')
            ->andWhere('t.requestId = :requestId')
            ->setParameter('context', $context)
            ->setParameter('requestId', $requestId)
        ;

        return $qb->getQuery()->getResult();
    }
}