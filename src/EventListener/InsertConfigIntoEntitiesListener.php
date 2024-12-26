<?php

namespace BisonLab\ContextBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/*
 * This one injects the context config into the entities so that they know
 * what they are and can do, all from a simple config file.
 * contexts.yml
 */
#[AsDoctrineListener('postLoad')]
#[AsDoctrineListener('prePersist')]
class InsertConfigIntoEntitiesListener
{
    public function __construct(
        private ParameterBagInterface $params
    ) {
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $this->_insertConfig($args);
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $this->_insertConfig($args);
    }

    private function _insertConfig($args)
    {
        $entity = $args->getObject();
        if (in_array("BisonLab\ContextBundle\Entity\ContextBaseTrait", class_uses($entity))) {
            $context_conf = $this->params->get('app.contexts');
            list($bundle, $object) = explode(":", $entity->getOwnerEntityAlias());
            $object_name = $entity->getObjectName();
            // Gotta be able to handle the case of no config at all..
            if (isset($context_conf[$bundle][$object]) 
                    && $context_conf[$bundle][$object][$entity->getSystem()]) {
                $conf = null;
                foreach ($context_conf[$bundle][$object][$entity->getSystem()] as $c)
                {
                    if ($c['object_name'] == $object_name) {
                        $conf = $c;
                        break;
                    }
                }
                // You may end up with an error point at this place.
                // The reason for this is that you haven't configured
                // contexts.yml properly. You might miss either a system or
                // object_name.
                if (!$conf) {
                    // Feel free to find a better exception. I just needed one.
                    throw new \InvalidArgumentException(sprintf("There was not Context config found for the %s:%s-%s context.", $bundle, $object, $object_name));
                }
                $entity->setConfig($conf);
                return $entity;
            }
        }
        // Had nothing to do.
        return false;
    }
}
