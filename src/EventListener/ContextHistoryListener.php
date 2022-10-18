<?php

namespace BisonLab\ContextBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Context\EventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use BisonLab\ContextBundle\Entity\ContextLog;

/*
 * Does as little as possible.
 */

class ContextHistoryListener implements EventSubscriberInterface
{
    private $uow;
    private $em;
    private $token_storage;

    public function __construct(TokenStorageInterface $token_storage, ManagerRegistry $doctrine)
    {
        $this->token_storage = $token_storage;
        $this->doctrine      = $doctrine;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $this->em = $eventArgs->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();

        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            if (in_array("BisonLab\ContextBundle\Entity\ContextBaseTrait",
                    class_uses($entity)))
                $this->logContext($entity, 'create');
        }
        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            if (in_array("BisonLab\ContextBundle\Entity\ContextBaseTrait",
                    class_uses($entity)))
                $this->logContext($entity, 'update');
        }
        foreach ($this->uow->getScheduledEntityDeletions() as $entity) {
            if (in_array("BisonLab\ContextBundle\Entity\ContextBaseTrait",
                    class_uses($entity)))
                $this->logContext($entity, 'delete');
        }
        return;
    }

    private function logContext($context, $action): void
    {
        // First, ignore if it's meant to be ignored.
        if ($context->doNotLog())
            return;

        // Do I have to check if we've already made a log context for this?
        // I was hoping we didn't and it really should not be like that.

        // Gotta use the correct entity manager
        $bcomm_em = $this->doctrine->getManagerForClass(ContextLog::class);

        // Then, check if the owner is set for removal. 
        // It may even be disconnected already, so if there are no owner,
        // these has to go.
        // But this may not work out properly. If the relation has been
        // disconnected before we are here, which may be the case, we do not
        // have an owner even if the owner exists.
        $owner = $context->getOwner();
        if ($action == "delete" && 
                (!$owner 
                  || $this->uow->isScheduledForDelete($owner))) {
            $qb = $bcomm_em
                ->createQueryBuilder()
                ->delete('BisonLab\ContextBundle\Entity\ContextLog', 'cl')
                ->where('cl.owner_class = :owner_class')
                ->andWhere('cl.owner_id = :owner_id')
                ->setParameter('owner_class', $context->getOwnerEntityAlias())
                ->setParameter('owner_id', $owner->getId());
            $qb->getQuery()->execute();
            return;
        }

        $clog = new ContextLog($context, $action);
        // Does it have a user?
        // Not always even a security token (It's hopefully running in console)
        if ($this->token_storage->getToken()) {
            $user = $this->token_storage->getToken()->getUser();
            $clog->setUserId($user->getid());
        }
        $bcomm_em->persist($clog);
        $metadata = $bcomm_em->getClassMetadata('BisonLab\ContextBundle\Entity\ContextLog');
        $this->uow->computeChangeSet($metadata, $clog);
        return;
    }
}
