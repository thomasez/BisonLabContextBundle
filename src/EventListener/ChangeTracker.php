<?php

namespace BisonLab\ContextBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

#[AsDoctrineListener('onFlush')]
class ChangeTracker
{
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getObjectManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (in_array("BisonLab\ContextBundle\Entity\ContextBaseTrait",
                    class_uses($entity)))
                if ($entity->isUnique())
                    $this->_checkUnique($entity, $em);
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (in_array("BisonLab\ContextBundle\Entity\ContextBaseTrait",
                    class_uses($entity)))
                if ($entity->isUnique())
                    $this->_checkUnique($entity, $em);
        }
    }

    private function _checkUnique($context, $em): void
    {
        if ($exists = $em->getRepository(get_class($context))->findOneBy(array(
                'system' => $context->getSystem(),
                'object_name' => $context->getObjectName(),
                'external_id' => $context->getExternalId(),
            ))) {
            // My, myself or not I?
            if ($exists !== $context) {
                throw new ConstraintDefinitionException("Context " . $context->getLabel() . " with external id " . $context->getExternalId() . " is already in use by " . (string)$exists->getOwner() . " the duplicate is " . (string)$context->getOwner() );
            }
        }
    }
}
