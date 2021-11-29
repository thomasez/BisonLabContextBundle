<?php

namespace BisonLab\ContextBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/*
 * To be honest, why extend this if it's nothing here?
 * (And the construct has to be in each repo.)
 */
class ContextBaseRepository extends ServiceEntityRepository
{
}
