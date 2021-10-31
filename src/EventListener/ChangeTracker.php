<?php

namespace BisonLab\ContextBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/*
 * Does as little as possible.
 */

class ChangeTracker implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
            Events::preUpdate,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
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

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        if ($eventArgs->hasChangedField('attributes_json')) {
            if ($json = $eventArgs->getNewValue('attributes_json')) {
                $la = $this->_array_change_key_case_recursive(json_decode($json, true), MB_CASE_LOWER);
                $json = json_encode($la);
                $eventArgs->setNewValue('attributes_json', $json);
            }
        }
    }

    private function _array_change_key_case_recursive($arr, $case = MB_CASE_LOWER): array
    {
        $ret = array();
        foreach ($arr as $k => $v) {
            if(is_array($v))
                $v = $this->_array_change_key_case_recursive($v, $case);
            $ret[mb_convert_case($k, $case, "UTF-8")] = $v;
        }
        return $ret;
    }

    private function _checkUnique($context, $em): void
    {
        if ($exists = $em->getRepository(get_class($context))->findOneBy(array(
                'system' => $context->getSystem(),
                'object_name' => $context->getObjectName(),
                'external_id' => $context->getExternalId(),
            ))) {
            throw new ConstraintDefinitionException("Context " . $context->getLabel() . " with external id " . $context->getExternalId() . " is already in use.");
        }
    }
}
