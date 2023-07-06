<?php

namespace BisonLab\ContextBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use BisonLab\ContextBundle\Entity\ContextLog;

/**
 * @method ContextLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContextLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContextLog[]    findAll()
 * @method ContextLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContextLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContextLog::class);
    }

    public function findByOwner($context_class, $owner_id)
    {
        $entity_name = $context_class->getOwnerEntityClass();
        $entity_alias = $context_class->getOwnerEntityAlias();

        $qb = $this->_em->createQueryBuilder();
        $qb->select('l')
              ->from($this->_entityName, 'l')
              ->where('l.owner_class in (:oc)')
              ->andWhere('l.owner_id = :owner_id')
              ->orderBy('l.logged_at', 'DESC')
              ->setParameter("oc", [$entity_name, $entity_alias])
              ->setParameter("owner_id", $owner_id);
        return $qb->getQuery()->getResult();
    }
}
