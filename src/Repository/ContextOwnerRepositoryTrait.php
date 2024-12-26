<?php

namespace BisonLab\ContextBundle\Repository;

use Doctrine\ORM\Mapping as ORM;

trait ContextOwnerRepositoryTrait
{
    public function getOneByContext($system, $object_name, $external_id)
    {
        return $this->findOneByContext($system, $object_name, $external_id);
    }

    // This is the correct name accortding to many.
    public function findOneByContext($system, $object_name, $external_id)
    {
        return current($this->findByContext($system, $object_name, $external_id));
    }

    public function findByContext($system, $object_name, $external_id)
    {
        $qb2 = $this->createQueryBuilder('o')
              ->innerJoin('o.contexts', 'oc')
              ->where('oc.system = :system')
              ->andWhere('oc.object_name = :object_name')
              ->andWhere('oc.external_id = :external_id')
              ->setParameter("system", $system)
              ->setParameter("object_name", $object_name)
              ->setParameter("external_id", (string)$external_id);
        return $qb2->getQuery()->getResult();
    }
}
