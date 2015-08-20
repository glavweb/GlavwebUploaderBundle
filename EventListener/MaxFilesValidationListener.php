<?php

namespace Glavweb\UploaderBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Glavweb\UploaderBundle\Event\ValidationEvent;
use Glavweb\UploaderBundle\Exception\ValidationException;

class MaxFilesValidationListener
{
    private $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function onValidate(ValidationEvent $event)
    {
//        $config = $event->getConfig();
//
//        if (!isset($config['max_files'])) {
//            return;
//        }
//
//        $request = $event->getRequest();
//        $requestId = $request->get('_glavweb_uploader_request_id');
//        $qb = $this->doctrine->getEntityManager()->createQueryBuilder();
//        $qb->select('COUNT(m.id)')
//            ->from('GlavwebUploaderBundle:Media', 'm')
//            ->where('m.requestId = :requestId')
//            ->setParameter('requestId', $requestId);
//        $result = $qb->getQuery()->getSingleScalarResult();
//
//        if ($result >= $config['max_files']) {
//            throw new ValidationException('error.maxfiles');
//        }
    }
}
