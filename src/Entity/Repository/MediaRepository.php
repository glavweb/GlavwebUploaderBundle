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
use Doctrine\ORM\NonUniqueResultException;
use Glavweb\UploaderBundle\Entity\Media;

/**
 * Class MediaRepository.
 *
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MediaRepository extends EntityRepository
{
    /**
     * @throws NonUniqueResultException
     */
    public function findOneBySecuredId(string $securedId): ?Media
    {
        $pos = strpos($securedId, '-');

        if (!$pos) {
            return null;
        }

        $id = substr($securedId, 0, $pos);
        $token = substr($securedId, $pos + 1);

        $qb = $this->createQueryBuilder('t')
            ->where('t.id = :id AND t.token = :token')
            ->setParameter('id', $id)
            ->setParameter('token', $token)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return Media[]
     */
    public function findIn(array $inParameters): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.id IN (:in_parameter)')
            ->setParameter('in_parameter', $inParameters)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Media[]
     *
     * @throws \DateMalformedStringException
     */
    public function findOldOrphans(int $lifetime): array
    {
        $datetime = new \DateTime('now');
        $datetime->modify(\sprintf('- %s seconds', $lifetime));

        $qb = $this->createQueryBuilder('t')
            ->where('t.isOrphan = true')
            ->andWhere('t.createdAt <= :datetime')
            ->setParameter('datetime', $datetime)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findMarkRemoveByContextAndRequestId(string $context, string $requestId): array
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

    public function findByContextAndRequestId(string $context, string $requestId): array
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
