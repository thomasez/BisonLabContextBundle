<?php

namespace BisonLab\ContextBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/*
 * Remember to put this in the owner Entity:
 * use You\YourBundle\Entity\WhateverContext as Context;
 * (Would be nice if it worked, but had to remove the class check in add and
 * remove. TODO: Find out if this is possible.)
 */

trait ContextOwnerTrait
{
    /*
     * This has to be pasted into the owner object, since it's a good thing  to
     * keep the naming correct.
     * s/whatever/realname/g 
     * (remember to add the slash and asterixes..)
     * @ORM\OneToMany(targetEntity="WhateverContext", mappedBy="whatever", cascade={"persist", "remove"})
    private $contexts;
     */

    /* 
     * This could also be solved with keeping __construct() and then
     * use ContextOwnerTrait { __construct as traitConstruct }
     * but I cannot see why it's better. To me it's more confusing.
     */
    public function traitConstruct($options = array())
    {
        $this->contexts = new ArrayCollection();
    }

    /**
     * Get contexts
     * Add system plus eventual object_name and you will get a collection
     * matching that.
     *
     * @return objects
     */
    public function getContexts($system = null, $object_name = null): Collection
    {
        if (!$system)
            return $this->contexts;

        $contexts = new \Doctrine\Common\Collections\ArrayCollection();
        
        // TODO: Use Criterias.
        foreach ($this->getContexts() as $c) {
            if (!$object_name && $system == $c->getSystem())
                $contexts->add($c);
            if (empty($object_name) || $system != $c->getSystem())
                continue;
            if ($object_name == $c->getObjectName())
                $contexts->add($c);
        }
        return $contexts;
    }

    /**
     * Add contexts
     *
     * @param Context $context;
     * @return $this
     * Can't do a class check since it's different context classes and aliasing
     * in the main owner class seems noe to be working.
     */
    public function addContext($context)
    {
        $this->contexts[] = $context;
        $context->setOwner($this);
        return $this;
    }

    /**
     * Remove context
     *
     * @param Context $context;
     */
    public function removeContext($context)
    {
        $this->contexts->removeElement($context);
    }

    /**
     * Get contexts
     * This has a flaw, it only handles one context of each type.
     * It was correct to assume there would be only one when this was made,
     * but it's not always the case.
     *
     * @return objects
     */
    public function getContextsAsHash()
    {
        $arr = array();
        foreach ($this->getContexts() as $c) {
            $arr[$c->getSystem()][$c->getObjectName()] = $c;
        }
        return $arr;
    }
}
