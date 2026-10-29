<?php

namespace BisonLab\ContextBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
class ContextBase
{
    use ContextBaseTrait;

    /*
     * See ContextBaseTrait for some simple documentation.
     */
    public function __construct($options = array())
    {
        return $this->traitConstruct($options);
    }
}
