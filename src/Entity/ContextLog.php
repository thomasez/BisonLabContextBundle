<?php

namespace BisonLab\ContextBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'bisonlab_context_log')]
#[ORM\Index(name: 'log_owner_lookup_idx', columns: ['owner_class', 'owner_id'])]
#[ORM\Entity(repositoryClass: 'BisonLab\ContextBundle\Repository\ContextLogRepository')]
class ContextLog
{
    /**
     * @var integer $id
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected $id;

    /**
     * @var string $action
     */
    #[ORM\Column(type: 'string', length: 8)]
    protected $action;

    /**
     * @var \DateTime $logged_at
     */
    #[ORM\Column(type: 'datetime')]
    protected $logged_at;

    /**
     * @var string $userid
     * Annoyingly enough, there might not be a (known) user doing this.
     */
    #[ORM\Column(type: 'string', length: 80, nullable: true)]
    protected $user_id;

    /**
     * @var string $classname
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $owner_class;

    /**
     * @var int $id
     */
    #[ORM\Column(type: 'string', length: 80)]
    private $owner_id;

    /**
     * @var string $system
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $system;

    /**
     * @var string $object_name
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $object_name;

    /**
     * @var string $external_id
     */
    #[ORM\Column(type: 'string', length: 80)]
    private $external_id;

    /**
     * @var string $url
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $url;

    public function __construct($context, $action)
    {
        $this->action = $action;
        $this->logged_at = new \DateTime();
        $this->owner_class = $context->getOwnerEntityAlias();
        $owner_entity = $context->getOwner();
        $this->owner_id = $owner_entity->getId();
        $this->system = $context->getSystem();
        $this->object_name = $context->getObjectName();
        $this->external_id = $context->getExternalId();
        $this->url = $context->getUrl();
        return $this;
    }

    public function setUserId(string|int $user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Get user_id
     *
     * @return string 
     */
    public function getUserId(): string|int
    {
        return $this->user_id;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Get logged_at
     *
     * @return DateTime 
     */
    public function getLoggedAt(): \DateTime
    {
        return $this->logged_at;
    }

    /**
     * Get system
     *
     * @return string 
     */
    public function getSystem(): string
    {
        return $this->system;
    }

    /**
     * Get object_name
     *
     * @return string 
     */
    public function getObjectName(): string
    {
        return $this->object_name;
    }

    /**
     * Get external_id
     *
     * @return string 
     */
    public function getExternalId(): string|int
    {
        return $this->external_id;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
}
