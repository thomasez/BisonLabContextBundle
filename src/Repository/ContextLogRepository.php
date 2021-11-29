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
}
